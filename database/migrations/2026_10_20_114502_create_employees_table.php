<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 30)->unique(); // Validasi NIK tidak ganda di level database
            $table->string('nama', 100);
            $table->string('jabatan', 50);
            $table->string('divisi', 50);
            $table->enum('status_kerja', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('tanggal_berhenti')->nullable(); // Disiapkan untuk M12-US2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
