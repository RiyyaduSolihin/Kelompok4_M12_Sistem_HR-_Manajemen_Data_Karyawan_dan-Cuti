<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
