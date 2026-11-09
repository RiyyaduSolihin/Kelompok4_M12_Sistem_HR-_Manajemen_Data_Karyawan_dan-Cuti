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
     * - Format NIK seragam: EMP-1001 s/d EMP-1008 (tanpa karakter aneh)
     * - ID konsisten 1-to-1: users.id == employees.id == users.employee_id
     * - Lengkap dengan Data Diri KTP, tanggal_aktif, dan tabel riwayat employee_status_histories
     */
    public function run(): void
    {
        // 1. Pastikan seluruh struktur tabel & kolom sudah sinkron
        DatabaseSchemaGuard::ensureSchema(true);

        // 2. Bersihkan data lama dan reset urutan ID kembali mulai dari 1
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE employee_status_histories, users, employees, positions, divisions RESTART IDENTITY CASCADE');
        } else {
            Schema::disableForeignKeyConstraints();
            if (Schema::hasTable('employee_status_histories')) {
                DB::table('employee_status_histories')->truncate();
            }
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

        // 5. Seed Tabel Karyawan (employees), Data KTP, Akun Login (users), dan Riwayat Status (ID 1 s/d 8)
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
                'tanggal_aktif' => '2024-01-10',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273015503920001',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1992-03-15',
                'jenis_kelamin' => 'Perempuan',
                'golongan_darah' => 'O',
                'alamat' => 'Jl. Dr. Setiabudhi No. 193',
                'rt_rw' => '002/005',
                'kelurahan_desa' => 'Gegerkalong',
                'kecamatan' => 'Sukasari',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-01-10', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                ],
            ],
            [
                'nik' => 'EMP-1002',
                'nama' => 'Andi Pratama',
                'email' => 'andi@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Software Engineer',
                'divisi' => 'Teknologi Informasi',
                'status_kerja' => 'aktif',
                'tanggal_aktif' => '2024-02-01',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273021207960002',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1996-07-12',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_darah' => 'A',
                'alamat' => 'Jl. Dago Asri No. 45',
                'rt_rw' => '004/008',
                'kelurahan_desa' => 'Dago',
                'kecamatan' => 'Coblong',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-02-01', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                ],
            ],
            [
                'nik' => 'EMP-1003',
                'nama' => 'Sinta Maharani',
                'email' => 'sinta@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Staff Keuangan',
                'divisi' => 'Keuangan',
                'status_kerja' => 'aktif',
                'tanggal_aktif' => '2024-03-01',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273036105970003',
                'tempat_lahir' => 'Cimahi',
                'tanggal_lahir' => '1997-05-21',
                'jenis_kelamin' => 'Perempuan',
                'golongan_darah' => 'B',
                'alamat' => 'Jl. Buah Batu No. 112',
                'rt_rw' => '001/003',
                'kelurahan_desa' => 'Turangga',
                'kecamatan' => 'Lengkong',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-03-01', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                ],
            ],
            [
                'nik' => 'EMP-1004',
                'nama' => 'Budi Santoso',
                'email' => 'budi@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Analis Data',
                'divisi' => 'Teknologi Informasi',
                'status_kerja' => 'aktif',
                'tanggal_aktif' => '2024-04-15',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273041809950004',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1995-09-18',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_darah' => 'AB',
                'alamat' => 'Jl. Soekarno Hatta No. 280',
                'rt_rw' => '005/011',
                'kelurahan_desa' => 'Sekejati',
                'kecamatan' => 'Buahbatu',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-04-15', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                ],
            ],
            [
                'nik' => 'EMP-1005',
                'nama' => 'Lestari Wulandari',
                'email' => 'lestari@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Spesialis Rekrutmen',
                'divisi' => 'SDM',
                'status_kerja' => 'aktif',
                'tanggal_aktif' => '2024-05-20',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273054911980005',
                'tempat_lahir' => 'Sumedang',
                'tanggal_lahir' => '1998-11-09',
                'jenis_kelamin' => 'Perempuan',
                'golongan_darah' => 'O',
                'alamat' => 'Jl. Cihampelas No. 88',
                'rt_rw' => '003/007',
                'kelurahan_desa' => 'Cipaganti',
                'kecamatan' => 'Coblong',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-05-20', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                ],
            ],
            [
                'nik' => 'EMP-1006',
                'nama' => 'Dewi Anggraini',
                'email' => 'dewi@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Staff Operasional',
                'divisi' => 'Operasional',
                'status_kerja' => 'aktif',
                'tanggal_aktif' => '2024-06-01',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273066501990006',
                'tempat_lahir' => 'Bogor',
                'tanggal_lahir' => '1999-01-25',
                'jenis_kelamin' => 'Perempuan',
                'golongan_darah' => 'A',
                'alamat' => 'Jl. Pasir Kaliki No. 64',
                'rt_rw' => '002/004',
                'kelurahan_desa' => 'Pasirkaliki',
                'kecamatan' => 'Cicendo',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Belum Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-06-01', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                ],
            ],
            [
                'nik' => 'EMP-1007',
                'nama' => 'Rizky Pratama',
                'email' => 'rizky@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Admin Gudang',
                'divisi' => 'Logistik',
                'status_kerja' => 'aktif',
                'tanggal_aktif' => '2026-10-20',
                'tanggal_berhenti' => null,
                'nik_ktp' => '3273071408940007',
                'tempat_lahir' => 'Garut',
                'tanggal_lahir' => '1994-08-14',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_darah' => 'B',
                'alamat' => 'Jl. Kopo Bihbul No. 17',
                'rt_rw' => '006/009',
                'kelurahan_desa' => 'Sayati',
                'kecamatan' => 'Margahayu',
                'kabupaten_kota' => 'Kabupaten Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-07-01', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                    ['status' => 'nonaktif', 'tanggal_efektif' => '2026-08-31', 'keterangan' => 'Kontrak tahap pertama berakhir.'],
                    ['status' => 'aktif', 'tanggal_efektif' => '2026-10-20', 'keterangan' => 'Karyawan diaktifkan kembali setelah perpanjangan kontrak.'],
                ],
            ],
            [
                'nik' => 'EMP-1008',
                'nama' => 'Ahmad Fauzi',
                'email' => 'ahmad@araya.com',
                'role' => 'Employee',
                'jabatan' => 'Spesialis Logistik',
                'divisi' => 'Logistik',
                'status_kerja' => 'nonaktif',
                'tanggal_aktif' => '2024-01-15',
                'tanggal_berhenti' => '2026-10-15',
                'nik_ktp' => '3273080312930008',
                'tempat_lahir' => 'Tasikmalaya',
                'tanggal_lahir' => '1993-12-03',
                'jenis_kelamin' => 'Laki-laki',
                'golongan_darah' => 'O',
                'alamat' => 'Jl. Antapani Lama No. 32',
                'rt_rw' => '001/006',
                'kelurahan_desa' => 'Antapani Wetan',
                'kecamatan' => 'Antapani',
                'kabupaten_kota' => 'Kota Bandung',
                'provinsi' => 'Jawa Barat',
                'agama' => 'Islam',
                'status_perkawinan' => 'Kawin',
                'pekerjaan_ktp' => 'Karyawan Swasta',
                'kewarganegaraan' => 'WNI',
                'histories' => [
                    ['status' => 'aktif', 'tanggal_efektif' => '2024-01-15', 'keterangan' => 'Karyawan terdaftar dan mulai aktif bekerja.'],
                    ['status' => 'nonaktif', 'tanggal_efektif' => '2026-10-15', 'keterangan' => 'Mengundurkan diri secara resmi.'],
                ],
            ],
        ];

        $hasEmailColumnOnEmployees = Schema::hasColumn('employees', 'email');
        $hasStatusColumnOnEmployees = Schema::hasColumn('employees', 'status');
        $hasAddressColumnOnEmployees = Schema::hasColumn('employees', 'address');

        foreach ($records as $row) {
            $histories = $row['histories'];
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
                'tanggal_aktif' => $row['tanggal_aktif'],
                'tanggal_berhenti' => $row['tanggal_berhenti'],
                'nik_ktp' => $row['nik_ktp'],
                'tempat_lahir' => $row['tempat_lahir'],
                'tanggal_lahir' => $row['tanggal_lahir'],
                'jenis_kelamin' => $row['jenis_kelamin'],
                'golongan_darah' => $row['golongan_darah'],
                'alamat' => $row['alamat'],
                'rt_rw' => $row['rt_rw'],
                'kelurahan_desa' => $row['kelurahan_desa'],
                'kecamatan' => $row['kecamatan'],
                'kabupaten_kota' => $row['kabupaten_kota'],
                'provinsi' => $row['provinsi'],
                'agama' => $row['agama'],
                'status_perkawinan' => $row['status_perkawinan'],
                'pekerjaan_ktp' => $row['pekerjaan_ktp'],
                'kewarganegaraan' => $row['kewarganegaraan'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($hasEmailColumnOnEmployees) {
                $empPayload['email'] = $row['email'];
            }
            if ($hasStatusColumnOnEmployees) {
                $empPayload['status'] = $row['status_kerja'];
            }
            if ($hasAddressColumnOnEmployees) {
                $empPayload['address'] = $row['alamat'];
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

            foreach ($histories as $hist) {
                DB::table('employee_status_histories')->insert([
                    'employee_id' => $empId,
                    'status' => $hist['status'],
                    'tanggal_efektif' => $hist['tanggal_efektif'],
                    'keterangan' => $hist['keterangan'],
                    'changed_by' => 'Rani Kusumawati',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
