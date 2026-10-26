<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('Employee');
            }
            if (! Schema::hasColumn('users', 'employee_id')) {
                $table->foreignId('employee_id')
                    ->nullable()
                    ->constrained('employees')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('users', 'api_token')) {
                $table->string('api_token', 255)->nullable()->index();
            }
        });

        // Seed data contoh karyawan & akun login kesepakatan 4 modul (HR & Employee)
        $hrEmp = DB::table('employees')->where('nik', '2026000')->first();
        if (! $hrEmp) {
            $hrEmpId = DB::table('employees')->insertGetId([
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
        } else {
            $hrEmpId = $hrEmp->id;
        }

        $staffEmp = DB::table('employees')->where('nik', '2026001')->first();
        if (! $staffEmp) {
            $staffEmpId = DB::table('employees')->insertGetId([
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
        } else {
            $staffEmpId = $staffEmp->id;
        }

        // Buat / perbarui akun Admin HR
        if (! DB::table('users')->where('email', 'hr@araya.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Rani Kartika',
                'email' => 'hr@araya.com',
                'password' => Hash::make('password123'),
                'role' => 'Admin HR',
                'employee_id' => $hrEmpId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'hr@araya.com')->update([
                'role' => 'Admin HR',
                'employee_id' => $hrEmpId,
            ]);
        }

        // Buat / perbarui akun Employee (Karyawan biasa)
        if (! DB::table('users')->where('email', 'andi@araya.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Andi Pratama',
                'email' => 'andi@araya.com',
                'password' => Hash::make('password123'),
                'role' => 'Employee',
                'employee_id' => $staffEmpId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->where('email', 'andi@araya.com')->update([
                'role' => 'Employee',
                'employee_id' => $staffEmpId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropConstrainedForeignId('employee_id');
            }
            if (Schema::hasColumn('users', 'api_token')) {
                $table->dropColumn('api_token');
            }
        });
    }
};
