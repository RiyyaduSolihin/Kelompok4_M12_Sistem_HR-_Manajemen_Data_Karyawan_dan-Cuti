<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KaryawanController extends Controller
{
    /**
     * Mengubah status karyawan menjadi nonaktif dan mencatat tanggal berhenti
     * Sesuai AC M12-US2: Riwayat tetap tersimpan tanpa perintah DELETE (hard delete).
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        // Validasi input tanggal berhenti wajib diisi dengan format tanggal yang valid
        $request->validate([
            'tanggal_berhenti' => 'required|date',
        ], [
            'tanggal_berhenti.required' => 'Tanggal berhenti wajib diisi.',
            'tanggal_berhenti.date' => 'Format tanggal berhenti tidak valid.',
        ]);

        // Cari data karyawan berdasarkan ID
        $karyawan = Karyawan::findOrFail($id);

        // Ubah status kerja dan catat tanggal berhenti
        $karyawan->status_kerja = 'nonaktif';
        $karyawan->tanggal_berhenti = $request->tanggal_berhenti;
        $karyawan->save();

        // Redirect atau kirim respons JSON (sesuai stack yang kelompok Anda gunakan)
        return response()->json([
            'success' => true,
            'message' => 'Karyawan berhasil dinonaktifkan dan riwayat kepegawaian tetap tersimpan.',
            'data' => $karyawan,
        ], 200);
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
