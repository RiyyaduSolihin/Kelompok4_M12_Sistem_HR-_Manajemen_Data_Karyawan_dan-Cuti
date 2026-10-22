<?php

use App\Http\Controllers\Api\EmployeeController;
use Illuminate\Support\Facades\Route;

// Endpoint CRUD Karyawan (M12-US1)
Route::apiResource('employees', EmployeeController::class)->except(['destroy']);
