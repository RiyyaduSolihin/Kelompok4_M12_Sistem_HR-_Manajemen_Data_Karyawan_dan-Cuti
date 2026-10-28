# JAWABAN KONFIRMASI DARI M12 (Kel. C3) KE M13 (Kel. C4)

> **Subject:** Re: M13 akan memakai M12 sebagai satu-satunya sumber login, identitas, dan role.
> **Status:** ✅ Terkonfirmasi & Sudah Live di Production Vercel + Shared Neon PostgreSQL (`neondb`)

---

## Konteks singkat

M13 (Kel. C4) sekarang login-nya masih ke BE M13 (`POST /api/auth/login`).
Kami sudah sepakat di tingkat ERP: **M12 = satu-satunya sumber login + data karyawan + role**. M13 fokus presensi + rekap + push ke M14.

Berikut jawaban resmi dari **Tim M12 (Kel. C3)** untuk ke-6 poin konfirmasi:

---

## 1. Endpoint login M12 yang harus dipanggil M13-FE

**Q1.** Apakah path-nya persis `/api/login`? Tolong konfirmasi base URL produksi M12 juga:

### ✅ JAWABAN M12 (Q1):
- **Base URL Produksi M12**: `https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app`
- **Path Login**: Persis `POST /api/login` (URL lengkap: `https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app/api/login`)
- **Request Body**: Menggunakan **`{ "email": "...", "password": "..." }`** (JSON). M13-FE **cukup memakai field `email` dan `password`**, tidak perlu `nik`.
- **Daftar Akun Demo Resmi (Sudah di-seed di Database Neon Production)**:

| Role | Email | Password | `employee_id` | `nik` | Nama Karyawan |
|---|---|---|---|---|---|
| **Admin HR** (`hr`) | `hr@araya.com` | `password123` | `1` | `EMP-1001` | Rani Kusumawati |
| **Employee** (`karyawan`) | `andi@araya.com` | `password123` | `3` | `EMP-1003` | Sinta Maharani |

- **Mekanisme Sinkronisasi Akun**: Karena M12, M13, dan M14 terhubung ke **1 Database PostgreSQL Neon yang sama (`neondb`)**, tabel `users` dan `employees` sudah terpusat. Semua akun demo di atas langsung aktif dan tidak perlu sinkronisasi manual antar-seed.

---

## 2. Respons login M12 — apa yang M13-FE harus simpan di cookie

**Q2.** Konfirmasi bentuk respons 200 dari `POST /api/login`:

### ✅ JAWABAN M12 (Q2):
Kami sudah menyesuaikan respons `POST /api/login` M12 agar **100% kompatibel** dengan struktur top-level yang diminta M13-FE sekaligus menyertakan objek detail `user` & `employee`:

```json
{
  "success": true,
  "message": "Login berhasil.",
  "token": "64_karakter_bearer_token_disini",
  "role": "Admin HR",
  "employee_id": 1,
  "name": "Rani Kusumawati",
  "nik": "EMP-1001",
  "data": {
    "token": "64_karakter_bearer_token_disini",
    "token_type": "Bearer",
    "role": "Admin HR",
    "employee_id": 1,
    "name": "Rani Kusumawati",
    "nik": "EMP-1001",
    "user": {
      "id": 1,
      "name": "Rani Kusumawati",
      "email": "hr@araya.com",
      "role": "Admin HR",
      "role_code": "hr",
      "employee_id": 1,
      "nik": "EMP-1001",
      "employee": {
        "id": 1,
        "nik": "EMP-1001",
        "nama": "Rani Kusumawati",
        "jabatan": "Spesialis SDM",
        "divisi": "Logistik",
        "status_kerja": "aktif",
        "tanggal_berhenti": null
      }
    }
  }
}
```

> **Catatan Nilai `role`**:
> - Untuk HR: `"role": "Admin HR"` (dan `data.user.role_code = "hr"`)
> - Untuk Karyawan: `"role": "Employee"` (dan `data.user.role_code = "karyawan"`)

