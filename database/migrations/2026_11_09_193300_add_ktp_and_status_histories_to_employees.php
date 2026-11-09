<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations:
     * 1. Menambahkan kolom tanggal_aktif & data diri sesuai KTP pada tabel employees
     * 2. Membuat tabel employee_status_histories untuk mencatat riwayat setiap kali
     *    karyawan diaktifkan / dinonaktifkan / diaktifkan kembali.
     */
    public function up(): void
    {
        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (! Schema::hasColumn('employees', 'tanggal_aktif')) {
                    $table->date('tanggal_aktif')->nullable()->after('status_kerja');
                }

                // Data Diri Sesuai KTP
                if (! Schema::hasColumn('employees', 'nik_ktp')) {
                    $table->string('nik_ktp', 20)->nullable()->unique();
                }
                if (! Schema::hasColumn('employees', 'tempat_lahir')) {
                    $table->string('tempat_lahir', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'tanggal_lahir')) {
                    $table->date('tanggal_lahir')->nullable();
                }
                if (! Schema::hasColumn('employees', 'jenis_kelamin')) {
                    $table->string('jenis_kelamin', 20)->nullable();
                }
                if (! Schema::hasColumn('employees', 'golongan_darah')) {
                    $table->string('golongan_darah', 5)->nullable();
                }
                if (! Schema::hasColumn('employees', 'alamat')) {
                    $table->text('alamat')->nullable();
                }
                if (! Schema::hasColumn('employees', 'rt_rw')) {
                    $table->string('rt_rw', 20)->nullable();
                }
                if (! Schema::hasColumn('employees', 'kelurahan_desa')) {
                    $table->string('kelurahan_desa', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'kecamatan')) {
                    $table->string('kecamatan', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'kabupaten_kota')) {
                    $table->string('kabupaten_kota', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'provinsi')) {
                    $table->string('provinsi', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'agama')) {
                    $table->string('agama', 30)->nullable();
                }
                if (! Schema::hasColumn('employees', 'status_perkawinan')) {
                    $table->string('status_perkawinan', 30)->nullable();
                }
                if (! Schema::hasColumn('employees', 'pekerjaan_ktp')) {
                    $table->string('pekerjaan_ktp', 100)->nullable();
                }
                if (! Schema::hasColumn('employees', 'kewarganegaraan')) {
                    $table->string('kewarganegaraan', 10)->nullable()->default('WNI');
                }
            });
        }

        if (! Schema::hasTable('employee_status_histories')) {
            Schema::create('employee_status_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')
                    ->constrained('employees')
                    ->cascadeOnDelete();
                $table->string('status', 20); // 'aktif' atau 'nonaktif'
                $table->date('tanggal_efektif');
                $table->string('keterangan', 255)->nullable();
                $table->string('changed_by', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_status_histories');

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $cols = [
                    'tanggal_aktif',
                    'nik_ktp',
                    'tempat_lahir',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'golongan_darah',
                    'alamat',
                    'rt_rw',
                    'kelurahan_desa',
                    'kecamatan',
                    'kabupaten_kota',
                    'provinsi',
                    'agama',
                    'status_perkawinan',
                    'pekerjaan_ktp',
                    'kewarganegaraan',
                ];

                $existing = array_filter($cols, fn ($c) => Schema::hasColumn('employees', $c));
                if (! empty($existing)) {
                    $table->dropColumn($existing);
                }
            });
        }
    }
};
