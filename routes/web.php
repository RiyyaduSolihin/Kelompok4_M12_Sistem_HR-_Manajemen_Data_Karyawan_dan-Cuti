<?php

use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

// Halaman utama data karyawan
Route::get('/', [KaryawanController::class, 'index'])->name('employees.index');

// Menyimpan data karyawan baru
Route::post('/karyawan', [KaryawanController::class, 'store'])->name('karyawan.store');

// Memperbarui data karyawan
Route::put('/karyawan/{id}', [KaryawanController::class, 'update'])->name('karyawan.update');

// Menonaktifkan karyawan
Route::patch('/karyawan/{id}/deactivate', [KaryawanController::class, 'deactivate'])->name('karyawan.deactivate');

// Endpoint kontrak data khusus karyawan aktif (dikonsumsi M13 & M14)
Route::get('/karyawan/aktif', [KaryawanController::class, 'getActiveKaryawan']);
Route::get('/api/karyawan/aktif', [KaryawanController::class, 'getActiveKaryawan']);
