<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

// Halaman utama data karyawan
Route::get('/', [KaryawanController::class, 'index'])->name('employees.index');

// Menyimpan data karyawan baru
Route::post('/karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');

// Memperbarui data karyawan
Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');

// Menyimpan / memperbarui data KTP karyawan
Route::put('/karyawan/{id}/ktp', [KaryawanController::class, 'updateKtp'])->name('karyawan.ktp');

// Menonaktifkan karyawan
Route::patch('/karyawan/{id}/deactivate', [KaryawanController::class, 'deactivate'])->name('karyawan.deactivate');

// Mengaktifkan kembali karyawan yang nonaktif
Route::patch('/karyawan/{id}/activate', [KaryawanController::class, 'activate'])->name('karyawan.activate');

// Endpoint kontrak data khusus karyawan aktif (dikonsumsi M13 & M14 — Hanya HR)
Route::get('/karyawan/aktif', [EmployeeController::class, 'active']);

// Halaman Dokumentasi API (Swagger UI)
Route::view('/api/documentation', 'swagger')->name('swagger.docs');
Route::view('/api/docs', 'swagger');
Route::view('/docs', 'swagger');
