<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations (M12-US6-T2: Tabel divisions & positions).
     */
    public function up(): void
    {
        // 1. Tabel Master Divisi (divisions)
        if (! Schema::hasTable('divisions')) {
            Schema::create('divisions', function (Blueprint $table) {
                $table->id();
                $table->string('kode_divisi', 20)->nullable()->unique();
                $table->string('nama_divisi', 100)->unique();
                $table->string('name', 100)->nullable();
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel Master Jabatan (positions)
        if (! Schema::hasTable('positions')) {
            Schema::create('positions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('division_id')
                    ->constrained('divisions')
                    ->restrictOnDelete();
                $table->string('nama_jabatan', 100)->unique();
                $table->string('name', 100)->nullable();
                $table->string('level_jabatan', 50)->default('Staff'); // Contoh: Manager, Supervisor, Staff
                $table->foreignId('parent_position_id')
                    ->nullable()
                    ->constrained('positions')
                    ->nullOnDelete(); // Relasi ke jabatan atasan langsung
                $table->string('atasan', 100)->nullable(); // Nama jabatan atasan
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        // 3. Tambahkan foreign key opsional division_id & position_id pada tabel employees
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (! Schema::hasColumn('employees', 'division_id')) {
                    $table->foreignId('division_id')
                        ->nullable()
                        ->constrained('divisions')
                        ->restrictOnDelete();
                }
                if (! Schema::hasColumn('employees', 'position_id')) {
                    $table->foreignId('position_id')
                        ->nullable()
                        ->constrained('positions')
                        ->restrictOnDelete();
                }
            });
        }

        // 4. Seed data awal Master Divisi & Jabatan jika masih kosong
        if (DB::table('divisions')->count() === 0) {
            $divisions = [
                ['kode_divisi' => 'DIV-HRD', 'nama_divisi' => 'HRD', 'name' => 'HRD', 'deskripsi' => 'Human Resource Development'],
                ['kode_divisi' => 'DIV-SDM', 'nama_divisi' => 'SDM', 'name' => 'SDM', 'deskripsi' => 'Sumber Daya Manusia'],
                ['kode_divisi' => 'DIV-TI', 'nama_divisi' => 'Teknologi Informasi', 'name' => 'Teknologi Informasi', 'deskripsi' => 'Divisi IT & Pengembangan Sistem'],
                ['kode_divisi' => 'DIV-KEU', 'nama_divisi' => 'Keuangan', 'name' => 'Keuangan', 'deskripsi' => 'Divisi Keuangan & Akuntansi'],
                ['kode_divisi' => 'DIV-OPS', 'nama_divisi' => 'Operasional', 'name' => 'Operasional', 'deskripsi' => 'Divisi Operasional Perusahaan'],
                ['kode_divisi' => 'DIV-LOG', 'nama_divisi' => 'Logistik', 'name' => 'Logistik', 'deskripsi' => 'Divisi Logistik & Gudang'],
            ];

            $divMap = [];
            foreach ($divisions as $div) {
                $divMap[$div['nama_divisi']] = DB::table('divisions')->insertGetId(array_merge($div, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // Jabatan Level Manager (Atasan Utama)
            $hrManagerId = DB::table('positions')->insertGetId([
                'division_id' => $divMap['HRD'],
                'nama_jabatan' => 'HR Manager',
                'name' => 'HR Manager',
                'level_jabatan' => 'Manager',
                'parent_position_id' => null,
                'atasan' => 'Direktur Utama',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $subPositions = [
                [
                    'division_id' => $divMap['SDM'],
                    'nama_jabatan' => 'Spesialis SDM',
                    'name' => 'Spesialis SDM',
                    'level_jabatan' => 'Supervisor',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'HR Manager',
                ],
                [
                    'division_id' => $divMap['SDM'],
                    'nama_jabatan' => 'Spesialis Rekrutmen',
                    'name' => 'Spesialis Rekrutmen',
                    'level_jabatan' => 'Staff',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'HR Manager',
                ],
                [
                    'division_id' => $divMap['Teknologi Informasi'],
                    'nama_jabatan' => 'Software Engineer',
                    'name' => 'Software Engineer',
                    'level_jabatan' => 'Staff',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'IT Manager',
                ],
                [
                    'division_id' => $divMap['Teknologi Informasi'],
                    'nama_jabatan' => 'Analis Data',
                    'name' => 'Analis Data',
                    'level_jabatan' => 'Staff',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'IT Manager',
                ],
                [
                    'division_id' => $divMap['Keuangan'],
                    'nama_jabatan' => 'Staff Keuangan',
                    'name' => 'Staff Keuangan',
                    'level_jabatan' => 'Staff',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'Finance Manager',
                ],
                [
                    'division_id' => $divMap['Operasional'],
                    'nama_jabatan' => 'Staff Operasional',
                    'name' => 'Staff Operasional',
                    'level_jabatan' => 'Staff',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'Operational Manager',
                ],
                [
                    'division_id' => $divMap['Logistik'],
                    'nama_jabatan' => 'Admin Gudang',
                    'name' => 'Admin Gudang',
                    'level_jabatan' => 'Staff',
                    'parent_position_id' => $hrManagerId,
                    'atasan' => 'Logistics Supervisor',
                ],
            ];

            foreach ($subPositions as $pos) {
                DB::table('positions')->insert(array_merge($pos, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (Schema::hasColumn('employees', 'position_id')) {
                    $table->dropConstrainedForeignId('position_id');
                }
                if (Schema::hasColumn('employees', 'division_id')) {
                    $table->dropConstrainedForeignId('division_id');
                }
            });
        }

        Schema::dropIfExists('positions');
        Schema::dropIfExists('divisions');
    }
};

