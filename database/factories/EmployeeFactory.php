<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => 'EMP-'.fake()->unique()->numerify('####'),
            'nama' => fake()->name(),
            'name' => fake()->name(),
            'jabatan' => 'Staff Operasional',
            'position' => 'Staff Operasional',
            'divisi' => 'Operasional',
            'department' => 'Operasional',
            'status_kerja' => 'aktif',
            'tanggal_aktif' => now()->toDateString(),
            'nik_ktp' => fake()->unique()->numerify('32730155039200##'),
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '1992-03-15',
            'jenis_kelamin' => 'Laki-laki',
            'golongan_darah' => 'O',
            'alamat' => 'Jl. Dr. Setiabudhi No. 193',
            'address' => 'Jl. Dr. Setiabudhi No. 193',
            'rt_rw' => '002/005',
            'kelurahan_desa' => 'Gegerkalong',
            'kecamatan' => 'Sukasari',
            'kabupaten_kota' => 'Kota Bandung',
            'provinsi' => 'Jawa Barat',
            'agama' => 'Islam',
            'status_perkawinan' => 'Belum Kawin',
            'pekerjaan_ktp' => 'Karyawan Swasta',
            'kewarganegaraan' => 'WNI',
        ];
    }
}