**Jawaban Pertanyaan Kunci Q2:**
1. **Apakah token M12 bisa dipakai langsung di semua endpoint M13?**
   - **YA.** Token disimpan di kolom `users.api_token` pada database PostgreSQL Neon bersama (`neondb`), sehingga M13-BE dapat langsung memvalidasi token tersebut ke tabel `users` (atau via `/api/me` M12) tanpa perlu menerbitkan token khusus M13.
2. **Apakah token punya expiry? Berapa lama?**
   - **Tidak ada expiry (non-expiring selama sesi aktif)** sampai user memanggil `POST /api/logout` atau login ulang. Ini sangat aman untuk demo E2E lintas modul agar token tidak tiba-tiba kadaluarsa di tengah pengujian.
3. **Apakah `POST /api/logout` M12 membatalkan token di semua modul sekaligus?**
   - **YA.** `POST /api/logout` menghapus `api_token` (`NULL`) di tabel `users` pada database terpusat, sehingga token otomatis gugur di M12, M13, dan M14 sekaligus.

---

## 3. Endpoint cek profil (`GET /api/me`)

**Q3.** Konfirmasi bentuk respons 200 dari `GET /api/me`:

### ✅ JAWABAN M12 (Q3):
- **YA**, `GET /api/me` langsung mengembalikan **`id` numerik (`employee_id`)**, `nama`, `jabatan`, `divisi`, `nik`, `status_kerja`, dan `role` di top-level JSON maupun di dalam objek `data`.
- **M13-FE TIDAK PERLU fallback** ke `GET /api/karyawan/aktif` untuk mapping `NIK -> id`.

Contoh respons `200 OK` dari `GET /api/me` (dengan header `Authorization: Bearer <token>`):

```json
{
  "success": true,
  "message": "Data profil user yang sedang login berhasil diambil.",
  "id": 3,
  "employee_id": 3,
  "user_id": 2,
  "nama": "Sinta Maharani",
  "name": "Sinta Maharani",
  "email": "andi@araya.com",
  "jabatan": "Staff Keuangan",
  "divisi": "Keuangan",
  "nik": "EMP-1003",
  "status_kerja": "aktif",
  "role": "Employee",
  "role_code": "karyawan",
  "data": {
    "id": 3,
    "user_id": 2,
    "employee_id": 3,
    "name": "Sinta Maharani",
    "nama": "Sinta Maharani",
    "email": "andi@araya.com",
    "role": "Employee",
    "role_code": "karyawan",
    "nik": "EMP-1003",
    "jabatan": "Staff Keuangan",
    "divisi": "Keuangan",
    "status_kerja": "aktif",
    "employee": {
      "id": 3,
      "nik": "EMP-1003",
      "nama": "Sinta Maharani",
      "jabatan": "Staff Keuangan",
      "divisi": "Keuangan",
      "status_kerja": "aktif",
      "tanggal_berhenti": null
    }
  }
}
```

---

## 4. Akses `GET /api/karyawan/aktif` untuk role Karyawan biasa

**Q4.** Untuk role `Employee`, apakah M12 punya endpoint khusus untuk data diri sendiri?

### ✅ JAWABAN M12 (Q4): **YA (Ada 3 Cara Resmi untuk Role `Employee`)**
Meskipun `GET /api/karyawan/aktif` (dan `/api/employees/active`) dikunci khusus **Admin HR**, role **`Employee` (Karyawan biasa)** tetap bisa mengambil data resmi dirinya sendiri (nama, divisi, jabatan, NIK, `employee_id`) dari M12 melalui salah satu dari 3 endpoint berikut dengan mengirimkan `Authorization: Bearer <token>`:

1. **`GET /api/me`** → Langsung mengembalikan profil + objek `employee` milik sendiri (nama, divisi, jabatan, NIK, `employee_id`).
2. **`GET /api/employees`** (atau **`GET /api/karyawan`**) → Jika dipanggil oleh token ber-role `Employee`, endpoint ini **otomatis memfilter dan hanya mengembalikan 1 baris data karyawan milik dirinya sendiri** (`200 OK`, bukan `403`).
3. **`GET /api/employees/{id}`** (atau **`GET /api/karyawan/{id}`**) → Jika `{id}` adalah `employee_id` miliknya sendiri, mengembalikan `200 OK` berisi detail karyawan tersebut (jika mencoba akses `{id}` orang lain baru terkena `403 Forbidden`).

