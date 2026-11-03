<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';

    protected $fillable = [
        'division_id',
        'nama_jabatan',
        'name',
        'level_jabatan',
        'parent_position_id',
        'atasan',
        'deskripsi',
    ];

    /**
     * Relasi Jabatan milik 1 Divisi
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Relasi ke Jabatan Atasan Langsung (Parent Position)
     */
    public function parentPosition(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'parent_position_id');
    }

    /**
     * Relasi ke Jabatan Bawahan (Subordinate Positions)
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Position::class, 'parent_position_id');
    }

    /**
     * Relasi 1 Jabatan dimiliki banyak Karyawan
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    /**
     * Mengecek apakah jabatan ini sedang dipakai oleh karyawan
     * (Sesuai AC-2 M12-US6: Proteksi hapus jika masih dipakai karyawan)
     */
    public function isUsedByEmployees(): bool
    {
        return $this->employees()->exists()
            || Employee::where('jabatan', $this->nama_jabatan)
                ->orWhere('position', $this->nama_jabatan)
                ->exists();
    }
}

