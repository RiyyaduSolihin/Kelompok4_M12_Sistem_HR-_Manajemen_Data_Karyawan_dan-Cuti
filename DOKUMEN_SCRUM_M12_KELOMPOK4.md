# IF21W0705-DS — DOKUMEN PEMBANGUNAN PERANGKAT LUNAK
## Dokumen Scrum (Scrum Document)
### M12 Sistem HR Manajemen Data Karyawan dan Cuti

- **Versi**: Draft-30/10/2026 (Revisi `[01]`)
- **Untuk**: Araya Business Hub Platform
- **Dipersiapkan oleh**: Kelompok 4 – Kelas C
- **Dosen Pengampu / Product Owner**: Dr. Ayi Purbasari, ST., MT.
- **Program Studi**: Teknik Informatika - Fakultas Teknik - Universitas Pasundan

---

## Riwayat Revisi

| Versi | Tanggal | Bab yang Berubah | Ringkasan Perubahan | Oleh |
| :--- | :--- | :--- | :--- | :--- |
| 1.0 | 29 Oktober 2026 | 1, 2, 3 | Laporan Sprint 1 | M.Riyyadu Solihin |
| 2.0 | `[tgl]` | 2, 4, 5 | `[Laporan Sprint 2 dan rekap akhir]` | `[nama]` |

---

## 1. Tim dan Aturan Kerja

### 1.1 Anggota dan Peran

| Nama | NRP | Peran | Keahlian Utama |
| :--- | :--- | :--- | :--- |
| Dr. Ayi Purbasari, ST., MT. | – | Product Owner | – |
| M.Riyyadu Solihin | 233040027 | Scrum Master + Backend | Backend |
| Riki Rikandi | 233040025 | Testing | Pengujian |
| Moreno Daffa Wisesa Gumilar | 233040035 | UI/UX | Desain |
| Syamsul Rakha Raditya | 233040032 | Analis + Backend | Backend |
| Rival Anugrah Azi Febrian | 233040007 | FrontEnd | Frontend |

### 1.2 Jadwal dan Alat Kerja

| Item | Isi |
| :--- | :--- |
| **Sprint 1** | Minggu 1–4 |
| **Sprint 2** | Minggu 5–8 |
| **Jadwal Daily Scrum** | Senin - Rabu disambung hari Jumat |
| **Scrum Board** | `[tautan Trello / Jira / GitHub Projects]` |
| **Repositori** | https://github.com/RiyyaduSolihin/Kelompok4_M12_Sistem_HR-_Manajemen_Data_Karyawan_dan-Cuti |
| **Prototipe Figma** | https://www.figma.com/design/UnFvL5xhHyVjuE4fWIMza6/Untitled?node-id=3-8&t=cI28jys5L9N27gNd-1 |

---

## 2. Product Backlog

### 2.1 Daftar User Story

| Urutan | ID | User Story | Bergantung Pada | Sprint | Status |
| :---: | :--- | :--- | :--- | :---: | :--- |
| 1 | **M12-US1** | Pengelolaan Data Karyawan | M12-US6 | 1 | Backlog |
| 2 | **M12-US2** | Menonaktifkan Karyawan Berhenti | M12-US1 | 1 | Backlog |
| 3 | **M12-US3** | Pengaturan Jatah Cuti Tahunan | M12-US1 | 2 | Backlog |
| 4 | **M12-US4** | Pengajuan Cuti Karyawan | M12-US1, M12-US3 | 2 | Backlog |
| 5 | **M12-US5** | Persetujuan / Penolakan Cuti | M12-US4 | 2 | Backlog |
| 6 | **M12-US6** | Pengelolaan Master Struktur Organisasi (Divisi & Jabatan) | - | 1 | Backlog |

### 2.2 Definition of Ready (DoR)
- [ ] User Story dan semua AC sudah ditulis di Dokumen Teknis Bab 2.2 (mencakup M12-US1 s.d. M12-US6).
- [ ] AC tambahan buatan kelompok sudah disetujui PO (validasi tolak duplikasi NIK pada M12-US1, filter pengecualian karyawan nonaktif dari proses absensi dan gaji pada M12-US2, validasi bilangan bulat positif jatah cuti pada M12-US3, validasi kecukupan sisa cuti dan tanggal bertabrakan pada M12-US4, pencatatan alasan penolakan cuti pada M12-US5, serta validasi duplikasi dan proteksi hapus divisi/jabatan pada M12-US6).
- [ ] Kontrak dengan modul lain yang dibutuhkan sudah disepakati pada Dokumen Teknis Bab 2.3 (penyediaan kontrak data induk karyawan aktif dan filter pengecualian karyawan berstatus nonaktif untuk Modul M13 Kehadiran & Modul M14 Penggajian, serta kontrak data cuti berstatus disetujui dari M12 ke M13-US4).

### 2.3 Definition of Done (DoD)
- [x] Kode sudah di-review anggota lain dan di-merge ke branch utama.
- [x] Tidak ada bug berstatus Kritis.
- [x] Sudah di-deploy dan bisa dibuka lewat URL.
- [x] Dokumen Teknis sudah diperbarui untuk story ini: skenario dan prototipe (Bab 2), desain (Bab 3), Test Case dan hasil uji (Bab 4).
- [x] File OpenAPI sudah diperbarui bila ada endpoint baru atau berubah.

---

## 3. Sprint 1

### 3.1 Sprint Planning