---

## 5. Kontrak Cuti dari M12 untuk US4

**Q5.** Bagaimana kontrak data Cuti dari M12 untuk US4?

### ✅ JAWABAN M12 (Q5):
- Sesuai **BRD/SRS Modul M12 (Kel. C3)**, ruang lingkup pengembangan M12 hanya mencakup **M12-US1 (Kelola Data Karyawan: Tambah, Lihat, Ubah)** dan **M12-US2 (Nonaktifkan Karyawan / Status Kerja `aktif` & `nonaktif`)**.
- Modul M12 **tidak memiliki tabel atau fitur pengajuan/approval cuti** (`GET /api/cuti` / `POST /api/cuti/approve` bukan bagian dari spesifikasi M12-US1 & M12-US2).
- **Solusi untuk M13-US4**: Status kehadiran seperti `hadir`, `izin`, `sakit`, atau `cuti` dikelola langsung pada pencatatan presensi/kehadiran di **M13**, sedangkan M12 berperan menyuplai **data master karyawan & validasi `status_kerja` (`aktif` / `nonaktif`)** agar hanya karyawan berstatus `aktif` yang dapat dihitung presensi/cutinya.

---

## 6. Konfirmasi teknis terakhir

### ✅ JAWABAN M12 (Q6a, Q6b, Q6c):

- **Q6a. Apakah token M12 (Bearer) diterima di semua endpoint M13, atau M13 harus issue token M13 sendiri?**
  - **Token M12 diterima langsung di semua endpoint M13.** M13-BE **tidak perlu** lagi membuat `POST /api/auth/login` atau menerbitkan token sendiri.
- **Q6b. Apakah M12 dan M13 pakai JWT secret yang sama, atau M13 introspect token M12?**
  - Karena kita berbagi **Shared Database PostgreSQL Neon (`neondb`)**, ada **2 opsi super mudah** bagi M13-BE untuk memvalidasi Bearer token dari M12:
    - **Opsi A (Paling Cepat — Latensi < 5ms, Rekomendasi Utama)**: M13-BE langsung query ke tabel `users` (join `employees`) di database Neon yang sama:
      ```sql
      SELECT u.id AS user_id, u.email, u.role, u.employee_id,
             e.nik, e.nama, e.jabatan, e.divisi, e.status_kerja
      FROM users u
      LEFT JOIN employees e ON e.id = u.employee_id
      WHERE u.api_token = $1
      LIMIT 1;
      ```
    - **Opsi B (Via HTTP Introspection ke M12)**: M13-BE meneruskan header `Authorization: Bearer <token>` ke `GET https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app/api/me`. Karena server Vercel M12 sudah dioptimasi di region **Singapore (`sin1`)** (1 region dengan AWS Neon Singapore `ap-southeast-1`), latensinya sekitar **~150–300ms**.
- **Q6c. Apakah ada cross-team E2E test untuk membuktikan token M12 valid di M13? Kapan?**
  - **SIAP KAPAN SAJA (Hari ini juga bisa langsung dites).** Endpoint `POST /api/login`, `GET /api/me`, `POST /api/logout`, dan `GET /api/employees/active` di `https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app` sudah **LIVE** dan bisa langsung dicoba via Swagger UI kami di:
    👉 `https://kelompok4-m12-sistem-hr-manajemen-d.vercel.app/docs`

---

## Sign-off Checklist

- [x] **C3 (M12)**: Jawab Q1–Q6 (Selesai & Terkonfirmasi)
- [x] **C3 (M12)**: Siapkan endpoint Auth (`POST /api/login`, `GET /api/me`, `POST /api/logout`) & akun demo E2E (`hr@araya.com` & `andi@araya.com`) di Production Vercel
- [ ] **C4-M13-BE**: Hapus 3 endpoint auth lokal, terima Bearer token M12
- [ ] **C4-M13-FE**: Rewire login, session, proxy ke M12
- [ ] **Joint**: Demo E2E 3 alur (HR set jam kerja, Karyawan check-in, Karyawan rekap)

