<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'division_id',
        'position_id',
        'status_kerja',
        'tanggal_aktif',
        'tanggal_berhenti',
        // Data Diri Sesuai KTP
        'nik_ktp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'alamat',
        'address',
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

    public function getAlamatAttribute($value): ?string
    {
        return $value ?? ($this->attributes['address'] ?? null);
    }

    /**
     * Relasi ke akun User (1 karyawan dapat memiliki 1 akun login)
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id');
    }

    /**
     * Relasi ke Master Divisi (M12-US6)
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    /**
     * Relasi ke Master Jabatan (M12-US6)
     */
    public function positionRelation(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * Relasi ke Riwayat Status Aktif / Nonaktif Karyawan
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(EmployeeStatusHistory::class, 'employee_id')->orderBy('id', 'asc');
    }

    /**
     * Mencatat riwayat perubahan status kepegawaian (aktif / nonaktif / re-aktivasi)
     */
    public function recordStatusHistory(
        string $status,
        ?string $tanggalEfektif = null,
        ?string $keterangan = null,
        ?string $changedBy = 'Admin HR'
    ): EmployeeStatusHistory {
        return $this->statusHistories()->create([
            'status' => $status,
            'tanggal_efektif' => $tanggalEfektif ?: now()->toDateString(),
            'keterangan' => $keterangan,
            'changed_by' => $changedBy,
        ]);
    }
}
