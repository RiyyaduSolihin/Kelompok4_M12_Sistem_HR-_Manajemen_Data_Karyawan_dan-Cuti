<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'nik',
        'nama',
        'name',
        'jabatan',
        'position',
        'divisi',
        'department',
        'status_kerja',
        'tanggal_berhenti',
    ];

    protected $appends = [
        'nama',
        'jabatan',
        'divisi',
        'status_kerja',
    ];

    public function getNamaAttribute($value): ?string
    {
        return $value ?? ($this->attributes['name'] ?? null);
    }

    public function getNameAttribute($value): ?string
    {
        return $value ?? ($this->attributes['nama'] ?? null);
    }

    public function getJabatanAttribute($value): ?string
    {
        return $value ?? ($this->attributes['position'] ?? null);
    }

    public function getPositionAttribute($value): ?string
    {
        return $value ?? ($this->attributes['jabatan'] ?? null);
    }

    public function getDivisiAttribute($value): ?string
    {
        return $value ?? ($this->attributes['department'] ?? null);
    }

    public function getDepartmentAttribute($value): ?string
    {
        return $value ?? ($this->attributes['divisi'] ?? null);
    }

    public function getStatusKerjaAttribute($value): string
    {
        return $value ?? 'aktif';
    }

    /**
     * Relasi ke akun User (1 karyawan dapat memiliki 1 akun login)
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }
}
