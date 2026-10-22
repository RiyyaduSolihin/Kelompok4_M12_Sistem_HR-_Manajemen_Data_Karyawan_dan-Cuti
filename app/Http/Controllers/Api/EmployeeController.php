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
     * GET /api/employees
     * Menampilkan daftar karyawan (bisa difilter berdasarkan status_kerja)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();

        if ($request->has('status_kerja')) {
            $query->where('status_kerja', $request->status_kerja);
        }

        $employees = $query->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar data karyawan berhasil diambil.',
            'data' => $employees,
        ], 200);
    }

    /**
     * POST /api/employees
     * Menyimpan data karyawan baru (M12-US1: AC 1 & AC 2)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:30|unique:employees,nik',
            'nama' => 'required|string|max:100',
            'jabatan' => 'required|string|max:50',
            'divisi' => 'required|string|max:50',
            'status_kerja' => 'required|in:aktif,nonaktif',
        ], [
            // Pesan kustom untuk validasi input kosong & duplikasi NIK
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah terdaftar.',
            'nama.required' => 'Nama karyawan wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi.',
            'status_kerja.required' => 'Status kerja wajib diisi.',
            'status_kerja.in' => 'Status kerja harus bernilai aktif atau nonaktif.',
        ]);

        // Jika validasi gagal (NIK duplikat atau input kosong)
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data karyawan. Periksa kembali input Anda.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // AC 1: Given data karyawan lengkap, When disimpan, Then informasi kepegawaian tersimpan rapi
        $employee = Employee::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Informasi kepegawaian berhasil disimpan.',
            'data' => $employee,
        ], 201);
    }

    /**
     * GET /api/employees/{id}
     * Menampilkan detail 1 karyawan
     */
    public function show(string $id): JsonResponse
    {
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
     * PUT /api/employees/{id}
     * Memperbarui data karyawan (Tetap cek NIK unik kecuali milik dirinya sendiri)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $employee = Employee::find($id);

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
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

        $employee->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data karyawan berhasil diperbarui.',
            'data' => $employee,
        ], 200);
    }
}
