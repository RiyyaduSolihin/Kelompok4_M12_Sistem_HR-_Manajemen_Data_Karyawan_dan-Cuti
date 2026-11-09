<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Support\DatabaseSchemaGuard;
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
     * Normalisasi jenis kelamin ('L' -> 'Laki-laki', 'P' -> 'Perempuan')
     */
    private function normalizeGender(?string $gender): ?string
    {
        if (! $gender) {
            return null;
        }

        $val = strtoupper(trim($gender));
        if ($val === 'L' || $val === 'LAKI-LAKI') {
            return 'Laki-laki';
        }
        if ($val === 'P' || $val === 'PEREMPUAN') {
            return 'Perempuan';
        }

        return $gender;
    }

    /**
     * Ekstrak dan normalisasi seluruh input Karyawan + Data Diri KTP
     */
    private function extractEmployeeInput(Request $request, ?Employee $existing = null): array
    {
        $status = $this->normalizeStatus(
            $request->input('status_kerja')
            ?? $request->input('status')
            ?? ($existing ? $existing->status_kerja : 'aktif')
        );

        $genderRaw = $request->input('jenis_kelamin', $existing?->jenis_kelamin);

        return [
            'nik' => $request->has('nik') ? trim((string) $request->input('nik')) : $existing?->nik,
            'nama' => $request->has('nama') || $request->has('name')
                ? trim((string) ($request->input('nama') ?? $request->input('name')))
                : $existing?->nama,
            'jabatan' => $request->has('jabatan') || $request->has('position')
                ? trim((string) ($request->input('jabatan') ?? $request->input('position')))
                : $existing?->jabatan,
            'divisi' => $request->has('divisi') || $request->has('department')
                ? trim((string) ($request->input('divisi') ?? $request->input('department')))
                : $existing?->divisi,
            'status_kerja' => $status,
            'tanggal_aktif' => $request->input('tanggal_aktif', $existing?->tanggal_aktif ?? now()->toDateString()),
            'tanggal_berhenti' => $request->input('tanggal_berhenti', $existing?->tanggal_berhenti),
            // Data Diri Sesuai KTP
            'nik_ktp' => $request->input('nik_ktp', $existing?->nik_ktp),
            'tempat_lahir' => $request->input('tempat_lahir', $existing?->tempat_lahir),
            'tanggal_lahir' => $request->input('tanggal_lahir', $existing?->tanggal_lahir),
            'jenis_kelamin' => $this->normalizeGender($genderRaw),
            'golongan_darah' => $request->input('golongan_darah', $existing?->golongan_darah),
            'alamat' => $request->input('alamat') ?? $request->input('address') ?? $existing?->alamat,
            'rt_rw' => $request->input('rt_rw', $existing?->rt_rw),
            'kelurahan_desa' => $request->input('kelurahan_desa', $existing?->kelurahan_desa),
            'kecamatan' => $request->input('kecamatan', $existing?->kecamatan),
            'kabupaten_kota' => $request->input('kabupaten_kota', $existing?->kabupaten_kota),
            'provinsi' => $request->input('provinsi', $existing?->provinsi),
            'agama' => $request->input('agama', $existing?->agama),
            'status_perkawinan' => $request->input('status_perkawinan', $existing?->status_perkawinan),
            'pekerjaan_ktp' => $request->input('pekerjaan_ktp', $existing?->pekerjaan_ktp),
            'kewarganegaraan' => $request->input('kewarganegaraan', $existing?->kewarganegaraan ?? 'WNI'),
        ];
    }

    /**
     * Aturan validasi Karyawan (termasuk larangan karakter aneh pada NIK & Nama serta validasi data KTP)
     */
    private function validationRules(?int $ignoreId = null): array
    {
        $nikUnique = $ignoreId
            ? Rule::unique('employees', 'nik')->ignore($ignoreId)
            : Rule::unique('employees', 'nik');

        $nikKtpUnique = $ignoreId
            ? Rule::unique('employees', 'nik_ktp')->ignore($ignoreId)
            : Rule::unique('employees', 'nik_ktp');

        return [
            // NIK hanya boleh huruf, angka, dan tanda hubung (-) tanpa spasi/simbol aneh
            'nik' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/', $nikUnique],
            // Nama karyawan hanya boleh huruf, spasi, titik, koma, dan apostrof (tanpa angka/simbol aneh)
            'nama' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\']+$/'],
            'jabatan' => ['required', 'string', 'max:50'],
            'divisi' => ['required', 'string', 'max:50'],
            'status_kerja' => ['required', 'in:aktif,nonaktif'],
            'tanggal_aktif' => ['nullable', 'date'],
            'tanggal_berhenti' => ['nullable', 'date'],
            // Validasi Data Diri KTP
            'nik_ktp' => ['nullable', 'string', 'regex:/^[0-9]{16}$/', $nikKtpUnique],
            'tempat_lahir' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\'\-]+$/'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'golongan_darah' => ['nullable', 'in:A,B,AB,O,-'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'rt_rw' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\/\-]+$/'],
            'kelurahan_desa' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'agama' => ['nullable', 'string', 'max:30'],
            'status_perkawinan' => ['nullable', 'string', 'max:30'],
            'pekerjaan_ktp' => ['nullable', 'string', 'max:100'],
            'kewarganegaraan' => ['nullable', 'in:WNI,WNA'],
        ];
    }

    /**
     * Pesan error validasi Bahasa Indonesia
     */
    private function validationMessages(): array
    {
        return [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah terdaftar.',
            'nik.regex' => 'NIK tidak boleh mengandung spasi atau karakter aneh (hanya huruf, angka, dan tanda hubung "-").',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'nama.regex' => 'Nama karyawan tidak boleh mengandung angka atau karakter aneh (hanya huruf, spasi, titik, koma, dan tanda petik).',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'status_kerja.required' => 'Status kerja wajib diisi.',
            'status_kerja.in' => 'Status kerja harus bernilai aktif atau nonaktif.',
            'tanggal_aktif.date' => 'Format tanggal aktif tidak valid.',
            'tanggal_berhenti.date' => 'Format tanggal berhenti tidak valid.',
            'nik_ktp.regex' => 'NIK KTP harus terdiri dari 16 digit angka tanpa huruf atau simbol.',
            'nik_ktp.unique' => 'NIK KTP sudah terdaftar pada karyawan lain.',
            'tempat_lahir.regex' => 'Tempat lahir tidak boleh mengandung karakter aneh.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
            'golongan_darah.in' => 'Golongan darah harus A, B, AB, O, atau -.',
            'rt_rw.regex' => 'Format RT/RW hanya boleh berisi angka dan garis miring (contoh: 003/005).',
            'kewarganegaraan.in' => 'Kewarganegaraan harus WNI atau WNA.',
        ];
    }

    /**
     * GET /api/employees & GET /api/karyawan
     * - HR: Dapat mengambil seluruh data karyawan beserta data KTP & riwayat tanggal status
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

        if (! $auth['is_hr']) {
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

            $ownEmployee = DatabaseSchemaGuard::run(
                fn () => Employee::with('statusHistories')->where('id', $auth['employee_id'])->get()
            );

            return response()->json([
                'success' => true,
                'message' => 'Data karyawan milik Anda berhasil diambil.',
                'data' => $ownEmployee,
            ], 200);
        }

        $employees = DatabaseSchemaGuard::run(function () use ($statusFilter) {
            $query = Employee::with('statusHistories');

            if ($statusFilter) {
                $query->where('status_kerja', $statusFilter);
            }

            $list = $query->orderBy('id', 'asc')->get();

            if ($list->isNotEmpty() && ! array_key_exists('nama', $list->first()->getAttributes())) {
                DatabaseSchemaGuard::ensureSchema(true);
                $query = Employee::with('statusHistories');
                if ($statusFilter) {
                    $query->where('status_kerja', $statusFilter);
                }
                $list = $query->orderBy('id', 'asc')->get();
            }

            return $list;
        });

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

        $karyawanAktif = DatabaseSchemaGuard::run(fn () => Employee::with('statusHistories')
            ->where('status_kerja', 'aktif')
            ->orderBy('id', 'asc')
            ->get());

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar karyawan aktif.',
            'data' => $karyawanAktif,
        ], 200);
    }

    /**
     * GET /api/employees/{id} & GET /api/karyawan/{id}
     * - HR: Bebas melihat detail karyawan siapa saja (lengkap dengan KTP & riwayat status)
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

        $employee = DatabaseSchemaGuard::run(fn () => Employee::with('statusHistories')->find($id));

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
     * Menyimpan data karyawan baru + data KTP + mencatat riwayat tanggal aktif — Hanya HR
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

        $input = $this->extractEmployeeInput($request);

        $validator = Validator::make($input, $this->validationRules(), $this->validationMessages());

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
        $validated['address'] = $validated['alamat'] ?? null;
        $validated['tanggal_aktif'] = $validated['tanggal_aktif'] ?? now()->toDateString();

        $actorName = $auth['user']->name ?? 'Admin HR';
        $keteranganStatus = $request->input('keterangan_status', 'Karyawan terdaftar dan mulai aktif bekerja.');

        $employee = DatabaseSchemaGuard::run(function () use ($validated, $actorName, $keteranganStatus) {
            $emp = Employee::create($validated);
            $emp->recordStatusHistory(
                $emp->status_kerja,
                $emp->status_kerja === 'aktif' ? $emp->tanggal_aktif : ($emp->tanggal_berhenti ?: now()->toDateString()),
                $keteranganStatus,
                $actorName
            );

            return $emp->load('statusHistories');
        });

        return response()->json([
            'success' => true,
            'message' => 'Informasi kepegawaian dan data diri KTP berhasil disimpan.',
            'data' => $employee,
        ], 201);
    }

    /**
     * PUT|PATCH /api/employees/{id} & PUT|PATCH /api/karyawan/{id}
     * Memperbarui data karyawan, data KTP, serta otomatis mencatat riwayat jika status berubah — Hanya HR
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

        $employee = DatabaseSchemaGuard::run(fn () => Employee::find($id));

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $oldStatus = $employee->status_kerja;
        $input = $this->extractEmployeeInput($request, $employee);

        $validator = Validator::make($input, $this->validationRules((int) $employee->id), $this->validationMessages());

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
        $validated['address'] = $validated['alamat'] ?? null;

        $actorName = $auth['user']->name ?? 'Admin HR';

        DatabaseSchemaGuard::run(function () use ($employee, $validated, $oldStatus, $request, $actorName) {
            // Jika diaktifkan kembali setelah sebelumnya nonaktif
            if ($oldStatus === 'nonaktif' && $validated['status_kerja'] === 'aktif') {
                $validated['tanggal_aktif'] = $request->input('tanggal_aktif') ?: now()->toDateString();
                $validated['tanggal_berhenti'] = null;
                $employee->update($validated);

                $employee->recordStatusHistory(
                    'aktif',
                    $validated['tanggal_aktif'],
                    $request->input('keterangan_status', 'Karyawan diaktifkan kembali setelah sebelumnya nonaktif.'),
                    $actorName
                );
            } elseif ($oldStatus === 'aktif' && $validated['status_kerja'] === 'nonaktif') {
                $validated['tanggal_berhenti'] = $request->input('tanggal_berhenti') ?: now()->toDateString();
                $employee->update($validated);

                $employee->recordStatusHistory(
                    'nonaktif',
                    $validated['tanggal_berhenti'],
                    $request->input('keterangan_status', 'Karyawan dinonaktifkan.'),
                    $actorName
                );
            } else {
                $employee->update($validated);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $employee->fresh('statusHistories'),
        ], 200);
    }

    /**
     * PATCH /api/employees/{id}/deactivate & PATCH /api/karyawan/{id}/deactivate
     * Mengubah status karyawan menjadi nonaktif dan mencatat tanggal berhenti di riwayat — HANYA HR (Poin 6)
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
            'keterangan' => 'nullable|string|max:255',
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

        $employee = DatabaseSchemaGuard::run(fn () => Employee::find($id));

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $actorName = $auth['user']->name ?? 'Admin HR';

        DatabaseSchemaGuard::run(function () use ($employee, $request, $actorName) {
            $employee->status_kerja = 'nonaktif';
            $employee->tanggal_berhenti = $request->tanggal_berhenti;
            $employee->save();

            $employee->recordStatusHistory(
                'nonaktif',
                $request->tanggal_berhenti,
                $request->input('keterangan', 'Karyawan dinonaktifkan.'),
                $actorName
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dinonaktifkan dan riwayat tanggal status tetap tersimpan.',
            'data' => $employee->fresh('statusHistories'),
        ], 200);
    }

    /**
     * PATCH /api/employees/{id}/activate & PATCH /api/karyawan/{id}/activate
     * Mengaktifkan kembali karyawan yang nonaktif dan mencatat tanggal aktif baru ke riwayat — HANYA HR
     */
    public function activate(Request $request, string $id): JsonResponse
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
                'message' => 'Akses ditolak: Hanya HR yang memiliki izin untuk mengaktifkan kembali karyawan.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_aktif' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal_aktif.date' => 'Format tanggal aktif tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi aktivasi karyawan gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = DatabaseSchemaGuard::run(fn () => Employee::find($id));

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $tanggalAktif = $request->input('tanggal_aktif') ?: now()->toDateString();
        $actorName = $auth['user']->name ?? 'Admin HR';

        DatabaseSchemaGuard::run(function () use ($employee, $tanggalAktif, $request, $actorName) {
            $employee->status_kerja = 'aktif';
            $employee->tanggal_aktif = $tanggalAktif;
            $employee->tanggal_berhenti = null;
            $employee->save();

            $employee->recordStatusHistory(
                'aktif',
                $tanggalAktif,
                $request->input('keterangan', 'Karyawan diaktifkan kembali setelah sebelumnya nonaktif.'),
                $actorName
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil diaktifkan kembali dan tanggal aktif baru telah tercatat di riwayat.',
            'data' => $employee->fresh('statusHistories'),
        ], 200);
    }

    /**
     * GET /api/employees/{id}/status-histories & GET /api/karyawan/{id}/status-histories
     * Mengambil riwayat tanggal aktif & nonaktif seorang karyawan
     */
    public function statusHistories(Request $request, string $id): JsonResponse
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
                'message' => 'Akses ditolak: Anda hanya diperbolehkan melihat riwayat status milik Anda sendiri.',
            ], 403);
        }

        $employee = DatabaseSchemaGuard::run(fn () => Employee::with('statusHistories')->find($id));

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Riwayat status aktif/nonaktif karyawan berhasil diambil.',
            'data' => [
                'employee_id' => $employee->id,
                'nik' => $employee->nik,
                'nama' => $employee->nama,
                'status_saat_ini' => $employee->status_kerja,
                'tanggal_aktif_terakhir' => $employee->tanggal_aktif,
                'tanggal_berhenti_terakhir' => $employee->tanggal_berhenti,
                'riwayat_status' => $employee->statusHistories,
            ],
        ], 200);
    }
}
