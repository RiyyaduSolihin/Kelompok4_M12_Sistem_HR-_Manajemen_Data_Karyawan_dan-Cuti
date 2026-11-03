<?php

namespace Database\Seeders;

use App\Support\DatabaseSchemaGuard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data master karyawan & akun login (HRD & Karyawan) lintas modul (M12, M13, M14).
     */
    public function run(): void
    {
        DatabaseSchemaGuard::ensureSchema(true);

        // 1. Seed Data Karyawan HRD & Karyawan Biasa
        $hrEmp = DB::table('employees')->where('nik', '2026000')->first();
        $hrEmpId = $hrEmp ? $hrEmp->id : DB::table('employees')->insertGetId([
            'nik' => '2026000',
            'nama' => 'Rani Kartika',
            'name' => 'Rani Kartika',
            'jabatan' => 'HR Manager',
            'position' => 'HR Manager',
            'divisi' => 'HRD',
            'department' => 'HRD',
            'status_kerja' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $staffEmp = DB::table('employees')->where('nik', '2026001')->first();
        $staffEmpId = $staffEmp ? $staffEmp->id : DB::table('employees')->insertGetId([
            'nik' => '2026001',
            'nama' => 'Andi Pratama',
            'name' => 'Andi Pratama',
            'jabatan' => 'Staf IT',
            'position' => 'Staf IT',
            'divisi' => 'IT',
            'department' => 'IT',
            'status_kerja' => 'aktif',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Seed Akun Login HRD (Role: Admin HR)
        DB::table('users')->updateOrInsert(
            ['email' => 'hr@araya.com'],
            [
                'name' => 'Rani Kartika',
                'password' => Hash::make('password123'),
                'role' => 'Admin HR',
                'employee_id' => $hrEmpId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // 3. Seed Akun Login Karyawan Biasa (Role: Employee)
        DB::table('users')->updateOrInsert(
            ['email' => 'andi@araya.com'],
            [
                'name' => 'Andi Pratama',
                'password' => Hash::make('password123'),
                'role' => 'Employee',
                'employee_id' => $staffEmpId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
