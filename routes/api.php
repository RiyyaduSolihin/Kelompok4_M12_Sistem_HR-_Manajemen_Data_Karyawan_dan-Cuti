<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Endpoint Autentikasi & Perjanjian User / Role (Lintas 4 Modul)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/me', [AuthController::class, 'me']);
Route::get('/auth/me', [AuthController::class, 'me']);
Route::get('/user', [AuthController::class, 'me']);

Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::get('/users', [AuthController::class, 'users']);

/*
|--------------------------------------------------------------------------
| 2. Endpoint Karyawan Aktif — HANYA HR (Poin 4 & Poin 8)
|    (Diselaraskan antara /api/employees/* dan /api/karyawan/*)
|--------------------------------------------------------------------------
*/
Route::get('/employees/active', [EmployeeController::class, 'active']);
Route::get('/employees/aktif', [EmployeeController::class, 'active']);
Route::get('/karyawan/aktif', [EmployeeController::class, 'active']);
Route::get('/karyawan/active', [EmployeeController::class, 'active']);

/*
|--------------------------------------------------------------------------
| 3. Endpoint Deaktivasi Karyawan — HANYA HR (Poin 6)
|--------------------------------------------------------------------------
*/
Route::patch('/employees/{id}/deactivate', [EmployeeController::class, 'deactivate']);
Route::patch('/karyawan/{id}/deactivate', [EmployeeController::class, 'deactivate']);

/*
|--------------------------------------------------------------------------
| 4. Endpoint CRUD Employees & Karyawan — Identik 100% (Poin 3, 5, 7)
|    - GET /api/employees & /api/karyawan: HR (semua), User biasa (diri sendiri)
|    - GET /api/employees/{id} & /api/karyawan/{id}: HR (semua), User biasa (diri sendiri)
|    - POST & PUT/PATCH: Hanya HR
|--------------------------------------------------------------------------
*/
Route::apiResource('employees', EmployeeController::class)->names('api.employees')->except(['destroy']);
Route::apiResource('karyawan', EmployeeController::class)->names('api.karyawan')->parameters([
    'karyawan' => 'employee',
])->except(['destroy']);
