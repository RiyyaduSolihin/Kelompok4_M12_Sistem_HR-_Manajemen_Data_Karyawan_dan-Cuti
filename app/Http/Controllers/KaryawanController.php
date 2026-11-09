<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Karyawan;
use App\Support\DatabaseSchemaGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    /**
     * Menampilkan daftar seluruh karyawan di antarmuka web
     */
    public function index()
    {
        $employees = DatabaseSchemaGuard::run(function () {
            $list = Employee::with('statusHistories')->orderBy('id', 'asc')->paginate(11);

            if ($list->isEmpty() || ! array_key_exists('nama', $list->first()->getAttributes())) {
                DatabaseSchemaGuard::ensureSchema(true);
                $list = Employee::with('statusHistories')->orderBy('id', 'asc')->paginate(11);
            }

            return $list;
        });

        return view('employees.index', compact('employees'));
    }

    /**
     * Menyimpan data karyawan baru dari form web
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/', 'unique:employees,nik'],
            'nama' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\']+$/'],
            'jabatan' => 'required|string|max:50',
            'divisi' => 'required|string|max:50',
            'status_kerja' => 'required|in:aktif,nonaktif',
            'tanggal_aktif' => 'nullable|date',
            'nik_ktp' => ['nullable', 'string', 'regex:/^[0-9]{16}$/', 'unique:employees,nik_ktp'],
            'tempat_lahir' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\'\-]+$/'],
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'golongan_darah' => 'nullable|in:A,B,AB,O,-',
            'alamat' => 'nullable|string|max:500',
            'rt_rw' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\/\-]+$/'],
            'kelurahan_desa' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'agama' => 'nullable|string|max:30',
            'status_perkawinan' => 'nullable|string|max:30',
            'pekerjaan_ktp' => 'nullable|string|max:100',
            'kewarganegaraan' => 'nullable|in:WNI,WNA',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah terdaftar.',
            'nik.regex' => 'NIK tidak boleh mengandung spasi atau karakter aneh (hanya huruf, angka, dan tanda hubung "-").',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'nama.regex' => 'Nama karyawan tidak boleh mengandung angka atau karakter aneh (hanya huruf, spasi, titik, koma, dan tanda petik).',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'status_kerja.required' => 'Status kerja wajib diisi.',
            'nik_ktp.regex' => 'NIK KTP harus terdiri dari 16 digit angka.',
            'nik_ktp.unique' => 'NIK KTP sudah terdaftar pada karyawan lain.',
        ]);

        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];
        $validated['address'] = $validated['alamat'] ?? null;
        $validated['tanggal_aktif'] = $validated['tanggal_aktif'] ?? now()->toDateString();

        DatabaseSchemaGuard::run(function () use ($validated) {
            $emp = Employee::create($validated);
            $emp->recordStatusHistory(
                $emp->status_kerja,
                $emp->tanggal_aktif,
                'Karyawan terdaftar dan mulai aktif bekerja.',
                'Admin HR'
            );
        });

        return redirect('/')->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data karyawan dari form web
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);
        $oldStatus = $employee->status_kerja;

        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/', 'unique:employees,nik,' . $employee->id],
            'nama' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\']+$/'],
            'jabatan' => 'required|string|max:50',
            'divisi' => 'required|string|max:50',
            'status_kerja' => 'required|in:aktif,nonaktif',
            'tanggal_aktif' => 'nullable|date',
            'nik_ktp' => ['nullable', 'string', 'regex:/^[0-9]{16}$/', 'unique:employees,nik_ktp,' . $employee->id],
            'tempat_lahir' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\'\-]+$/'],
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'golongan_darah' => 'nullable|in:A,B,AB,O,-',
            'alamat' => 'nullable|string|max:500',
            'rt_rw' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\/\-]+$/'],
            'kelurahan_desa' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten_kota' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'agama' => 'nullable|string|max:30',
            'status_perkawinan' => 'nullable|string|max:30',
            'pekerjaan_ktp' => 'nullable|string|max:100',
            'kewarganegaraan' => 'nullable|in:WNI,WNA',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah digunakan karyawan lain.',
            'nik.regex' => 'NIK tidak boleh mengandung spasi atau karakter aneh (hanya huruf, angka, dan tanda hubung "-").',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'nama.regex' => 'Nama karyawan tidak boleh mengandung angka atau karakter aneh (hanya huruf, spasi, titik, koma, dan tanda petik).',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'status_kerja.required' => 'Status kerja wajib diisi.',
            'nik_ktp.regex' => 'NIK KTP harus terdiri dari 16 digit angka.',
            'nik_ktp.unique' => 'NIK KTP sudah terdaftar pada karyawan lain.',
        ]);

        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];
        if (array_key_exists('alamat', $validated)) {
            $validated['address'] = $validated['alamat'];
        }

        DatabaseSchemaGuard::run(function () use ($employee, $validated, $oldStatus, $request) {
            if ($oldStatus === 'nonaktif' && $validated['status_kerja'] === 'aktif') {
                $validated['tanggal_aktif'] = $request->input('tanggal_aktif') ?: now()->toDateString();
                $validated['tanggal_berhenti'] = null;
                $employee->update($validated);
                $employee->recordStatusHistory(
                    'aktif',
                    $validated['tanggal_aktif'],
                    'Karyawan diaktifkan kembali setelah sebelumnya nonaktif.',
                    'Admin HR'
                );
            } elseif ($oldStatus === 'aktif' && $validated['status_kerja'] === 'nonaktif') {
                $validated['tanggal_berhenti'] = $request->input('tanggal_berhenti') ?: now()->toDateString();
                $employee->update($validated);
                $employee->recordStatusHistory(
                    'nonaktif',
                    $validated['tanggal_berhenti'],
                    'Karyawan dinonaktifkan.',
                    'Admin HR'
                );
            } else {
                $employee->update($validated);
            }
        });

        return redirect('/')->with('success', "Data karyawan {$employee->nama} berhasil diperbarui.");
    }

    /**
     * Mengubah status karyawan menjadi nonaktif dan mencatat tanggal berhenti di riwayat
     */
    public function deactivate(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berhenti' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal_berhenti.required' => 'Tanggal berhenti wajib diisi.',
            'tanggal_berhenti.date' => 'Format tanggal berhenti tidak valid.',
        ]);

        $karyawan = DatabaseSchemaGuard::run(function () use ($id, $request) {
            $emp = Employee::findOrFail($id);
            $emp->status_kerja = 'nonaktif';
            $emp->tanggal_berhenti = $request->tanggal_berhenti;
            $emp->save();

            $emp->recordStatusHistory(
                'nonaktif',
                $request->tanggal_berhenti,
                $request->input('keterangan', 'Karyawan dinonaktifkan.'),
                'Admin HR'
            );

            return $emp->load('statusHistories');
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dinonaktifkan dan riwayat kepegawaian tetap tersimpan.',
                'data' => $karyawan,
            ], 200);
        }

        return redirect('/')->with('success', "Karyawan {$karyawan->nama} berhasil dinonaktifkan.");
    }

    /**
     * Mengaktifkan kembali karyawan yang nonaktif dan mencatat tanggal aktif baru di riwayat
     */
    public function activate(Request $request, string $id)
    {
        $request->validate([
            'tanggal_aktif' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal_aktif.date' => 'Format tanggal aktif tidak valid.',
        ]);

        $tanggalAktif = $request->input('tanggal_aktif') ?: now()->toDateString();

        $karyawan = DatabaseSchemaGuard::run(function () use ($id, $tanggalAktif, $request) {
            $emp = Employee::findOrFail($id);
            $emp->status_kerja = 'aktif';
            $emp->tanggal_aktif = $tanggalAktif;
            $emp->tanggal_berhenti = null;
            $emp->save();

            $emp->recordStatusHistory(
                'aktif',
                $tanggalAktif,
                $request->input('keterangan', 'Karyawan diaktifkan kembali setelah sebelumnya nonaktif.'),
                'Admin HR'
            );

            return $emp->load('statusHistories');
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diaktifkan kembali dan tanggal aktif telah tercatat di riwayat.',
                'data' => $karyawan,
            ], 200);
        }

        return redirect('/')->with('success', "Karyawan {$karyawan->nama} berhasil diaktifkan kembali.");
    }

    /**
     * Endpoint khusus penyedia data karyawan aktif untuk M13 dan M14
     */
    public function getActiveKaryawan(): JsonResponse
    {
        $karyawanAktif = DatabaseSchemaGuard::run(function () {
            return Karyawan::with('statusHistories')
                ->where('status_kerja', 'aktif')
                ->get();
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar karyawan aktif.',
            'data' => $karyawanAktif
        ], 200);
    }
}
