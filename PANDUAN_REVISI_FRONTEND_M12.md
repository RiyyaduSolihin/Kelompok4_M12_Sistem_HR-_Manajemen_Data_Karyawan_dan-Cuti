# PANDUAN INTEGRASI & REVISI FRONTEND (MODUL M12)

> **Target File Utama Frontend**: `resources/views/employees/index.blade.php`  
> **Status Backend & Database (Neon PostgreSQL)**: ✅ Sudah Selesai, Di-migrate, dan Di-seed  
> **Tujuan**: Menyesuaikan tampilan tabel, modal form Tambah/Edit, modal Detail/Riwayat, dan validasi di sisi Frontend agar sinkron 100% dengan revisi Backend terbaru.

---

## Ringkasan 3 Poin Revisi yang Harus Disesuaikan di Frontend

1. **Validasi Anti-Karakter Aneh pada `NIK` dan `Nama Karyawan`** (mencegah simbol/karakter aneh di form input & menampilkan pesan error validasi).
2. **Tanggal Status Aktif (`tanggal_aktif`), Tombol "Aktifkan Kembali", & Modal Riwayat Status (`statusHistories`)** (menampilkan histori kapan karyawan mulai aktif, kapan dinonaktifkan, dan kapan diaktifkan kembali).
3. **Input & Tampilan Data Diri Karyawan Sesuai KTP** (menambahkan field KTP pada form Tambah/Edit serta modal Detail Karyawan).

---

## 1. Validasi Input `NIK` & `Nama Karyawan` (Jangan Ada Karakter Aneh)

Backend sudah memvalidasi secara ketat agar `nik` dan `nama` menolak karakter aneh. Agar pengalaman pengguna di Frontend langsung responsif, tambahkan atribut `pattern` dan validasi pada `<input>` di Modal Tambah & Modal Edit:

### A. Aturan Format yang Diterima Backend
| Field | Name Attribute | Aturan Karakter yang Diizinkan | Regex HTML5 `pattern` | Contoh Benar | Contoh Ditolak |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **NIK Karyawan** | `name="nik"` | Hanya huruf, angka, dan tanda hubung (`-`), tanpa spasi/simbol | `^[A-Za-z0-9\-]+$` | `EMP-1009` | `EMP@1009!`, `EMP 1009` |
| **Nama Lengkap** | `name="nama"` | Hanya huruf, spasi, titik (`.`), koma (`,`), dan petik (`'`), tanpa angka/simbol | `^[A-Za-z\s\.,\']+$` | `M. Riyyadu, S.Kom.` | `Riyyadu123`, `Budi#$%` |
| **NIK KTP** | `name="nik_ktp"` | Wajib tepat **16 digit angka** murni | `^[0-9]{16}$` | `3273015503920001` | `32730155ABC` |

### B. Contoh Implementasi Input HTML di Form Tambah & Edit
```html
<!-- Input NIK Karyawan -->
<input
    type="text"
    name="nik"
    required
    maxlength="30"
    pattern="^[A-Za-z0-9\-]+$"
    placeholder="Contoh: EMP-1009"
    oninvalid="this.setCustomValidity(this.validity.ValueMissing ? 'NIK wajib diisi.' : 'NIK hanya boleh berisi huruf, angka, dan tanda hubung (-).')"
    oninput="this.setCustomValidity('')"
/>

<!-- Input Nama Lengkap Karyawan -->
<input
    type="text"
    name="nama"
    required
    maxlength="100"
    pattern="^[A-Za-z\s\.,\']+$"
    placeholder="Contoh: Rani Kusumawati"
    oninvalid="this.setCustomValidity(this.validity.ValueMissing ? 'Nama karyawan wajib diisi.' : 'Nama tidak boleh mengandung angka atau simbol/karakter aneh.')"
    oninput="this.setCustomValidity('')"
/>
```

---

## 2. Tanggal Status Aktif, Tombol "Aktifkan Kembali", & Riwayat Status

