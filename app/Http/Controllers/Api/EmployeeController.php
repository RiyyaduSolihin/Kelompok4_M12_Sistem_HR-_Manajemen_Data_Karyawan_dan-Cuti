<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Normalisasi nilai status_kerja ('active' -> 'aktif', 'inactive' -> 'nonaktif')
     */
    private function normalizeStatus(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        $val = strtolower(trim($status));
        if (in_array($val, ['aktif', 'active'], true)) {
            return 'aktif';
        }
        if (in_array($val, ['nonaktif', 'inactive', 'non-aktif'], true)) {
            return 'nonaktif';
        }

        return $val;
    }

    /**
     * GET /api/employees & GET /api/karyawan
     * - HR: Dapat mengambil seluruh data karyawan (bisa filter ?status_kerja=aktif / ?status=aktif)
     * - Employee (User biasa): Hanya boleh mengambil datanya sendiri (Poin 3)
     */
    public function index(Request $request): JsonResponse
    {
        $auth = AuthController::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu (sertakan Authorization: Bearer <token> atau header X-User-Role).',
            ], 401);
        }

        $rawStatus = $request->query('status_kerja') ?? $request->query('status');
        $statusFilter = $this->normalizeStatus($rawStatus);

        // Poin 3 & Poin 4: Jika bukan HR
        if (! $auth['is_hr']) {
            // Jika user biasa mencoba mengambil daftar seluruh karyawan aktif lewat filter status
            if ($statusFilter === 'aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Hanya HR yang dapat mengambil daftar seluruh karyawan aktif.',
                ], 403);
            }

            if (! $auth['employee_id']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kepegawaian untuk akun Anda belum terhubung.',
                    'data' => [],
                ], 404);
            }

            $ownEmployee = Employee::where('id', $auth['employee_id'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan milik Anda berhasil diambil.',
                'data' => $ownEmployee,
            ], 200);
        }

        // Jika HR: Boleh mengambil seluruh data karyawan
        $query = Employee::query();

        if ($statusFilter) {
            $query->where('status_kerja', $statusFilter);
        }

        $employees = $query->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => $statusFilter === 'aktif'
                ? 'Berhasil mengambil daftar karyawan aktif.'
                : 'Daftar seluruh data karyawan berhasil diambil.',
            'data' => $employees,
        ], 200);
    }

    /**
     * GET /api/employees/active | GET /api/employees/aktif | GET /api/karyawan/aktif | GET /api/karyawan/active
     * Mengambil daftar seluruh karyawan aktif — HANYA HR (Poin 4 & Poin 8)
     */
    public function active(Request $request): JsonResponse
    {
        $auth = AuthController::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu (sertakan Authorization: Bearer <token> atau header X-User-Role).',
            ], 401);
        }

        if (! $auth['is_hr']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya HR yang dapat mengambil daftar karyawan aktif.',
            ], 403);
        }

        $karyawanAktif = Employee::where('status_kerja', 'aktif')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar karyawan aktif.',
            'data' => $karyawanAktif,
        ], 200);
    }

    /**
     * GET /api/employees/{id} & GET /api/karyawan/{id}
     * - HR: Bebas melihat detail karyawan siapa saja
     * - Employee (User biasa): Hanya boleh melihat datanya sendiri (Poin 3)
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $auth = AuthController::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (! $auth['is_hr'] && (int) $id !== (int) $auth['employee_id']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda hanya diperbolehkan mengambil data kepegawaian milik Anda sendiri.',
            ], 403);
        }

        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail karyawan berhasil diambil.',
            'data' => $employee,
        ], 200);
    }

    /**
     * POST /api/employees & POST /api/karyawan
     * Menyimpan data karyawan baru — Hanya HR (M12-US1: AC 1 & AC 2, Poin 7: Employee = Karyawan)
     */
    public function store(Request $request): JsonResponse
    {
        $auth = AuthController::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (! $auth['is_hr']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya HR yang memiliki izin untuk menambahkan data karyawan.',
            ], 403);
        }

        // Normalisasi input agar mendukung nama field Indonesia (Karyawan) maupun Inggris (Employee)
        $input = [
            'nik' => $request->input('nik'),
            'nama' => $request->input('nama') ?? $request->input('name'),
            'jabatan' => $request->input('jabatan') ?? $request->input('position'),
            'divisi' => $request->input('divisi') ?? $request->input('department'),
            'status_kerja' => $this->normalizeStatus($request->input('status_kerja') ?? $request->input('status') ?? 'aktif'),
        ];

        $validator = Validator::make($input, [
            'nik' => 'required|string|max:30|unique:employees,nik',
            'nama' => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'divisi' => 'required|string|max:50',
            'status_kerja' => 'required|in:aktif,nonaktif',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah terdaftar.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'status_kerja.required' => 'Status kerja wajib diisi.',
            'status_kerja.in' => 'Status kerja harus bernilai aktif atau nonaktif.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data karyawan. Periksa kembali input Anda.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];

        $employee = Employee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Informasi kepegawaian berhasil disimpan.',
            'data' => $employee,
        ], 201);
    }

    /**
     * PUT|PATCH /api/employees/{id} & PUT|PATCH /api/karyawan/{id}
     * Memperbarui data karyawan — Hanya HR
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $auth = AuthController::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (! $auth['is_hr']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya HR yang memiliki izin untuk memperbarui data karyawan.',
            ], 403);
        }

        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $input = [
            'nik' => $request->input('nik', $employee->nik),
            'nama' => $request->input('nama') ?? $request->input('name') ?? $employee->nama,
            'jabatan' => $request->input('jabatan') ?? $request->input('position') ?? $employee->jabatan,
            'divisi' => $request->input('divisi') ?? $request->input('department') ?? $employee->divisi,
            'status_kerja' => $this->normalizeStatus($request->input('status_kerja') ?? $request->input('status') ?? $employee->status_kerja),
        ];

        $validator = Validator::make($input, [
            'nik' => ['required', 'string', 'max:30', Rule::unique('employees', 'nik')->ignore($employee->id)],
            'nama' => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'divisi' => 'required|string|max:50',
            'status_kerja' => 'required|in:aktif,nonaktif',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah digunakan karyawan lain.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'status_kerja.required' => 'Status kerja wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data karyawan.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $employee,
        ], 200);
    }

    /**
     * PATCH /api/employees/{id}/deactivate & PATCH /api/karyawan/{id}/deactivate
     * Mengubah status karyawan menjadi nonaktif dan mencatat tanggal berhenti — HANYA HR (Poin 6)
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        $auth = AuthController::resolveAuth($request);

        if (! $auth['authenticated']) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (! $auth['is_hr']) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya HR yang memiliki izin untuk menonaktifkan karyawan.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_berhenti' => 'required|date',
        ], [
            'tanggal_berhenti.required' => 'Tanggal berhenti wajib diisi.',
            'tanggal_berhenti.date' => 'Format tanggal berhenti tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi deaktivasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $employee->status_kerja = 'nonaktif';
        $employee->tanggal_berhenti = $request->tanggal_berhenti;
        $employee->save();

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dinonaktifkan dan riwayat kepegawaian tetap tersimpan.',
            'data' => $employee,
        ], 200);
    }
}
