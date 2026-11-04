<?php

namespace Database\Seeders;

use App\Support\DatabaseSchemaGuard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed ulang database bersih & konsisten untuk M12, M13, dan M14:
     * - Format NIK seragam: EMP-1001 s/d EMP-1008
     * - ID konsisten 1-to-1: users.id == employees.id == users.employee_id (tidak ada 1 user 2 ID)
     * - Relasi penuh ke tabel divisions & positions terbaru (M12-US6)
     */
    public function run(): void
    {
        // 1. Pastikan seluruh struktur tabel & kolom sudah sinkron
        DatabaseSchemaGuard::ensureSchema(true);

        // 2. Bersihkan data lama dan reset urutan ID kembali mulai dari 1
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE users, employees, positions, divisions RESTART IDENTITY CASCADE');
        } else {
            Schema::disableForeignKeyConstraints();
            DB::table('users')->truncate();
            DB::table('employees')->truncate();
            DB::table('positions')->truncate();
            DB::table('divisions')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        // 3. Seed Tabel Master Divisi (divisions)
        $divisionsData = [
            ['kode_divisi' => 'DIV-SDM', 'nama_divisi' => 'SDM', 'name' => 'SDM', 'deskripsi' => 'Sumber Daya Manusia & HRD'],
            ['kode_divisi' => 'DIV-TI', 'nama_divisi' => 'Teknologi Informasi', 'name' => 'Teknologi Informasi', 'deskripsi' => 'Pengembangan Sistem & Infrastruktur IT'],
            ['kode_divisi' => 'DIV-KEU', 'nama_divisi' => 'Keuangan', 'name' => 'Keuangan', 'deskripsi' => 'Keuangan, Pajak & Akuntansi'],
            ['kode_divisi' => 'DIV-OPS', 'nama_divisi' => 'Operasional', 'name' => 'Operasional', 'deskripsi' => 'Operasional Harian Perusahaan'],
            ['kode_divisi' => 'DIV-LOG', 'nama_divisi' => 'Logistik', 'name' => 'Logistik', 'deskripsi' => 'Pergudangan & Distribusi Barang'],
        ];

        $divMap = [];
        foreach ($divisionsData as $div) {
            $divMap[$div['nama_divisi']] = DB::table('divisions')->insertGetId(array_merge($div, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 4. Seed Tabel Master Jabatan (positions)
        $hrManagerPosId = DB::table('positions')->insertGetId([
            'division_id' => $divMap['SDM'],
            'nama_jabatan' => 'HR Manager',
            'name' => 'HR Manager',
            'level_jabatan' => 'Manager',
            'parent_position_id' => null,
            'atasan' => 'Direktur Utama',
            'deskripsi' => 'Manajer Sumber Daya Manusia (Admin HR)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $positionsData = [
            [
                'division' => 'Teknologi Informasi',
                'nama_jabatan' => 'Software Engineer',
                'level_jabatan' => 'Staff',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
            [
                'division' => 'Keuangan',
                'nama_jabatan' => 'Staff Keuangan',
                'level_jabatan' => 'Staff',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
            [
                'division' => 'Teknologi Informasi',
                'nama_jabatan' => 'Analis Data',
                'level_jabatan' => 'Staff',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
            [
                'division' => 'SDM',
                'nama_jabatan' => 'Spesialis Rekrutmen',
                'level_jabatan' => 'Supervisor',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
            [
                'division' => 'Operasional',
                'nama_jabatan' => 'Staff Operasional',
                'level_jabatan' => 'Staff',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
            [
                'division' => 'Logistik',
                'nama_jabatan' => 'Admin Gudang',
                'level_jabatan' => 'Staff',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
            [
                'division' => 'Logistik',
                'nama_jabatan' => 'Spesialis Logistik',
                'level_jabatan' => 'Supervisor',
                'parent_position_id' => $hrManagerPosId,
                'atasan' => 'HR Manager',
            ],
        ];

        $posMap = ['HR Manager' => $hrManagerPosId];
        foreach ($positionsData as $pos) {
            $posMap[$pos['nama_jabatan']] = DB::table('positions')->insertGetId([
                'division_id' => $divMap[$pos['division']],
                'nama_jabatan' => $pos['nama_jabatan'],
                'name' => $pos['nama_jabatan'],
                'level_jabatan' => $pos['level_jabatan'],
                'parent_position_id' => $pos['parent_position_id'],
                'atasan' => $pos['atasan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Seed Tabel Karyawan (employees) & Akun Login (users) secara 1-to-1 (ID 1 s/d 8)
        $passwordHash = Hash::make('password123');

        $records = [
            [
                'nik' => 'EMP-1001',
                'nama' => 'Rani Kusumawati',
                'email' => 'hr@araya.com',
                'role' => 'Admin HR',
                'jabatan' => 'HR Manager',
                'divisi' => 'SDM',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1002',
                'nama' => 'Andi Pratama',
                'email' => 'andi@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Software Engineer',
                'divisi' => 'Teknologi Informasi',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1003',
                'nama' => 'Sinta Maharani',
                'email' => 'sinta@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Staff Keuangan',
                'divisi' => 'Keuangan',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1004',
                'nama' => 'Budi Santoso',
                'email' => 'budi@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Analis Data',
                'divisi' => 'Teknologi Informasi',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1005',
                'nama' => 'Lestari Wulandari',
                'email' => 'lestari@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Spesialis Rekrutmen',
                'divisi' => 'SDM',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1006',
                'nama' => 'Dewi Anggraini',
                'email' => 'dewi@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Staff Operasional',
                'divisi' => 'Operasional',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1007',
                'nama' => 'Rizky Pratama',
                'email' => 'rizky@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Admin Gudang',
                'divisi' => 'Logistik',
                'status_kerja' => 'aktif',
                'tanggal_berhenti' => null,
            ],
            [
                'nik' => 'EMP-1008',
                'nama' => 'Ahmad Fauzi',
                'email' => 'ahmad@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Spesialis Logistik',
                'divisi' => 'Logistik',
                'status_kerja' => 'nonaktif',
                'tanggal_berhenti' => '2026-10-15',
            ],
        ];

        $hasEmailColumnOnEmployees = Schema::hasColumn('employees', 'email');
        $hasStatusColumnOnEmployees = Schema::hasColumn('employees', 'status');

        foreach ($records as $row) {
            $empPayload = [
                'nik' => $row['nik'],
                'nama' => $row['nama'],
                'name' => $row['nama'],
                'jabatan' => $row['jabatan'],
                'position' => $row['jabatan'],
                'divisi' => $row['divisi'],
                'department' => $row['divisi'],
                'division_id' => $divMap[$row['divisi']] ?? null,
                'position_id' => $posMap[$row['jabatan']] ?? null,
                'status_kerja' => $row['status_kerja'],
                'tanggal_berhenti' => $row['tanggal_berhenti'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasEmailColumnOnEmployees) {
                $empPayload['email'] = $row['email'];
            }
            if ($hasStatusColumnOnEmployees) {
                $empPayload['status'] = $row['status_kerja'];
            }

            $empId = DB::table('employees')->insertGetId($empPayload);

            DB::table('users')->insert([
                'name' => $row['nama'],
                'email' => $row['email'],
                'password' => $passwordHash,
                'role' => $row['role'],
                'employee_id' => $empId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
