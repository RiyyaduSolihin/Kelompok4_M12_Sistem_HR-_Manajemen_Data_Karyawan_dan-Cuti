<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $table = 'divisions';

    protected $fillable = [
        'kode_divisi',
        'nama_divisi',
        'name',
        'deskripsi',
    ];

    /**
     * Relasi 1 Divisi memiliki banyak Jabatan (positions)
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'division_id');
    }

    /**
     * Relasi 1 Divisi memiliki banyak Karyawan (employees)
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'division_id');
    }

    /**
     * Mengecek apakah divisi ini sedang dipakai oleh karyawan atau jabatan
     * (Sesuai AC-2 M12-US6: Proteksi hapus jika masih dipakai karyawan)
     */
    public function isUsed(): bool
    {
        return $this->positions()->exists()
            || $this->employees()->exists()
            || Employee::where('divisi', $this->nama_divisi)
                ->orWhere('department', $this->nama_divisi)
                ->exists();
    }
}