| Item | Isi |
| :--- | :--- |
| **User Story Terpilih** | **M12-US1** — Mengelola Data Master Karyawan<br>**M12-US2** — Menonaktifkan Karyawan yang Berhenti<br>**M12-US6** — Mengelola Data Master Struktur Organisasi (Divisi & Jabatan) |
| **Kapasitas** | 5 orang × 6 jam × 4 minggu × 80% ≈ 77 jam |
| **Total Jam** | 75 jam (≤ 77 jam kapasitas) |
| **Sprint Goal** | Admin HR dapat mengelola data master struktur organisasi (divisi & jabatan), mengelola data induk karyawan tanpa duplikasi NIK, serta menonaktifkan karyawan yang berhenti dengan riwayat tetap tersimpan dan terfilter dari proses absensi/gaji berikutnya. |

#### Daftar Task Sprint 1 (18 Task — 75 Jam)

| ID | Task | Lapisan | Berasal Dari | Jam | Penanggung Jawab |
| :--- | :--- | :--- | :--- | :---: | :--- |
| **M12-US1-T1** | Membuat tabel/migrasi `employees` (kolom: `id`, `nik` [unique], `nama`, `jabatan`, `divisi`, `status_kerja`, `tanggal_berhenti`) | Database | M12-US1 AC-1, AC-2 | 4 | M.Riyyadu Solihin |
| **M12-US1-T2** | Membuat API/Controller CRUD data karyawan beserta validasi unique NIK dan validasi input kosong (+ OpenAPI) | Backend | M12-US1 AC-1, AC-2 | 6 | M.Riyyadu Solihin |
| **M12-US1-T3** | Membuat tampilan halaman (Figma) tabel daftar karyawan serta form tambah/edit karyawan untuk Admin HR | Desain | M12-US1 AC-1, AC-2 | 4 | Moreno |
| **M12-US1-T4** | Implementasi halaman (koding) tabel daftar karyawan serta form tambah/edit karyawan untuk Admin HR | Frontend | M12-US1 AC-1, AC-2 | 6 | Rival |
| **M12-US1-T5** | Menguji skenario simpan data lengkap (berhasil) dan input NIK kembar (ditolak sistem) + code review | Pengujian | M12-US1 AC-1, AC-2 + DoD | 3 | Riki Rikandi |
| **M12-US1-T6** | Testing mengisi Dokumen Teknis Bagian Pengerjaan Sprint 1 (Bab 4 Test Case) | Pengujian | M12-US1 & US2 + DoD | 3 | Riki Rikandi |
| **M12-US1-T7** | Analisis mengisi Dokumen Teknis Bagian Pengerjaan Sprint 1 (Bab 2 Skenario Use Case) | Analisis | M12-US1 & US2 | 3 | Syamsul Rakha |
| **M12-US2-T1** | Membuat endpoint/fungsi soft-deactivation untuk mengubah `status_kerja` menjadi `nonaktif` dan mencatat `tanggal_berhenti` tanpa menghapus record (`DELETE`) dari database | Backend | M12-US2 AC-1 | 5 | M.Riyyadu Solihin |
| **M12-US2-T2** | Menyediakan filter query/endpoint data karyawan khusus yang berstatus `aktif` untuk dikonsumsi modul absensi (M13) & gaji (M14) | Backend | M12-US2 AC-2 | 4 | Syamsul Rakha |
| **M12-US2-T3** | Tampilan halaman (Figma) menambahkan tombol/modal "Nonaktifkan Karyawan" beserta input date picker Tanggal Berhenti | Desain | M12-US2 AC-1 | 3 | Moreno |
| **M12-US2-T4** | Menguji penonaktifan karyawan dan memastikan riwayat datanya masih muncul di database tetapi tidak masuk ke proses absensi/gaji + code review | Pengujian | M12-US2 AC-1, AC-2 + DoD | 3 | Riki Rikandi |
| **M12-US2-T5** | Implementasi halaman (koding) menambahkan tombol/modal "Nonaktifkan Karyawan" beserta input date picker Tanggal Berhenti | Frontend | M12-US2 AC-1 | 5 | Rival |
| **M12-US6-T1** | Desain mengisi Dokumen Teknis Bagian Pengerjaan Sprint 1 (Bab 3 Class Diagram, ERD, Sequence Diagram) & prototipe Figma Master Struktur Organisasi | Desain | M12-US6 AC-1, AC-2 | 4 | Moreno |
| **M12-US6-T2** | Membuat tabel/migrasi `divisions` dan `positions` (relasi divisi, nama jabatan, level jabatan/atasan) | Database | M12-US6 AC-1 | 4 | M.Riyyadu Solihin |
| **M12-US6-T3** | Membuat API/Controller CRUD Master Struktur Organisasi (Divisi & Jabatan) beserta validasi tolak duplikasi nama dan proteksi hapus jika masih dipakai karyawan | Backend | M12-US6 AC-1, AC-2 | 6 | Syamsul Rakha |
| **M12-US6-T4** | Implementasi halaman (koding) Master Struktur Organisasi (tabel & form divisi/jabatan serta integrasi dropdown ke form karyawan M12-US1) | Frontend | M12-US6 AC-1, AC-2 | 6 | Rival |
| **M12-US6-T5** | Menguji skenario simpan divisi/jabatan, tolak duplikasi nama, dan cegah hapus jabatan yang sedang dipakai karyawan + code review | Pengujian | M12-US6 AC-1, AC-2 + DoD | 4 | Riki Rikandi |
| **M12-US6-T6** | Deploy aplikasi Sprint 1 ke server + menulis langkah instalasi/deployment pada Dokumen Teknis Bab 3.1 dan Bab 5 | Deployment | DoD | 2 | M.Riyyadu Solihin |
| **Total** | **18 Task (M12-US1, M12-US2, M12-US6)** | | | **75** | |

