<?php

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class)->in('Feature');

/**
 * Data karyawan minimal yang valid untuk pengujian ubah data.
 *
 * @return array<string, mixed>
 */
function validKaryawanPayload(array $overrides = []): array
{
    return array_merge([
        'nik' => 'EMP-5001',
        'nama' => 'Rani Kusumawati',
        'jabatan' => 'Staff Operasional',
        'divisi' => 'Operasional',
        'status_kerja' => 'aktif',
    ], $overrides);
}

/**
 * Data KTP minimal yang valid untuk pengujian ubah data KTP.
 *
 * @return array<string, mixed>
 */
function validKtpPayload(array $overrides = []): array
{
    return array_merge([
        'nik_ktp' => '3273015503920001',
        'tempat_lahir' => 'Bandung',
        'tanggal_lahir' => '1992-03-15',
        'jenis_kelamin' => 'Laki-laki',
        'alamat' => 'Jl. Dr. Setiabudhi No. 193',
        'rt_rw' => '002/005',
        'kelurahan_desa' => 'Gegerkalong',
        'kecamatan' => 'Sukasari',
        'kabupaten_kota' => 'Kota Bandung',
        'provinsi' => 'Jawa Barat',
    ], $overrides);
}

it('menolak NIK dengan karakter yang tidak diperbolehkan saat ubah data karyawan', function () {
    $employee = Employee::factory()->create(['nik' => 'EMP-1001']);

    $response = $this->putJson("/karyawan/{$employee->id}", validKaryawanPayload([
        'nik' => 'EMP 1001!',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('nik');

    expect($response->json('errors.nik.0'))
        ->toBe('NIK tidak boleh mengandung spasi atau karakter aneh (hanya huruf, angka, dan tanda hubung "-").');
});

it('menolak nama dengan karakter yang tidak diperbolehkan saat ubah data karyawan', function () {
    $employee = Employee::factory()->create(['nik' => 'EMP-1001']);

    $response = $this->putJson("/karyawan/{$employee->id}", validKaryawanPayload([
        'nama' => 'Rani123',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('nama');

    expect($response->json('errors.nama.0'))
        ->toBe('Nama karyawan tidak boleh mengandung angka atau karakter aneh (hanya huruf, spasi, titik, koma, dan tanda petik).');
});

it('menolak NIK KTP yang bukan 16 digit angka saat ubah data KTP', function () {
    $employee = Employee::factory()->create(['nik_ktp' => '3273015503920001']);

    $response = $this->putJson("/karyawan/{$employee->id}/ktp", validKtpPayload([
        'nik_ktp' => '32730155039200',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('nik_ktp');

    expect($response->json('errors.nik_ktp.0'))
        ->toBe('NIK KTP harus terdiri dari 16 digit angka.');
});

it('menolak karakter yang tidak diperbolehkan pada kolom alamat saat ubah data KTP', function () {
    $employee = Employee::factory()->create(['nik_ktp' => '3273015503920001']);

    $response = $this->putJson("/karyawan/{$employee->id}/ktp", validKtpPayload([
        'alamat' => 'Jl <script>alert(1)</script>',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('alamat');

    expect($response->json('errors.alamat.0'))
        ->toBe('Alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca yang lazim (titik, koma, tanda hubung, garis miring).');
});

it('menolak NIK yang sudah digunakan karyawan lain saat ubah data karyawan', function () {
    Employee::factory()->create(['nik' => 'EMP-2000']);
    $employee = Employee::factory()->create(['nik' => 'EMP-1001']);

    $response = $this->putJson("/karyawan/{$employee->id}", validKaryawanPayload([
        'nik' => 'EMP-2000',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors('nik');

    expect($response->json('errors.nik.0'))
        ->toBe('Sistem menolak duplikasi: NIK sudah digunakan karyawan lain.');
});

it('menyimpan data karyawan dengan benar saat isinya valid', function () {
    $employee = Employee::factory()->create(['nik' => 'EMP-1001', 'nama' => 'Nama Lama']);

    $response = $this->putJson("/karyawan/{$employee->id}", validKaryawanPayload([
        'nama' => 'Rani Kusumawati',
    ]));

    $response->assertStatus(200);

    expect($employee->fresh()->nama)->toBe('Rani Kusumawati');
});

it('menyimpan data KTP dengan benar saat isinya valid', function () {
    $employee = Employee::factory()->create(['nik_ktp' => '3273015503920001']);

    $response = $this->putJson("/karyawan/{$employee->id}/ktp", validKtpPayload([
        'alamat' => 'Jl. Dr. Setiabudhi No. 200',
    ]));

    $response->assertStatus(200);

    expect($employee->fresh()->alamat)->toBe('Jl. Dr. Setiabudhi No. 200');
});
