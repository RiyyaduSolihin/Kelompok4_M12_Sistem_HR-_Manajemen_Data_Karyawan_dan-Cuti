<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Karyawan;
use App\Models\Position;
use App\Support\DatabaseSchemaGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class KaryawanController extends Controller
{
    /**
     * Menampilkan daftar seluruh karyawan di antarmuka web
     */
    public function index()
    {
        $employees = DatabaseSchemaGuard::run(function () {
            $list = Employee::with('statusHistories')->orderBy('id', 'asc')->paginate(11);

            if ($list->isEmpty() || ! array_key_exists('nama', $list->first()->getAttributes())) {
                DatabaseSchemaGuard::ensureSchema(true);
                $list = Employee::with('statusHistories')->orderBy('id', 'asc')->paginate(11);
            }

            return $list;
        });

        // Ambil data Master Jabatan & Divisi untuk dropdown
        $positions = DatabaseSchemaGuard::run(function () {
            $defaultPositions = [
                'HR Manager',
                'Spesialis SDM',
                'Spesialis Rekrutmen',
                'Software Engineer',
                'Analis Data',
                'Staff Keuangan',
                'Staff Operasional',
                'Admin Gudang',
                'Spesialis Logistik',
            ];

            if (Schema::hasTable('positions')) {
                $dbPositions = Position::orderBy('nama_jabatan', 'asc')->pluck('nama_jabatan')->toArray();
                if (! empty($dbPositions)) {
                    return array_values(array_unique(array_merge($dbPositions, $defaultPositions)));
                }
            }

            return $defaultPositions;
        });

        $divisions = DatabaseSchemaGuard::run(function () {
            $defaultDivisions = ['Logistik', 'Keuangan', 'SDM', 'Teknologi Informasi', 'Operasional'];

            if (Schema::hasTable('divisions')) {
                $dbDivisions = Division::orderBy('nama_divisi', 'asc')->pluck('nama_divisi')->toArray();
                if (! empty($dbDivisions)) {
                    return array_values(array_unique(array_merge($dbDivisions, $defaultDivisions)));
                }
            }

            return $defaultDivisions;
        });

        return view('employees.index', compact('employees', 'positions', 'divisions'));
    }

    /**
     * Menampilkan form KTP untuk karyawan yang baru dibuat (via session)
     */
    public function showKtpForm(string $id)
    {
        $employee = Employee::findOrFail($id);

        return view('employees.index', compact('employee'));
    }

    /**
     * Aturan validasi untuk kolom data karyawan (tab "Data Karyawan").
     *
     * @param  int|null  $ignoreId  ID karyawan yang sedang diubah, agar NIK miliknya sendiri tidak dianggap duplikat.
     */
    private function karyawanRules(?int $ignoreId = null): array
    {
        return [
            'nik' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/', $this->uniqueRule('nik', $ignoreId)],
            'nama' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\']+$/'],
            'jabatan' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\.\&\'\(\)\/\-]+$/'],
            'divisi' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\s\.\&\'\(\)\/\-]+$/'],
            'status_kerja' => 'required|in:aktif,nonaktif',
            'tanggal_aktif' => 'nullable|date',
        ];
    }

    /**
     * Aturan validasi untuk kolom data diri sesuai KTP (tab "Data KTP").
     *
     * @param  int|null  $ignoreId  ID karyawan yang sedang diubah, agar NIK KTP miliknya sendiri tidak dianggap duplikat.
     */
    private function ktpRules(?int $ignoreId = null): array
    {
        return [
            'nik_ktp' => ['nullable', 'string', 'regex:/^[0-9]{16}$/', $this->uniqueRule('nik_ktp', $ignoreId)],
            'tempat_lahir' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z\s\.,\'\-]+$/'],
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'golongan_darah' => 'nullable|in:A,B,AB,O,-',
            'alamat' => ['nullable', 'string', 'max:500', 'regex:/^[A-Za-z0-9\s\.,\'\-\/#]+$/'],
            'rt_rw' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\/\-]+$/'],
            'kelurahan_desa' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s\.,\'\-]+$/'],
            'kecamatan' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s\.,\'\-]+$/'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s\.,\'\-]+$/'],
            'provinsi' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s\.,\'\-]+$/'],
            'agama' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z\s]+$/'],
            'status_perkawinan' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z\s]+$/'],
            'pekerjaan_ktp' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9\s\.,\'\-]+$/'],
            'kewarganegaraan' => 'nullable|in:WNI,WNA',
        ];
    }

    /**
     * Aturan unique yang mengabaikan satu baris (karyawan yang sedang diubah).
     */
    private function uniqueRule(string $column, ?int $ignoreId = null): string
    {
        return $ignoreId
            ? 'unique:employees,'.$column.','.$ignoreId
            : 'unique:employees,'.$column;
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk seluruh aturan di atas.
     */
    private function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute maksimal :max karakter.',
            'date' => 'Format :attribute tidak valid.',
            'in' => ':attribute yang dipilih tidak valid.',
            'nik.regex' => 'NIK tidak boleh mengandung spasi atau karakter aneh (hanya huruf, angka, dan tanda hubung "-").',
            'nik.unique' => 'Sistem menolak duplikasi: NIK sudah digunakan karyawan lain.',
            'nama.regex' => 'Nama karyawan tidak boleh mengandung angka atau karakter aneh (hanya huruf, spasi, titik, koma, dan tanda petik).',
            'jabatan.regex' => 'Jabatan tidak boleh mengandung karakter aneh (hanya huruf, angka, spasi, titik, tanda hubung, dan tanda kurung).',
            'divisi.regex' => 'Divisi tidak boleh mengandung karakter aneh (hanya huruf, angka, spasi, titik, tanda hubung, dan tanda kurung).',
            'nik_ktp.regex' => 'NIK KTP harus terdiri dari 16 digit angka.',
            'nik_ktp.unique' => 'NIK KTP sudah terdaftar pada karyawan lain.',
            'tempat_lahir.regex' => 'Tempat lahir hanya boleh berisi huruf, spasi, titik, koma, tanda hubung, dan tanda petik.',
            'alamat.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca yang lazim (titik, koma, tanda hubung, garis miring).',
            'rt_rw.regex' => 'RT / RW hanya boleh berisi angka, garis miring, dan tanda hubung (contoh: 002/005).',
            'kelurahan_desa.regex' => 'Kelurahan / Desa hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            'kecamatan.regex' => 'Kecamatan hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            'kabupaten_kota.regex' => 'Kabupaten / Kota hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            'provinsi.regex' => 'Provinsi hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            'agama.regex' => 'Agama hanya boleh berisi huruf dan spasi.',
            'status_perkawinan.regex' => 'Status perkawinan hanya boleh berisi huruf dan spasi.',
            'pekerjaan_ktp.regex' => 'Pekerjaan (di KTP) hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
        ];
    }

    /**
     * Nama kolom dalam bahasa Indonesia agar pesan error otomatis terbaca jelas.
     */
    private function attributeNames(): array
    {
        return [
            'nik' => 'NIK',
            'nama' => 'nama karyawan',
            'jabatan' => 'jabatan',
            'divisi' => 'divisi',
            'status_kerja' => 'status kerja',
            'tanggal_aktif' => 'tanggal aktif',
            'nik_ktp' => 'NIK KTP',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'golongan_darah' => 'golongan darah',
            'alamat' => 'alamat',
            'rt_rw' => 'RT / RW',
            'kelurahan_desa' => 'kelurahan / desa',
            'kecamatan' => 'kecamatan',
            'kabupaten_kota' => 'kabupaten / kota',
            'provinsi' => 'provinsi',
            'agama' => 'agama',
            'status_perkawinan' => 'status perkawinan',
            'pekerjaan_ktp' => 'pekerjaan (di KTP)',
            'kewarganegaraan' => 'kewarganegaraan',
        ];
    }

    /**
     * Menyimpan data karyawan baru dari form web
     */
    public function store(Request $request)
    {
        $validated = $request->validate(
            array_merge($this->karyawanRules(), $this->ktpRules()),
            $this->validationMessages(),
            $this->attributeNames()
        );

        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];
        if (! empty($validated['alamat'])) {
            $validated['address'] = $validated['alamat'];
        } else {
            unset($validated['address']);
        }
        $validated['tanggal_aktif'] = $validated['tanggal_aktif'] ?? now()->toDateString();

        if (Schema::hasTable('positions')) {
            $posObj = Position::where('nama_jabatan', $validated['jabatan'])->first();
            if ($posObj) {
                $validated['position_id'] = $posObj->id;
                if (! isset($validated['division_id']) && $posObj->division_id) {
                    $validated['division_id'] = $posObj->division_id;
                }
            }
        }
        if (Schema::hasTable('divisions')) {
            $divObj = Division::where('nama_divisi', $validated['divisi'])->first();
            if ($divObj) {
                $validated['division_id'] = $divObj->id;
            }
        }

        $newEmployee = null;

        DatabaseSchemaGuard::run(function () use ($validated, &$newEmployee) {
            $emp = Employee::create($validated);
            $emp->recordStatusHistory(
                $emp->status_kerja,
                $emp->tanggal_aktif,
                'Karyawan terdaftar dan mulai aktif bekerja.',
                'Admin HR'
            );
            $newEmployee = $emp;
        });

        // Redirect ke halaman utama dengan trigger untuk buka modal KTP
        return redirect('/')
            ->with('open_ktp_modal', $newEmployee->id)
            ->with('ktp_nama', $newEmployee->nama)
            ->with('ktp_employee', [
                'id' => $newEmployee->id,
                'nik' => $newEmployee->nik,
                'nama' => $newEmployee->nama,
                'jabatan' => $newEmployee->jabatan,
                'divisi' => $newEmployee->divisi,
                'status_kerja' => $newEmployee->status_kerja,
            ]);
    }

    /**
     * Memperbarui data karyawan dari form web
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);
        $oldStatus = $employee->status_kerja;

        $validated = $request->validate(
            array_merge($this->karyawanRules($employee->id), $this->ktpRules($employee->id)),
            $this->validationMessages(),
            $this->attributeNames()
        );

        $validated['name'] = $validated['nama'];
        $validated['position'] = $validated['jabatan'];
        $validated['department'] = $validated['divisi'];
        if (! empty($validated['alamat'])) {
            $validated['address'] = $validated['alamat'];
        } else {
            unset($validated['address']);
        }

        // Jangan kosongkan tanggal aktif / berhenti bila tidak dikirim form,
        // agar data riwayat kepegawaian tetap utuh saat data karyawan diperbarui.
        foreach (['tanggal_aktif', 'tanggal_berhenti'] as $optionalDate) {
            if (empty($validated[$optionalDate])) {
                unset($validated[$optionalDate]);
            }
        }

        if (Schema::hasTable('positions')) {
            $posObj = Position::where('nama_jabatan', $validated['jabatan'])->first();
            if ($posObj) {
                $validated['position_id'] = $posObj->id;
                if ($posObj->division_id) {
                    $validated['division_id'] = $posObj->division_id;
                }
            }
        }
        if (Schema::hasTable('divisions')) {
            $divObj = Division::where('nama_divisi', $validated['divisi'])->first();
            if ($divObj) {
                $validated['division_id'] = $divObj->id;
            }
        }

        DatabaseSchemaGuard::run(function () use ($employee, $validated, $oldStatus, $request) {
            if ($oldStatus === 'nonaktif' && $validated['status_kerja'] === 'aktif') {
                $validated['tanggal_aktif'] = $request->input('tanggal_aktif') ?: now()->toDateString();
                $validated['tanggal_berhenti'] = null;
                $employee->update($validated);
                $employee->recordStatusHistory(
                    'aktif',
                    $validated['tanggal_aktif'],
                    'Karyawan diaktifkan kembali setelah sebelumnya nonaktif.',
                    'Admin HR'
                );
            } elseif ($oldStatus === 'aktif' && $validated['status_kerja'] === 'nonaktif') {
                $validated['tanggal_berhenti'] = $request->input('tanggal_berhenti') ?: now()->toDateString();
                $employee->update($validated);
                $employee->recordStatusHistory(
                    'nonaktif',
                    $validated['tanggal_berhenti'],
                    'Karyawan dinonaktifkan.',
                    'Admin HR'
                );
            } else {
                $employee->update($validated);
            }
        });

        // Setelah data karyawan disimpan, pengguna diarahkan ke form data KTP
        // supaya data KTP wajib dilengkapi terlebih dahulu.
        if ($request->boolean('require_ktp')) {
            return redirect('/')
                ->with('open_ktp_modal', $employee->id)
                ->with('ktp_nama', $employee->nama)
                ->with('ktp_employee', [
                    'id' => $employee->id,
                    'nik' => $employee->nik,
                    'nama' => $employee->nama,
                    'jabatan' => $employee->jabatan,
                    'divisi' => $employee->divisi,
                    'status_kerja' => $employee->status_kerja,
                ]);
        }

        return redirect('/')->with('success', "Data karyawan {$employee->nama} berhasil diperbarui.");
    }

    /**
     * Menyimpan / memperbarui data KTP karyawan (M12-US1: Data Diri KTP)
     */
    public function updateKtp(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate(
            $this->ktpRules($employee->id),
            $this->validationMessages(),
            $this->attributeNames()
        );

        // Alamat pada tabel employees diselaraskan dengan alamat sesuai KTP,
        // termasuk ketika kolom dikosongkan lewat form ubah data KTP.
        if (array_key_exists('alamat', $validated)) {
            $validated['address'] = $validated['alamat'];
        }

        DatabaseSchemaGuard::run(function () use ($employee, $validated) {
            $employee->update($validated);
        });

        return redirect('/')->with('success', "Data KTP karyawan {$employee->nama} berhasil disimpan.");
    }

    /**
     * Mengubah status karyawan menjadi nonaktif dan mencatat tanggal berhenti di riwayat
     */
    public function deactivate(Request $request, string $id)
    {
        $request->validate([
            'tanggal_berhenti' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal_berhenti.required' => 'Tanggal berhenti wajib diisi.',
            'tanggal_berhenti.date' => 'Format tanggal berhenti tidak valid.',
        ]);

        $karyawan = DatabaseSchemaGuard::run(function () use ($id, $request) {
            $emp = Employee::findOrFail($id);
            $emp->status_kerja = 'nonaktif';
            $emp->tanggal_berhenti = $request->tanggal_berhenti;
            $emp->save();

            $emp->recordStatusHistory(
                'nonaktif',
                $request->tanggal_berhenti,
                $request->input('keterangan', 'Karyawan dinonaktifkan.'),
                'Admin HR'
            );

            return $emp->load('statusHistories');
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil dinonaktifkan dan riwayat kepegawaian tetap tersimpan.',
                'data' => $karyawan,
            ], 200);
        }

        return redirect('/')->with('success', "Karyawan {$karyawan->nama} berhasil dinonaktifkan.");
    }

    /**
     * Mengaktifkan kembali karyawan yang nonaktif dan mencatat tanggal aktif baru di riwayat
     */
    public function activate(Request $request, string $id)
    {
        $request->validate([
            'tanggal_aktif' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tanggal_aktif.date' => 'Format tanggal aktif tidak valid.',
        ]);

        $tanggalAktif = $request->input('tanggal_aktif') ?: now()->toDateString();

        $karyawan = DatabaseSchemaGuard::run(function () use ($id, $tanggalAktif, $request) {
            $emp = Employee::findOrFail($id);
            $emp->status_kerja = 'aktif';
            $emp->tanggal_aktif = $tanggalAktif;
            $emp->tanggal_berhenti = null;
            $emp->save();

            $emp->recordStatusHistory(
                'aktif',
                $tanggalAktif,
                $request->input('keterangan', 'Karyawan diaktifkan kembali setelah sebelumnya nonaktif.'),
                'Admin HR'
            );

            return $emp->load('statusHistories');
        });

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Karyawan berhasil diaktifkan kembali dan tanggal aktif telah tercatat di riwayat.',
                'data' => $karyawan,
            ], 200);
        }

        return redirect('/')->with('success', "Karyawan {$karyawan->nama} berhasil diaktifkan kembali.");
    }

    /**
     * Endpoint khusus penyedia data karyawan aktif untuk M13 dan M14
     */
    public function getActiveKaryawan(): JsonResponse
    {
        $karyawanAktif = DatabaseSchemaGuard::run(function () {
            return Karyawan::with('statusHistories')
                ->where('status_kerja', 'aktif')
                ->get();
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mengambil daftar karyawan aktif.',
            'data' => $karyawanAktif,
        ], 200);
    }
}