Backend sudah menyediakan:
- Kolom `$emp->tanggal_aktif` (tanggal aktif terakhir) dan `$emp->tanggal_berhenti` (tanggal berhenti terakhir).
- Relasi `$emp->statusHistories` yang otomatis di-*load* di `KaryawanController@index` maupun di API `/api/employees`.
- Route Web untuk mengaktifkan kembali karyawan yang nonaktif:
  - **Route Name**: `route('karyawan.activate', $emp->id)`
  - **Method**: `PATCH /karyawan/{id}/activate`
- Route API (jika dipanggil via Fetch/AJAX):
  - **Aktifkan Kembali**: `PATCH /api/employees/{id}/activate` (atau `/api/karyawan/{id}/activate`)
  - **Lihat Riwayat**: `GET /api/employees/{id}/status-histories` (atau `/api/karyawan/{id}/status-histories`)

### A. Penambahan Input `tanggal_aktif` di Form Tambah Karyawan
Tambahkan input tanggal mulai aktif bekerja di Modal Tambah Karyawan:
```html
<div>
    <label class="block text-sm font-semibold text-[var(--text)] mb-1">
        Tanggal Mulai Aktif
    </label>
    <input
        type="date"
        name="tanggal_aktif"
        value="{{ date('Y-m-d') }}"
        class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)]"
    >
</div>
```

### B. Tombol "Aktifkan Kembali" pada Baris Karyawan yang Berstatus `nonaktif`
Pada kolom **Aksi** di tabel daftar karyawan (`resources/views/employees/index.blade.php`), ketika `$emp->status_kerja === 'nonaktif'`, tampilkan tombol **Aktifkan** yang membuka modal konfirmasi aktivasi kembali:

```html
@if($emp->status_kerja === 'aktif')
    <!-- Tombol Nonaktifkan yang sudah ada -->
    <button type="button" onclick="openDeactivateModal({{ $emp->id }}, '{{ addslashes($emp->nama) }}')">
        Nonaktifkan
    </button>
@else
    <!-- Tombol Aktifkan Kembali untuk karyawan nonaktif -->
    <button type="button" onclick="openActivateModal({{ $emp->id }}, '{{ addslashes($emp->nama) }}')">
        Aktifkan Kembali
    </button>
@endif
```

### C. Form Modal "Aktifkan Kembali Karyawan"
Buat modal untuk mengirim `PATCH` ke `route('karyawan.activate', $id)` atau `/karyawan/{id}/activate`:
```html
<form id="activate-form" method="POST" action="">
    @csrf
    @method('PATCH')
    <div>
        <label>Tanggal Aktif Kembali <span class="text-red-600">*</span></label>
        <input type="date" name="tanggal_aktif" value="{{ date('Y-m-d') }}" required>
    </div>
    <div>
        <label>Keterangan Aktivasi</label>
        <input type="text" name="keterangan" placeholder="Contoh: Diaktifkan kembali karena perpanjangan kontrak">
    </div>
    <button type="submit">Aktifkan Karyawan</button>
</form>

<script>
function openActivateModal(id, nama) {
    document.getElementById('activate-form').action = '/karyawan/' + id + '/activate';
    // Tampilkan modal...
}
</script>
```

### D. Menampilkan Tabel / Timeline Riwayat Tanggal Status (`$emp->statusHistories`)
Setiap objek `$emp` sudah memiliki koleksi `$emp->statusHistories`. Contoh properti di setiap item riwayat:
- `$hist->status` → `'aktif'` atau `'nonaktif'`
- `$hist->tanggal_efektif` → Tanggal mulai aktif / tanggal berhenti / tanggal aktif kembali (`YYYY-MM-DD`)
- `$hist->keterangan` → Alasan / catatan status (misal: *"Karyawan terdaftar dan mulai aktif bekerja"*, *"Kontrak tahap pertama berakhir"*, *"Karyawan diaktifkan kembali setelah perpanjangan kontrak"*)
- `$hist->changed_by` → Nama HR yang mengubah

