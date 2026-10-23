<?php

use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::patch('/karyawan/{id}/deactivate', [KaryawanController::class, 'deactivate'])->name('karyawan.deactivate');
