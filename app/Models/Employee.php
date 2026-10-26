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

    /**
     * Relasi ke akun User (1 karyawan dapat memiliki 1 akun login)
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }
}