Frontend dapat menampilkannya di dalam **Modal Detail / Riwayat Karyawan** seperti ini:
```html
<div class="space-y-2">
    <h4 class="text-sm font-bold">Riwayat Tanggal Aktif & Nonaktif</h4>
    <table class="w-full text-xs border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 text-left">Status</th>
                <th class="p-2 text-left">Tanggal Efektif</th>
                <th class="p-2 text-left">Keterangan</th>
                <th class="p-2 text-left">Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emp->statusHistories as $hist)
                <tr class="border-t">
                    <td class="p-2 font-semibold">
                        {{ strtoupper($hist->status) }}
                    </td>
                    <td class="p-2 font-mono">{{ $hist->tanggal_efektif }}</td>
                    <td class="p-2">{{ $hist->keterangan ?? '-' }}</td>
                    <td class="p-2">{{ $hist->changed_by ?? 'Admin HR' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-2 text-center text-gray-400">Belum ada riwayat status.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
```

---

## 3. Daftar Field Data Diri Karyawan Sesuai KTP

Backend dan Database sudah menyiapkan **15 kolom Data Diri KTP** pada tabel `employees`. Semua field ini bersifat *nullable* (opsional saat tambah cepat, namun tervalidasi jika diisi) dan sudah memiliki data *seed* lengkap untuk karyawan `EMP-1001` s/d `EMP-1008`.

Pastikan atribut `name="..."` pada form Modal Tambah & Modal Edit di Frontend **sama persis** dengan tabel di bawah ini:

| Label Form (UI) | Attribute `name` di Form / JSON API | Tipe Input HTML | Pilihan / Validasi | Contoh Nilai di Database |
| :--- | :--- | :--- | :--- | :--- |
| **NIK KTP (16 Digit)** | `nik_ktp` | `<input type="text" maxlength="16" pattern="^[0-9]{16}$">` | 16 digit angka, unik | `3273015503920001` |
| **Tempat Lahir** | `tempat_lahir` | `<input type="text">` | Huruf & spasi (maks 100 karakter) | `Bandung` |
| **Tanggal Lahir** | `tanggal_lahir` | `<input type="date">` | Format `YYYY-MM-DD` | `1992-03-15` |
| **Jenis Kelamin** | `jenis_kelamin` | `<select>` | `Laki-laki` atau `Perempuan` | `Perempuan` |
| **Golongan Darah** | `golongan_darah` | `<select>` | `A`, `B`, `AB`, `O`, atau `-` | `O` |
| **Alamat KTP** | `alamat` | `<textarea>` atau `<input type="text">` | Maks 500 karakter | `Jl. Dr. Setiabudhi No. 193` |
| **RT / RW** | `rt_rw` | `<input type="text" placeholder="002/005">` | Angka & garis miring (`/`) | `002/005` |
| **Kelurahan / Desa** | `kelurahan_desa` | `<input type="text">` | Maks 100 karakter | `Gegerkalong` |
| **Kecamatan** | `kecamatan` | `<input type="text">` | Maks 100 karakter | `Sukasari` |
| **Kabupaten / Kota** | `kabupaten_kota` | `<input type="text">` | Maks 100 karakter | `Kota Bandung` |
| **Provinsi** | `provinsi` | `<input type="text">` | Maks 100 karakter | `Jawa Barat` |
| **Agama** | `agama` | `<select>` | `Islam`, `Kristen`, `Katolik`, `Hindu`, `Buddha`, `Konghucu` | `Islam` |
| **Status Perkawinan** | `status_perkawinan` | `<select>` | `Belum Kawin`, `Kawin`, `Cerai Hidup`, `Cerai Mati` | `Kawin` |
| **Pekerjaan (KTP)** | `pekerjaan_ktp` | `<input type="text">` | Maks 100 karakter | `Karyawan Swasta` |
| **Kewarganegaraan** | `kewarganegaraan` | `<select>` | `WNI` atau `WNA` (Default: `WNI`) | `WNI` |

