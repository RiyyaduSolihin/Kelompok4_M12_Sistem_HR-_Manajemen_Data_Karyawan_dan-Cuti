<?php

use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\KaryawanController;
use Illuminate\Support\Facades\Route;

// Endpoint CRUD Karyawan (M12-US1)
Route::apiResource('employees', EmployeeController::class)->except(['destroy']);

// Endpoint untuk menonaktifkan karyawan berdasarkan ID (M12-US2)
Route::patch('/karyawan/{id}/deactivate', [KaryawanController::class, 'deactivate']);
Route::patch('/employees/{id}/deactivate', [EmployeeController::class, 'deactivate']);
