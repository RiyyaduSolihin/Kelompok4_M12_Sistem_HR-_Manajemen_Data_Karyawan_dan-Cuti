<?php

use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::patch('/karyawan/{id}/deactivate', [KaryawanController::class, 'deactivate'])->name('karyawan.deactivate');

// Endpoint kontrak data khusus karyawan aktif (dikonsumsi M13 & M14)
Route::get('/karyawan/aktif', [KaryawanController::class, 'getActiveKaryawan']);
Route::get('/api/karyawan/aktif', [KaryawanController::class, 'getActiveKaryawan']);