---

## 4. Contoh Payload & Respons JSON API (Untuk Integrasi Fetch / Swagger)

### Endpoint yang Tersedia di Backend:
- `GET /api/employees` (atau `GET /api/karyawan`) → Mengambil daftar karyawan + KTP + `status_histories`
- `GET /api/employees/{id}` → Detail 1 karyawan + KTP + `status_histories`
- `POST /api/employees` → Tambah karyawan baru + KTP + otomatis catat `status_histories`
- `PUT /api/employees/{id}` → Edit karyawan + KTP + otomatis catat `status_histories` bila status berubah
- `PATCH /api/employees/{id}/deactivate` → Nonaktifkan karyawan (`{ "tanggal_berhenti": "2026-11-09", "keterangan": "..." }`)
- `PATCH /api/employees/{id}/activate` → Aktifkan kembali karyawan (`{ "tanggal_aktif": "2026-11-10", "keterangan": "..." }`)
- `GET /api/employees/{id}/status-histories` → Mengambil khusus daftar riwayat status aktif/nonaktif karyawan

### Contoh Objek Karyawan dari Respons Backend:
```json
{
  "id": 7,
  "nik": "EMP-1007",
  "nama": "Rizky Pratama",
  "jabatan": "Admin Gudang",
  "divisi": "Logistik",
  "status_kerja": "aktif",
  "tanggal_aktif": "2026-10-20",
  "tanggal_berhenti": null,
  "nik_ktp": "3273071408940007",
  "tempat_lahir": "Garut",
  "tanggal_lahir": "1994-08-14",
  "jenis_kelamin": "Laki-laki",
  "golongan_darah": "B",
  "alamat": "Jl. Kopo Bihbul No. 17",
  "rt_rw": "006/009",
  "kelurahan_desa": "Sayati",
  "kecamatan": "Margahayu",
  "kabupaten_kota": "Kabupaten Bandung",
  "provinsi": "Jawa Barat",
  "agama": "Islam",
  "status_perkawinan": "Kawin",
  "pekerjaan_ktp": "Karyawan Swasta",
  "kewarganegaraan": "WNI",
  "status_histories": [
    {
      "id": 7,
      "employee_id": 7,
      "status": "aktif",
      "tanggal_efektif": "2024-07-01",
      "keterangan": "Karyawan terdaftar dan mulai aktif bekerja.",
      "changed_by": "Rani Kusumawati"
    },
    {
      "id": 8,
      "employee_id": 7,
      "status": "nonaktif",
      "tanggal_efektif": "2026-08-31",
      "keterangan": "Kontrak tahap pertama berakhir.",
      "changed_by": "Rani Kusumawati"
    },
    {
      "id": 9,
      "employee_id": 7,
      "status": "aktif",
      "tanggal_efektif": "2026-10-20",
      "keterangan": "Karyawan diaktifkan kembali setelah perpanjangan kontrak.",
      "changed_by": "Rani Kusumawati"
    }
  ]
}
```

---

## 5. Checklist Pengerjaan untuk Tim Frontend
- [ ] Tambahkan atribut `pattern` & pesan validasi pada input `nik` dan `nama` di Modal Tambah & Edit agar menolak karakter aneh.
- [ ] Tambahkan field input **Data Diri KTP** (`nik_ktp`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `golongan_darah`, `alamat`, `rt_rw`, `kelurahan_desa`, `kecamatan`, `kabupaten_kota`, `provinsi`, `agama`, `status_perkawinan`, `pekerjaan_ktp`, `kewarganegaraan`) di Modal Tambah & Edit Karyawan.
- [ ] Tambahkan tombol/modal **Detail Karyawan** untuk melihat biodata lengkap KTP dan tabel **Riwayat Tanggal Status (`$emp->statusHistories`)**.
- [ ] Tambahkan tombol **Aktifkan Kembali** pada baris karyawan yang berstatus `nonaktif` yang mengarah ke `PATCH /karyawan/{id}/activate`.
