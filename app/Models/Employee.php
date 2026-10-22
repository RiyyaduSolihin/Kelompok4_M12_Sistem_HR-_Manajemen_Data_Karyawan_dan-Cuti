<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama',
        'jabatan',
        'divisi',
        'status_kerja',
        'tanggal_berhenti',
    ];
}
