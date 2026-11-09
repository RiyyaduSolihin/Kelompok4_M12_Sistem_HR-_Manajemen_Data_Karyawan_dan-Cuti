<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'employee_status_histories';

    protected $fillable = [
        'employee_id',
        'status',
        'tanggal_efektif',
        'keterangan',
        'changed_by',
    ];

    /**
     * Relasi ke Karyawan (Employee)
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
