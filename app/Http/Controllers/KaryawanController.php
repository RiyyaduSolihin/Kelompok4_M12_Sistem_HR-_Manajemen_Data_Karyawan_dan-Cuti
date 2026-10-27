<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Karyawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    /**
     * Menampilkan daftar seluruh karyawan di antarmuka web
     */
    public function index()
    {
        $employees = Employee::orderBy('id', 'asc')->get();
        return view('employees.index', compact('employees'));
    }

    /**
     * Menyimpan data karyawan baru dari form web
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
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
        ]);

        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];

        Employee::create($validated);

        return redirect('/')->with('success', 'Karyawan baru berhasil ditambahkan.');
    }

    /**
     * Memperbarui data karyawan dari form web
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'nik' => 'required|string|max:30|unique:employees,nik,' . $employee->id,
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

        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];

        $employee->update($validated);

        return redirect('/')->with('success', "Data karyawan {$employee->nama} berhasil diperbarui.");
    }

    /**
     * Mengubah status karyawan menjadi nonaktif dan mencatat tanggal berhenti
     * Sesuai AC M12-US2: Riwayat tetap tersimpan tanpa perintah DELETE (hard delete).
     */
    public function deactivate(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berhenti' => 'required|date',
        ], [
            'tanggal_berhenti.required' => 'Tanggal berhenti wajib diisi.',
            'tanggal_berhenti.date' => 'Format tanggal berhenti tidak valid.',
        ]);

        $karyawan = Employee::findOrFail($id);
        $karyawan->status_kerja = 'nonaktif';
        $karyawan->tanggal_berhenti = $request->tanggal_berhenti;
        $karyawan->save();

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
     * Endpoint khusus penyedia data karyawan aktif untuk M13 dan M14
     */
    public function getActiveKaryawan(): JsonResponse
    {
        // Query untuk mengambil karyawan yang HANYA berstatus 'aktif'
        // Select digunakan untuk membatasi kolom agar payload API lebih ringan
        $karyawanAktif = Karyawan::where('status_kerja', 'aktif')
            ->select('id', 'nik', 'nama', 'jabatan', 'divisi', 'status_kerja')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar karyawan aktif.',
            'data' => $karyawanAktif
        ], 200);
    }
}
