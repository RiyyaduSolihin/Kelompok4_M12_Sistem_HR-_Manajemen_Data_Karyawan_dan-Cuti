@extends('layouts.app')

@section('title', 'Daftar Karyawan - Araya Business Hub')

@section('content')
<div class="space-y-6">

    <!-- Header Halaman: Breadcrumb, Judul H1 & Tombol Aksi Utama -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <!-- Breadcrumb / Jejak Halaman -->
            <nav class="text-xs text-[var(--text-muted)] mb-1">
                <span>SDM &amp; cuti</span>
                <span class="mx-1">/</span>
                <span class="text-[var(--text)] font-medium">Data karyawan</span>
            </nav>
            <!-- Judul H1 (Plus Jakarta Sans, 28px, Bobot 700) -->
            <h1 class="text-[28px] font-bold font-head text-[var(--text)] tracking-tight">
                Daftar karyawan
            </h1>
        </div>

        <!-- Tombol Aksi Utama (Tinggi 40px, Radius 6px, Maksimal satu tombol utama) -->
        <div>
            <button 
                type="button" 
                onclick="openTambahModal()"
                class="inline-flex items-center gap-2 h-[40px] px-4 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-sm font-semibold hover:bg-[var(--primary-hover)] transition cursor-pointer shadow-sm"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah karyawan</span>
            </button>
        </div>
    </div>

    <!-- Flash Message Notifikasi (Toast / Banner) jika ada -->
    @if(session('success'))
        <div class="bg-[var(--success-bg)] border-l-4 border-[var(--success-solid)] p-4 rounded-[var(--radius-sm)] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-5 h-5 rounded-full bg-[var(--success-solid)] text-white flex items-center justify-center text-xs font-bold">✓</span>
                <p class="text-sm font-semibold text-[var(--success-fg)]">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-[var(--success-fg)] hover:opacity-75 text-lg font-bold">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div style="background:#FEE2E2;border-left:4px solid #DC2626;padding:1rem;border-radius:6px;display:flex;align-items:flex-start;justify-content:space-between;box-shadow:0 1px 4px rgba(220,38,38,0.15);">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <span style="width:24px;height:24px;border-radius:50%;background:#DC2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">✕</span>
                <div>
                    <p style="font-size:14px;font-weight:700;color:#991B1B;margin:0;">Gagal menyimpan data</p>
                    <ul style="font-size:12px;color:#DC2626;margin:2px 0 0;padding-left:16px;list-style:disc;">
                        @foreach($errors->all() as $errorMessage)
                            <li>{{ $errorMessage }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" style="color:#DC2626;background:none;border:none;font-size:20px;font-weight:700;cursor:pointer;margin-left:16px;" onmouseover="this.style.color='#991B1B'" onmouseout="this.style.color='#DC2626'">&times;</button>
        </div>
    @endif

    <!-- Baris Alat: Pencarian dan Filter -->
    <div class="flex flex-col sm:flex-row items-center gap-3">
        <!-- Search Input -->
        <div class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[var(--text-muted)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
            <input 
                type="text" 
                id="search-input"
                onkeyup="filterTable()"
                placeholder="Cari NIK atau nama...." 
                class="w-full h-[40px] pl-9 pr-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] placeholder-[var(--text-muted)] focus:outline-none focus:border-[var(--primary)] focus:ring-1 focus:ring-[var(--primary)] transition"
            >
        </div>

        <!-- Filter Dropdown: Semua Divisi -->
        <div class="w-full sm:w-48">
            <select 
                id="filter-divisi"
                onchange="filterTable()"
                class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)] transition cursor-pointer"
            >
                <option value="">Semua divisi</option>
                <option value="Logistik">Logistik</option>
                <option value="Keuangan">Keuangan</option>
                <option value="SDM">SDM</option>
                <option value="Teknologi Informasi">Teknologi Informasi</option>
                <option value="Operasional">Operasional</option>
            </select>
        </div>

        <!-- Filter Dropdown: Semua Status -->
        <div class="w-full sm:w-48">
            <select 
                id="filter-status"
                onchange="filterTable()"
                class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)] transition cursor-pointer"
            >
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>

    <!-- Tabel Data Karyawan (Kartu radius 10px, border tipis, tanpa bayangan) -->
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="employee-table">
                <!-- Table Header: Latar Permukaan 2 (#EDF1F7), Teks 12px Redup -->
                <thead>
                    <tr class="bg-[var(--surface-2)] border-b border-[var(--line)] text-xs font-semibold text-[var(--text-muted)] font-head">
                        <th class="py-3 px-6 w-36">NIK</th>
                        <th class="py-3 px-4">Nama karyawan</th>
                        <th class="py-3 px-4">Jabatan</th>
                        <th class="py-3 px-4">Divisi</th>
                        <th class="py-3 px-4 w-36">Status kerja</th>
                        <th class="py-3 pr-6 pl-4 text-left w-44">Aksi</th>
                    </tr>
                </thead>
                <!-- Table Body: Tinggi baris 48px, Hover Permukaan 2 -->
                <tbody class="divide-y divide-[var(--line)] text-sm">
                    @forelse($employees as $emp)
                        <tr class="h-[48px] hover:bg-[var(--surface-2)] transition group employee-row" 
                            data-nik="{{ strtolower($emp->nik) }}" 
                            data-nama="{{ strtolower($emp->nama) }}" 
                            data-divisi="{{ $emp->divisi }}" 
                            data-status="{{ $emp->status_kerja }}">
                            
                            <!-- NIK (Font Monospace Sistem & Angka Sejajar Tabular) -->
                            <td class="py-3 px-6 font-mono text-[var(--text-muted)] font-medium tabular-nums">
                                {{ $emp->nik }}
                            </td>

                            <!-- Nama Karyawan -->
                            <td class="py-3 px-4 font-medium text-[var(--text)]">
                                {{ $emp->nama }}
                            </td>

                            <!-- Jabatan -->
                            <td class="py-3 px-4 text-[var(--text)]">
                                {{ $emp->jabatan }}
                            </td>

                            <!-- Divisi -->
                            <td class="py-3 px-4 text-[var(--text)]">
                                {{ $emp->divisi }}
                            </td>

                            <!-- Status Kerja (Pil Badge 24px dengan Titik 8px) -->
                            <td class="py-3 px-4">
                                @if($emp->status_kerja === 'aktif')
                                    <span class="inline-flex items-center gap-1.5 h-[24px] px-2.5 rounded-full text-xs font-semibold bg-[var(--success-bg)] text-[var(--success-fg)]">
                                        <span class="w-2 h-2 rounded-full bg-[var(--success-solid)]"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 h-[24px] px-2.5 rounded-full text-xs font-semibold bg-[var(--neutral-bg)] text-[var(--neutral-fg)]">
                                        <span class="w-2 h-2 rounded-full bg-[var(--neutral-solid)]"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Aksi yang Rapi dan Lurus dengan Judul Aksi di Ujung Kanan -->
                            <td class="py-3 pr-6 pl-4 text-left whitespace-nowrap">
                                <div class="inline-flex items-center justify-start gap-4">
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ json_encode($emp) }})"
                                        class="text-sm font-semibold text-[var(--text)] hover:text-[var(--primary)] hover:underline transition cursor-pointer"
                                    >
                                        Ubah
                                    </button>
                                    <button
                                        type="button"
                                        onclick="openDetailModal({{ json_encode($emp) }})"
                                        class="text-sm font-semibold text-[var(--text)] hover:text-[var(--primary)] hover:underline transition cursor-pointer"
                                    >
                                        Lihat
                                    </button>
                                    
                                    <!-- Aksi Menu Opsi Titik Tiga (SVG Horizontal Ellipsis Rapi) -->
                                    <div class="relative inline-block text-left">
                                        <button 
                                            type="button" 
                                            onclick="toggleDropdown('dropdown-{{ $emp->id }}')"
                                            class="w-7 h-7 inline-flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text-muted)] hover:bg-[var(--surface-2)] hover:text-[var(--text)] transition cursor-pointer"
                                            title="Opsi lainnya"
                                        >
                                            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 20 20">
                                                <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Dropdown Menu -->
                                        <div id="dropdown-{{ $emp->id }}" class="hidden absolute right-0 mt-1 w-52 bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-sm)] shadow-[var(--shadow)] z-20 py-1">
                                            @if($emp->status_kerja === 'aktif')
                                                <button
                                                    type="button"
                                                    onclick="openDeactivateModal('{{ $emp->id }}', '{{ $emp->nama }}')"
                                                    class="block w-full text-left px-3 py-2 text-xs font-semibold text-[var(--danger-action)] hover:bg-[var(--danger-bg)] transition"
                                                >
                                                    Nonaktifkan karyawan
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    onclick="openActivateModal('{{ $emp->id }}', '{{ $emp->nama }}')"
                                                    class="block w-full text-left px-3 py-2 text-xs font-semibold text-[var(--success-fg)] hover:bg-[var(--success-bg)] transition"
                                                >
                                                    Aktifkan kembali
                                                </button>
                                                <span class="block px-3 py-2 text-xs text-[var(--text-muted)] border-t border-[var(--line)]">
                                                    Berhenti: {{ $emp->tanggal_berhenti ? \Carbon\Carbon::parse($emp->tanggal_berhenti)->translatedFormat('j M Y') : '-' }}
                                                </span>
                                            @endif
                                            <button
                                                type="button"
                                                onclick="openDetailModal({{ json_encode($emp) }}, 'history')"
                                                class="block w-full text-left px-3 py-2 text-xs font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] transition border-t border-[var(--line)]"
                                            >
                                                Lihat riwayat status
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-[var(--text-muted)]">
                                <p class="text-sm">Belum ada data karyawan.</p>
                                <button onclick="openTambahModal()" class="mt-2 text-sm text-[var(--primary)] font-semibold hover:underline">
                                    Tambah karyawan sekarang
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer: Jumlah Data di Kiri dan Nomor Halaman di Kanan -->
        <div class="p-4 border-t border-[var(--line)] bg-[var(--surface)] flex items-center justify-between text-xs text-[var(--text-muted)]">
            <span id="footer-count">
                Menampilkan {{ $employees->firstItem() }}–{{ $employees->lastItem() }} dari {{ $employees->total() }} karyawan
            </span>
            <div class="inline-flex items-center gap-1 font-head">
                {{-- Tombol Prev --}}
                @if ($employees->onFirstPage())
                    <span class="w-7 h-7 flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text-muted)] cursor-not-allowed opacity-40">‹</span>
                @else
                    <a href="{{ $employees->previousPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text)] hover:bg-[var(--surface-2)] transition font-semibold">‹</a>
                @endif

                {{-- Nomor Halaman --}}
                @foreach ($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                    @if ($page == $employees->currentPage())
                        <span class="w-7 h-7 flex items-center justify-center rounded-[var(--radius-sm)] bg-[var(--primary)] text-white font-semibold text-xs shadow-sm">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="w-7 h-7 flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text)] hover:bg-[var(--surface-2)] transition font-semibold text-xs">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Tombol Next --}}
                @if ($employees->hasMorePages())
                    <a href="{{ $employees->nextPageUrl() }}" class="w-7 h-7 flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text)] hover:bg-[var(--surface-2)] transition font-semibold">›</a>
                @else
                    <span class="w-7 h-7 flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text-muted)] cursor-not-allowed opacity-40">›</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 1: TAMBAH KARYAWAN                   -->
<!-- ========================================== -->
<div id="modal-tambah" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-lg h-[600px] max-h-[90vh] shadow-[var(--shadow)] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between shrink-0">
            <h3 id="tambah-form-title" class="text-lg font-bold font-head text-[var(--text)]">Tambah karyawan baru</h3>
            <button onclick="closeModal('modal-tambah')" class="text-[var(--text-muted)] hover:text-[var(--text)] text-xl font-bold">&times;</button>
        </div>

        <form id="tambah-form" action="{{ route('karyawan.store') }}" method="POST" data-store-url="{{ route('karyawan.store') }}" data-submit-label="Simpan" class="flex flex-col flex-1 overflow-hidden" novalidate>
            @csrf

            {{-- Penanda bahwa setelah data karyawan disimpan, pengguna diarahkan ke form data KTP --}}
            <input type="hidden" name="require_ktp" id="tambah-require-ktp" value="0">

            {{-- Notifikasi Gagal Menyimpan Data (disembunyikan secara default) --}}
            <div id="tambah-error-alert" class="hidden px-6 pt-4 shrink-0">
                <div class="bg-[var(--danger-bg)] border-l-4 border-[var(--danger-solid)] p-4 rounded-[var(--radius-sm)] flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-[var(--danger-solid)] text-white flex items-center justify-center text-xs font-bold shrink-0">&times;</span>
                        <div>
                            <p class="text-sm font-semibold text-[var(--danger-fg)]">Gagal menyimpan data</p>
                            <p id="tambah-error-message" class="text-xs text-[var(--danger-solid)] mt-0.5"></p>
                        </div>
                    </div>
                    <button type="button" onclick="document.getElementById('tambah-error-alert').classList.add('hidden')" class="text-[var(--danger-solid)] hover:opacity-75 text-lg font-bold cursor-pointer shrink-0">&times;</button>
                </div>
            </div>

            <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    NIK <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input
                    type="text"
                    id="tambah-nik"
                    name="nik"
                    value="{{ old('nik') }}"
                    required
                    placeholder="Contoh: EMP-1010"
                    oninvalid="this.setCustomValidity('NIK wajib diisi.')"
                    oninput="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Nama lengkap <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input
                    type="text"
                    id="tambah-nama"
                    name="nama"
                    value="{{ old('nama') }}"
                    required
                    placeholder="Contoh: Rani Kusumawati"
                    oninvalid="this.setCustomValidity('Nama karyawan wajib diisi.')"
                    oninput="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Jabatan <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <select
                    id="tambah-jabatan"
                    name="jabatan"
                    required
                    oninvalid="this.setCustomValidity('Jabatan wajib dipilih.')"
                    onchange="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
                    <option value="" disabled @selected(! old('jabatan'))>Pilih Jabatan</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}" @selected(old('jabatan') === $pos)>{{ $pos }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Divisi <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <select
                    id="tambah-divisi"
                    name="divisi"
                    required
                    oninvalid="this.setCustomValidity('Divisi wajib dipilih.')"
                    onchange="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
                    <option value="" disabled @selected(! old('divisi'))>Pilih Divisi</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div }}" @selected(old('divisi') === $div)>{{ $div }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Status kerja <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input type="hidden" name="status_kerja" id="tambah-status" value="aktif">
                <input
                    type="text"
                    id="tambah-status-label"
                    value="Aktif"
                    readonly
                    tabindex="-1"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface-2)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] cursor-not-allowed focus:outline-none select-none font-medium"
                >
            </div>

            </div>{{-- end scrollable body --}}

            <div class="px-6 py-4 border-t border-[var(--line)] bg-[var(--surface)] flex items-center justify-end gap-3 shrink-0">
                <button
                    type="button"
                    onclick="closeModal('modal-tambah')"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="tambah-submit-label"
                    class="h-[40px] px-5 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-sm font-semibold hover:bg-[var(--primary-hover)] transition"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: UBAH DATA KARYAWAN / DATA KTP     -->
<!-- ========================================== -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-lg h-[600px] max-h-[90vh] shadow-[var(--shadow)] flex flex-col overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between shrink-0">
            <h3 class="text-lg font-bold font-head text-[var(--text)]">Ubah data karyawan</h3>
            <button onclick="closeModal('modal-edit')" class="text-[var(--text-muted)] hover:text-[var(--text)] text-xl font-bold">&times;</button>
        </div>

        {{-- Navigasi pilihan data yang akan diubah --}}
        <div class="px-6 pt-4 shrink-0">
            <div class="grid grid-cols-2 gap-3">
                <button
                    type="button"
                    id="tab-edit-karyawan"
                    onclick="showEditTab('karyawan')"
                    class="detail-tab-active"
                >
                    Data Karyawan
                </button>

                <button
                    type="button"
                    id="tab-edit-ktp"
                    onclick="showEditTab('ktp')"
                    class="detail-tab-inactive"
                >
                    Data KTP
                </button>
            </div>
        </div>

        {{-- Notifikasi Gagal Menyimpan Data (disembunyikan secara default) --}}
        <div id="edit-error-alert" class="hidden px-6 pt-4 shrink-0">
            <div class="bg-[var(--danger-bg)] border-l-4 border-[var(--danger-solid)] p-4 rounded-[var(--radius-sm)] flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-5 h-5 rounded-full bg-[var(--danger-solid)] text-white flex items-center justify-center text-xs font-bold shrink-0">&times;</span>
                    <div>
                        <p class="text-sm font-semibold text-[var(--danger-fg)]">Gagal menyimpan data</p>
                        <p id="edit-error-message" class="text-xs text-[var(--danger-solid)] mt-0.5"></p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('edit-error-alert').classList.add('hidden')" class="text-[var(--danger-solid)] hover:opacity-75 text-lg font-bold cursor-pointer shrink-0">&times;</button>
            </div>
        </div>

        {{-- TAB: UBAH DATA KARYAWAN --}}
        <div id="panel-edit-karyawan-wrap" class="flex-1 min-h-0">
            <form id="edit-form" method="POST" data-submit-label="Simpan perubahan" class="h-full flex flex-col overflow-hidden" novalidate>
                @csrf
                @method('PUT')

                <div id="panel-edit-karyawan" class="p-6 space-y-4 overflow-y-auto flex-1">
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    NIK <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input
                    type="text"
                    id="edit-nik"
                    name="nik"
                    required
                    oninvalid="this.setCustomValidity('NIK wajib diisi.')"
                    oninput="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Nama lengkap <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input
                    type="text"
                    id="edit-nama"
                    name="nama"
                    required
                    oninvalid="this.setCustomValidity('Nama karyawan wajib diisi.')"
                    oninput="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Jabatan <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <select
                    id="edit-jabatan"
                    name="jabatan"
                    required
                    oninvalid="this.setCustomValidity('Jabatan wajib dipilih.')"
                    onchange="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
                    <option value="" disabled>Pilih Jabatan</option>
                    @foreach($positions as $pos)
                        <option value="{{ $pos }}">{{ $pos }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Divisi <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <select
                    id="edit-divisi"
                    name="divisi"
                    required
                    oninvalid="this.setCustomValidity('Divisi wajib dipilih.')"
                    onchange="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
                    <option value="" disabled>Pilih Divisi</option>
                    @foreach($divisions as $div)
                        <option value="{{ $div }}">{{ $div }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Status kerja <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <select
                    id="edit-status"
                    name="status_kerja"
                    required
                    oninvalid="this.setCustomValidity('Status kerja wajib dipilih.')"
                    onchange="this.setCustomValidity('')"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>

            </div>{{-- end scrollable body --}}

            <div class="px-6 py-4 border-t border-[var(--line)] bg-[var(--surface)] flex items-center justify-end gap-3 shrink-0">
                <button
                    type="button"
                    onclick="closeModal('modal-edit')"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="h-[40px] px-5 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-sm font-semibold hover:bg-[var(--primary-hover)] transition"
                >
                    Simpan perubahan
                </button>
            </div>
            </form>
        </div>

        {{-- TAB: UBAH DATA KTP --}}
        <div id="panel-edit-ktp-wrap" class="hidden flex-1 min-h-0">
            <form id="edit-ktp-form" method="POST" data-submit-label="Simpan data KTP" class="h-full flex flex-col overflow-hidden" novalidate>
                @csrf
                @method('PUT')

                <div id="panel-edit-ktp" class="p-6 space-y-4 overflow-y-auto flex-1">
                    @include('employees.partials.ktp-fields', ['required' => true])
                </div>

                <div class="px-6 py-4 border-t border-[var(--line)] bg-[var(--surface)] flex items-center justify-end gap-3 shrink-0">
                    <button
                        type="button"
                        onclick="closeModal('modal-edit')"
                        class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="h-[40px] px-5 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-sm font-semibold hover:bg-[var(--primary-hover)] transition"
                    >
                        Simpan data KTP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 3: DETAIL KARYAWAN                   -->
<!-- ========================================== -->

<!-- ========================================== -->
<!-- MODAL 3: DETAIL KARYAWAN                   -->
<!-- ========================================== -->
<div id="modal-detail"
     class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">

    <div class="bg-[var(--surface)] border border-[var(--line)]
                rounded-xl w-full max-w-xl h-[600px] max-h-[90vh]
                flex flex-col shadow-[var(--shadow)] overflow-hidden">

        <!-- HEADER -->
        <div class="px-6 py-4 border-b border-[var(--line)]
                    flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold font-head text-[var(--text)]">
                    Detail data karyawan
                </h3>
                <p id="detail-subtitle"
                   class="text-xs text-[var(--text-muted)] mt-1">
                    Informasi lengkap karyawan
                </p>
            </div>

            <button type="button"
                    onclick="closeModal('modal-detail')"
                    class="text-[var(--text-muted)] hover:text-[var(--text)]
                           text-2xl cursor-pointer">
                &times;
            </button>
        </div>

        <!-- NAVIGASI -->
        <div class="px-6 pt-4 shrink-0">
            <div class="grid grid-cols-3 gap-3">

                <button type="button"
                        id="tab-employee"
                        onclick="showDetailTab('employee')"
                        class="detail-tab-active">
                    Data Karyawan
                </button>

                <button type="button"
                        id="tab-ktp"
                        onclick="showDetailTab('ktp')"
                        class="detail-tab-inactive">
                    Data KTP
                </button>

                <button type="button"
                        id="tab-history"
                        onclick="showDetailTab('history')"
                        class="detail-tab-inactive">
                    Riwayat
                </button>

            </div>
        </div>



        <!-- CONTENT (tinggi tetap agar semua tab sama besar) -->
        <div class="px-6 py-4 overflow-y-auto flex-1">

            <!-- PANEL DATA KARYAWAN -->
            <div id="panel-employee" class="min-h-full">

                <h4 class="text-sm font-bold text-[var(--text)] mb-1">
                    Informasi Kepegawaian
                </h4>

                <p class="text-xs text-[var(--text-muted)] mb-3">
                    Informasi pekerjaan dan status karyawan.
                </p>

                <div class="divide-y divide-[var(--line)]">

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">NIK</span>
                        <strong id="detail-nik" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Nama lengkap</span>
                        <strong id="detail-nama" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Jabatan</span>
                        <strong id="detail-jabatan" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Divisi</span>
                        <strong id="detail-divisi" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Status kerja</span>
                        <div id="detail-status">-</div>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Tanggal mulai aktif</span>
                        <strong id="detail-tanggal-aktif" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div id="detail-berhenti-row"
                         class="hidden flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Tanggal berhenti kerja</span>
                        <strong id="detail-berhenti" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                </div>
            </div>

            <!-- PANEL DATA KTP -->
            <div id="panel-ktp" class="hidden min-h-full">

                <h4 class="text-sm font-bold text-[var(--text)] mb-1">
                    Identitas Kependudukan
                </h4>

                <p class="text-xs text-[var(--text-muted)] mb-3">
                    Informasi identitas sesuai Kartu Tanda Penduduk.
                </p>

                <div class="divide-y divide-[var(--line)]">

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">NIK KTP</span>
                        <strong id="detail-nik-ktp" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Tempat lahir</span>
                        <strong id="detail-tempat-lahir" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Tanggal lahir</span>
                        <strong id="detail-tanggal-lahir" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Jenis kelamin</span>
                        <strong id="detail-jk" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Golongan darah</span>
                        <strong id="detail-goldar" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Agama</span>
                        <strong id="detail-agama" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Status perkawinan</span>
                        <strong id="detail-status-perkawinan" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                </div>

                <h4 class="text-sm font-bold text-[var(--text)] mt-6 mb-1">
                    Alamat Sesuai KTP
                </h4>

                <p class="text-xs text-[var(--text-muted)] mb-3">
                    Informasi alamat berdasarkan KTP.
                </p>

                <div class="divide-y divide-[var(--line)]">

                    <div class="flex justify-between items-start gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)] shrink-0">Alamat lengkap</span>
                        <strong id="detail-alamat" class="text-sm text-right text-[var(--text)] break-words">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">RT / RW</span>
                        <strong id="detail-rtrw" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Kelurahan / Desa</span>
                        <strong id="detail-kelurahan" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Kecamatan</span>
                        <strong id="detail-kecamatan" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Kabupaten / Kota</span>
                        <strong id="detail-kota" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Provinsi</span>
                        <strong id="detail-provinsi" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                </div>

                <h4 class="text-sm font-bold text-[var(--text)] mt-6 mb-1">
                    Informasi Tambahan
                </h4>

                <div class="divide-y divide-[var(--line)]">

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Pekerjaan</span>
                        <strong id="detail-pekerjaan" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                    <div class="flex justify-between items-center gap-4 py-3">
                        <span class="text-sm text-[var(--text-muted)]">Kewarganegaraan</span>
                        <strong id="detail-wn" class="text-sm text-right text-[var(--text)]">-</strong>
                    </div>

                </div>

            </div>

            <!-- PANEL RIWAYAT STATUS KERJA -->
            <div id="panel-history" class="hidden min-h-full">

                <h4 class="text-sm font-bold text-[var(--text)] mb-1">
                    Riwayat Status Kepegawaian
                </h4>

                <p class="text-xs text-[var(--text-muted)] mb-3">
                    Catatan kapan karyawan diaktifkan, dinonaktifkan, dan diaktifkan kembali.
                </p>

                <div id="detail-history-list" class="space-y-3">
                    {{-- Diisi secara dinamis oleh openDetailModal() --}}
                </div>

            </div>
        </div>

        <!-- FOOTER -->
        <div class="px-6 py-3 bg-[var(--surface-2)]
                    border-t border-[var(--line)]
                    flex justify-end shrink-0">

            <button type="button"
                    onclick="closeModal('modal-detail')"
                    class="h-9 px-6 rounded-[var(--radius-sm)]
                           bg-[var(--primary)] text-white text-sm
                           font-semibold hover:bg-[var(--primary-hover)]
                           transition cursor-pointer">
                Tutup
            </button>

        </div>

    </div>
</div>


<!-- ========================================== -->
<!-- MODAL 4: NONAKTIFKAN KARYAWAN (M12-US2)   -->
<!-- ========================================== -->
{{-- ========================================== --}}
{{-- MODAL 5: LENGKAPI DATA KTP KARYAWAN       --}}
{{-- ========================================== --}}
<div id="modal-ktp" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-lg h-[600px] max-h-[90vh] shadow-[var(--shadow)] flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-lg font-bold font-head text-[var(--text)]">Data Diri Sesuai KTP</h3>
                <p class="text-xs text-[var(--text-muted)] mt-0.5">Karyawan <strong id="ktp-nama-karyawan"></strong> berhasil ditambahkan. Silakan lengkapi data KTP berikut.</p>
            </div>
            <button onclick="backToEmployeeForm()" class="text-[var(--text-muted)] hover:text-[var(--text)] text-xl font-bold cursor-pointer">&times;</button>
        </div>

        {{-- Notifikasi Gagal Menyimpan Data (disembunyikan secara default) --}}
        <div id="ktp-error-alert" class="hidden px-6 pt-4 shrink-0">
            <div class="bg-[var(--danger-bg)] border-l-4 border-[var(--danger-solid)] p-4 rounded-[var(--radius-sm)] flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-5 h-5 rounded-full bg-[var(--danger-solid)] text-white flex items-center justify-center text-xs font-bold shrink-0">&times;</span>
                    <div>
                        <p class="text-sm font-semibold text-[var(--danger-fg)]">Gagal menyimpan data</p>
                        <p id="ktp-error-message" class="text-xs text-[var(--danger-solid)] mt-0.5"></p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('ktp-error-alert').classList.add('hidden')" class="text-[var(--danger-solid)] hover:opacity-75 text-lg font-bold cursor-pointer shrink-0">&times;</button>
            </div>
        </div>

        {{-- Form Body (Scrollable) --}}
        <form id="ktp-form" method="POST" data-submit-label="Simpan Data KTP" class="flex flex-col flex-1 overflow-hidden" novalidate>
            @csrf
            @method('PUT')

            <div class="p-6 space-y-4 overflow-y-auto flex-1">

                @include('employees.partials.ktp-fields', ['required' => true])

            </div>{{-- end scrollable body --}}

            {{-- Footer Tombol --}}
            <div class="px-6 py-4 border-t border-[var(--line)] bg-[var(--surface)] flex items-center justify-between gap-3 shrink-0">
                <button
                    type="button"
                    onclick="backToEmployeeForm()"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] transition cursor-pointer"
                >
                    Kembali
                </button>
                <button
                    type="submit"
                    class="h-[40px] px-5 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-sm font-semibold hover:bg-[var(--primary-hover)] transition cursor-pointer"
                >
                    Simpan Data KTP
                </button>
            </div>
        </form>
    </div>
</div>
<!-- ========================================== -->
<div id="modal-deactivate" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-md shadow-[var(--shadow)] overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)]">
            <h3 class="text-lg font-bold font-head text-[var(--text)]">Nonaktifkan karyawan?</h3>
        </div>
        <form id="deactivate-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PATCH')
            <p class="text-sm text-[var(--text-muted)]">
                Anda akan menonaktifkan status kerja <strong id="deactivate-emp-name" class="text-[var(--text)]"></strong>. Riwayat data kepegawaian akan tetap tersimpan di sistem.
            </p>
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Tanggal berhenti <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input
                    type="date"
                    name="tanggal_berhenti"
                    required
                    value="{{ date('Y-m-d') }}"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Keterangan
                </label>
                <input
                    type="text"
                    name="keterangan"
                    maxlength="255"
                    placeholder="Contoh: Karyawan mengajukan cuti panjang"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[var(--line)]">
                <button
                    type="button"
                    onclick="closeModal('modal-deactivate')"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] bg-[var(--danger-action)] text-white text-sm font-semibold hover:opacity-90 transition cursor-pointer"
                >
                    Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: AKTIFKAN KEMBALI KARYAWAN            -->
<!-- ========================================== -->
<div id="modal-activate" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-md shadow-[var(--shadow)] overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)]">
            <h3 class="text-lg font-bold font-head text-[var(--text)]">Aktifkan kembali karyawan?</h3>
        </div>
        <form id="activate-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PATCH')
            <p class="text-sm text-[var(--text-muted)]">
                Anda akan mengaktifkan kembali status kerja <strong id="activate-emp-name" class="text-[var(--text)]"></strong>. Riwayat penonaktifan sebelumnya tetap tersimpan.
            </p>
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Tanggal mulai aktif kembali <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input
                    type="date"
                    name="tanggal_aktif"
                    required
                    value="{{ date('Y-m-d') }}"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Keterangan
                </label>
                <input
                    type="text"
                    name="keterangan"
                    maxlength="255"
                    placeholder="Contoh: Karyawan kembali bekerja"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[var(--line)]">
                <button
                    type="button"
                    onclick="closeModal('modal-activate')"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] bg-[var(--success-solid)] text-white text-sm font-semibold hover:opacity-90 transition cursor-pointer"
                >
                    Aktifkan kembali
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // Kembali dari modal KTP ke form pengisian data karyawan
    // Data karyawan yang sudah ditambahkan tidak hilang: form diisi ulang
    // dengan data karyawan tersebut dan mode form diubah menjadi "Ubah".
    function backToEmployeeForm() {
        closeModal('modal-ktp');

        const emp = window.karyawanTerkini;

        if (emp) {
            fillTambahForm(emp);
        }

        openModal('modal-tambah');
    }

    // Mengisi form "Tambah karyawan" dengan data karyawan tertentu.
    function fillTambahForm(emp) {
        const form = document.getElementById('tambah-form');

        document.getElementById('tambah-nik').value = emp.nik || '';
        document.getElementById('tambah-nama').value = emp.nama || '';
        selectOptionByValue('tambah-jabatan', emp.jabatan);
        selectOptionByValue('tambah-divisi', emp.divisi);

        const status = emp.status_kerja || 'aktif';
        document.getElementById('tambah-status').value = status;
        document.getElementById('tambah-status-label').value = status === 'aktif' ? 'Aktif' : 'Nonaktif';

        // Karena data karyawan sudah tersimpan, form berubah menjadi mode ubah
        // agar simpan berikutnya memperbarui data, bukan membuat duplikat.
        form.action = `/karyawan/${emp.id}`;
        setTambahFormMethod('PUT');

        // Data KTP belum dilengkapi, jadi setelah disimpan pengguna kembali
        // diarahkan ke form data KTP.
        document.getElementById('tambah-require-ktp').value = '1';

        document.getElementById('tambah-form-title').textContent = 'Ubah data karyawan';
        document.getElementById('tambah-submit-label').textContent = 'Simpan perubahan';
        form.dataset.submitLabel = 'Simpan perubahan';
    }

    // Mengatur method spoofing pada form tambah (PUT saat mode ubah, dihapus saat tambah baru).
    function setTambahFormMethod(method) {
        const form = document.getElementById('tambah-form');
        let field = form.querySelector('input[name="_method"]');

        if (!method) {
            if (field) field.remove();
            return;
        }

        if (!field) {
            field = document.createElement('input');
            field.type = 'hidden';
            field.name = '_method';
            form.appendChild(field);
        }

        field.value = method;
    }

    // Memilih opsi pada <select>, menambah opsi baru bila belum tersedia.
    function selectOptionByValue(selectId, value) {
        if (!value) return;

        const select = document.getElementById(selectId);
        const exists = Array.from(select.options).some(opt => opt.value === value);

        if (!exists) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            select.appendChild(option);
        }

        select.value = value;
    }

    // Membuka form data KTP untuk seorang karyawan.
    function openKtpFormFor(id, nama) {
        const form = document.getElementById('ktp-form');
        const namaEl = document.getElementById('ktp-nama-karyawan');

        if (!form || !namaEl) return;

        form.action = `/karyawan/${id}/ktp`;
        namaEl.textContent = nama || '';

        closeModal('modal-tambah');
        openModal('modal-ktp');
    }

    // Membuka modal "Tambah karyawan" dalam mode kosong (menambah karyawan baru).
    function openTambahModal() {
        const form = document.getElementById('tambah-form');

        form.reset();
        form.action = form.dataset.storeUrl;
        setTambahFormMethod(null);
        document.getElementById('tambah-status').value = 'aktif';
        document.getElementById('tambah-status-label').value = 'Aktif';
        document.getElementById('tambah-form-title').textContent = 'Tambah karyawan baru';
        document.getElementById('tambah-submit-label').textContent = 'Simpan';
        form.dataset.submitLabel = 'Simpan';

        // Sembunyikan notifikasi gagal dari percobaan sebelumnya.
        const tambahErrorAlert = document.getElementById('tambah-error-alert');
        if (tambahErrorAlert) tambahErrorAlert.classList.add('hidden');

        openModal('modal-tambah');
    }

    function toggleDropdown(id) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            if (el.id !== id) el.classList.add('hidden');
        });
        const el = document.getElementById(id);
        el.classList.toggle('hidden');
    }

    function openEditModal(emp) {
        document.getElementById('edit-nik').value = emp.nik || '';
        document.getElementById('edit-nama').value = emp.nama || '';
        
        const jabatanSelect = document.getElementById('edit-jabatan');
        if (emp.jabatan) {
            let exists = Array.from(jabatanSelect.options).some(opt => opt.value === emp.jabatan);
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = emp.jabatan;
                opt.textContent = emp.jabatan;
                jabatanSelect.appendChild(opt);
            }
            jabatanSelect.value = emp.jabatan;
        }

        const divisiSelect = document.getElementById('edit-divisi');
        if (emp.divisi) {
            let existsDiv = Array.from(divisiSelect.options).some(opt => opt.value === emp.divisi);
            if (!existsDiv) {
                const opt = document.createElement('option');
                opt.value = emp.divisi;
                opt.textContent = emp.divisi;
                divisiSelect.appendChild(opt);
            }
            divisiSelect.value = emp.divisi;
        }

        document.getElementById('edit-status').value = emp.status_kerja;
        document.getElementById('edit-form').action = `/karyawan/${emp.id}`;

        // Isi kolom data KTP sesuai data karyawan terpilih.
        fillEditKtpFields(emp);
        document.getElementById('edit-ktp-form').action = `/karyawan/${emp.id}/ktp`;

        // Mulai dari tab data karyawan setiap kali modal dibuka.
        showEditTab('karyawan');

        openModal('modal-edit');
    }

    // Mengisi kolom data KTP pada form ubah data KTP.
    function fillEditKtpFields(emp) {
        const form = document.getElementById('edit-ktp-form');

        const setValue = (name, value) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field) field.value = value ?? '';
        };

        setValue('nik_ktp', emp.nik_ktp);
        setValue('tempat_lahir', emp.tempat_lahir);
        setValue('tanggal_lahir', emp.tanggal_lahir);
        setValue('jenis_kelamin', emp.jenis_kelamin);
        setValue('golongan_darah', emp.golongan_darah);
        setValue('alamat', emp.alamat || emp.address);
        setValue('rt_rw', emp.rt_rw);
        setValue('kelurahan_desa', emp.kelurahan_desa);
        setValue('kecamatan', emp.kecamatan);
        setValue('kabupaten_kota', emp.kabupaten_kota);
        setValue('provinsi', emp.provinsi);
        setValue('agama', emp.agama);
        setValue('status_perkawinan', emp.status_perkawinan);
        setValue('pekerjaan_ktp', emp.pekerjaan_ktp);
        setValue('kewarganegaraan', emp.kewarganegaraan || 'WNI');
    }

    // Mengganti tab pada modal ubah data: karyawan atau KTP.
    function showEditTab(tab) {
        const karyawanWrap = document.getElementById('panel-edit-karyawan-wrap');
        const ktpWrap = document.getElementById('panel-edit-ktp-wrap');

        const karyawanButton = document.getElementById('tab-edit-karyawan');
        const ktpButton = document.getElementById('tab-edit-ktp');

        karyawanWrap.classList.toggle('hidden', tab !== 'karyawan');
        ktpWrap.classList.toggle('hidden', tab !== 'ktp');

        karyawanButton.className = tab === 'karyawan' ? 'detail-tab-active' : 'detail-tab-inactive';
        ktpButton.className = tab === 'ktp' ? 'detail-tab-active' : 'detail-tab-inactive';

        // Sembunyikan notifikasi gagal dari tab sebelumnya agar tidak membingungkan.
        const editErrorAlert = document.getElementById('edit-error-alert');
        if (editErrorAlert) editErrorAlert.classList.add('hidden');
    }

    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '-';
        const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        // Tanggal tanpa jam (format Y-m-d) diuraikan manual agar tidak bergeser hari.
        const dateOnly = /^(\d{4})-(\d{2})-(\d{2})$/.exec(dateStr);
        if (dateOnly) {
            const tanggal = Number(dateOnly[3]);
            const indexBulan = Number(dateOnly[2]) - 1;
            return `${tanggal} ${bulan[indexBulan]} ${dateOnly[1]}`;
        }

        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
    }


function showDetailTab(tab) {
    const employeePanel = document.getElementById('panel-employee');
    const ktpPanel = document.getElementById('panel-ktp');
    const historyPanel = document.getElementById('panel-history');

    const employeeButton = document.getElementById('tab-employee');
    const ktpButton = document.getElementById('tab-ktp');
    const historyButton = document.getElementById('tab-history');

    employeePanel.classList.toggle('hidden', tab !== 'employee');
    ktpPanel.classList.toggle('hidden', tab !== 'ktp');
    historyPanel.classList.toggle('hidden', tab !== 'history');

    employeeButton.className =
        tab === 'employee'
            ? 'detail-tab-active'
            : 'detail-tab-inactive';

    ktpButton.className =
        tab === 'ktp'
            ? 'detail-tab-active'
            : 'detail-tab-inactive';

    historyButton.className =
        tab === 'history'
            ? 'detail-tab-active'
            : 'detail-tab-inactive';
}

/**
 * Menampilkan riwayat status kepegawaian (aktif / nonaktif / aktif kembali)
 * dalam bentuk linimasa waktu di tab "Riwayat".
 */
function renderStatusHistory(histories) {
    const container = document.getElementById('detail-history-list');

    if (!container) return;

    const list = Array.isArray(histories) ? histories : [];

    if (list.length === 0) {
        container.innerHTML = `
            <div class="bg-[var(--surface-2)] border border-[var(--line)] rounded-[var(--radius-lg)] p-6 text-center">
                <p class="text-sm font-semibold text-[var(--text)]">Belum ada riwayat</p>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Riwayat akan muncul saat status kerja karyawan berubah.
                </p>
            </div>
        `;
        return;
    }

    container.innerHTML = list.map(item => {
        const isAktif = item.status === 'aktif';

        const badge = isAktif
            ? `<span class="inline-flex items-center gap-1.5 h-[24px] px-2.5 rounded-full text-xs font-semibold bg-[var(--success-bg)] text-[var(--success-fg)]">
                   <span class="w-2 h-2 rounded-full bg-[var(--success-solid)]"></span>
                   Aktif
               </span>`
            : `<span class="inline-flex items-center gap-1.5 h-[24px] px-2.5 rounded-full text-xs font-semibold bg-[var(--neutral-bg)] text-[var(--neutral-fg)]">
                   <span class="w-2 h-2 rounded-full bg-[var(--neutral-solid)]"></span>
                   Nonaktif
               </span>`;

        const keterangan = item.keterangan
            ? `<p class="text-xs text-[var(--text-muted)] mt-1">${escapeHtml(item.keterangan)}</p>`
            : '';

        const changedBy = item.changed_by
            ? `<p class="text-[11px] text-[var(--text-muted)] mt-1">Diubah oleh: ${escapeHtml(item.changed_by)}</p>`
            : '';

        return `
            <div class="flex gap-3">
                <div class="flex flex-col items-center shrink-0">
                    <span class="w-3 h-3 rounded-full mt-1 ${isAktif ? 'bg-[var(--success-solid)]' : 'bg-[var(--neutral-solid)]'}"></span>
                    <span class="w-px flex-1 bg-[var(--line)] my-1"></span>
                </div>
                <div class="pb-2 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        ${badge}
                        <strong class="text-sm text-[var(--text)]">${formatTanggalIndo(item.tanggal_efektif)}</strong>
                    </div>
                    ${keterangan}
                    ${changedBy}
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Melindungi teks riwayat dari karakter HTML agar tidak merusak tampilan.
 */
function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}




function openDetailModal(emp, initialTab = 'employee') {

    // Helper untuk menampilkan data atau tanda "-"
    const setDetail = (id, value) => {
        const element = document.getElementById(id);

        if (element) {
            element.textContent =
                value !== null && value !== undefined && value !== ''
                    ? value
                    : '-';
        }
    };

    // HEADER
    setDetail(
        'detail-subtitle',
        `${emp.nik || '-'} · ${emp.nama || '-'}`
    );

    // DATA KARYAWAN
    setDetail('detail-nik', emp.nik);
    setDetail('detail-nama', emp.nama);
    setDetail('detail-jabatan', emp.jabatan);
    setDetail('detail-divisi', emp.divisi);

    setDetail(
        'detail-tanggal-aktif',
        formatTanggalIndo(emp.tanggal_aktif)
    );

    // STATUS KERJA
    const statusEl = document.getElementById('detail-status');
    const berhentiRow = document.getElementById('detail-berhenti-row');

    if (emp.status_kerja === 'aktif') {

        statusEl.innerHTML = `
            <span class="inline-flex items-center gap-1.5
                         h-[24px] px-2.5 rounded-full text-xs
                         font-semibold bg-[var(--success-bg)]
                         text-[var(--success-fg)]">
                <span class="w-2 h-2 rounded-full
                             bg-[var(--success-solid)]"></span>
                Aktif
            </span>
        `;

        berhentiRow.classList.add('hidden');

    } else {

        statusEl.innerHTML = `
            <span class="inline-flex items-center gap-1.5
                         h-[24px] px-2.5 rounded-full text-xs
                         font-semibold bg-[var(--neutral-bg)]
                         text-[var(--neutral-fg)]">
                <span class="w-2 h-2 rounded-full
                             bg-[var(--neutral-solid)]"></span>
                Nonaktif
            </span>
        `;

        berhentiRow.classList.remove('hidden');

        setDetail(
            'detail-berhenti',
            formatTanggalIndo(emp.tanggal_berhenti)
        );
    }

    // DATA KTP
    setDetail('detail-nik-ktp', emp.nik_ktp);
    setDetail('detail-tempat-lahir', emp.tempat_lahir);

    setDetail(
        'detail-tanggal-lahir',
        formatTanggalIndo(emp.tanggal_lahir)
    );

    setDetail('detail-jk', emp.jenis_kelamin);
    setDetail('detail-goldar', emp.golongan_darah);

    // ALAMAT
    setDetail('detail-alamat', emp.alamat || emp.address);
    setDetail('detail-rtrw', emp.rt_rw);
    setDetail('detail-kelurahan', emp.kelurahan_desa);
    setDetail('detail-kecamatan', emp.kecamatan);
    setDetail('detail-kota', emp.kabupaten_kota);
    setDetail('detail-provinsi', emp.provinsi);

    // INFORMASI TAMBAHAN
    setDetail('detail-agama', emp.agama);
    setDetail('detail-status-perkawinan', emp.status_perkawinan);
    setDetail('detail-pekerjaan', emp.pekerjaan_ktp);
    setDetail('detail-wn', emp.kewarganegaraan);

    // RIWAYAT STATUS KERJA
    renderStatusHistory(emp.status_histories || emp.statusHistories || []);

    // Selalu mulai dari tab yang diminta
    showDetailTab(initialTab);

    // Tampilkan modal
    openModal('modal-detail');
}


    function openDeactivateModal(id, nama) {
        document.getElementById('deactivate-emp-name').textContent = nama;
        document.getElementById('deactivate-form').action = `/karyawan/${id}/deactivate`;
        openModal('modal-deactivate');
    }

    function openActivateModal(id, nama) {
        document.getElementById('activate-emp-name').textContent = nama;
        document.getElementById('activate-form').action = `/karyawan/${id}/activate`;
        openModal('modal-activate');
    }

    function filterTable() {
        const search = document.getElementById('search-input').value.toLowerCase();
        const divisi = document.getElementById('filter-divisi').value;
        const status = document.getElementById('filter-status').value;

        let visibleCount = 0;
        const rows = document.querySelectorAll('.employee-row');
        rows.forEach(row => {
            const nik = row.getAttribute('data-nik');
            const nama = row.getAttribute('data-nama');
            const rowDivisi = row.getAttribute('data-divisi');
            const rowStatus = row.getAttribute('data-status');

            const matchSearch = nik.includes(search) || nama.includes(search);
            const matchDivisi = !divisi || rowDivisi === divisi;
            const matchStatus = !status || rowStatus === status;

            if (matchSearch && matchDivisi && matchStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        const footerCount = document.getElementById('footer-count');
        if (footerCount) {
            footerCount.textContent = `Menampilkan 1–${visibleCount} dari ${rows.length} karyawan`;
        }
    }

    // Indikator memproses form: "Menyimpan…" sesuai panduan hal 6
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = 'Menyimpan…';
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            }
        });
    });

    // Tutup dropdown jika klik di luar
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
        }
    });

    // ============================================================
    // Validasi Form Ubah Data: blokir submit + tampilkan notifikasi
    // Berlaku untuk form "Ubah Data Karyawan" dan "Ubah Data KTP".
    // Seluruh pesan notifikasi menggunakan bahasa Indonesia.
    // ============================================================
    const VALIDATION_RULES = {
        karyawan: [
            {
                name: 'nik',
                label: 'NIK',
                required: true,
                pattern: /^[A-Za-z0-9\-]+$/,
                message: 'NIK tidak boleh mengandung spasi atau karakter aneh (hanya huruf, angka, dan tanda hubung "-").',
            },
            {
                name: 'nama',
                label: 'Nama karyawan',
                required: true,
                pattern: /^[A-Za-z\s\.,']+$/,
                message: 'Nama karyawan tidak boleh mengandung angka atau karakter aneh (hanya huruf, spasi, titik, koma, dan tanda petik).',
            },
            {
                name: 'jabatan',
                label: 'Jabatan',
                required: true,
            },
            {
                name: 'divisi',
                label: 'Divisi',
                required: true,
            },
            {
                name: 'status_kerja',
                label: 'Status kerja',
                required: true,
            },
        ],
        ktp: [
            {
                name: 'nik_ktp',
                label: 'NIK KTP',
                required: true,
                pattern: /^[0-9]{16}$/,
                message: 'NIK KTP harus terdiri dari 16 digit angka.',
            },
            {
                name: 'tempat_lahir',
                label: 'Tempat lahir',
                required: true,
                pattern: /^[A-Za-z\s\.,'\-]+$/,
                message: 'Tempat lahir hanya boleh berisi huruf, spasi, titik, koma, tanda hubung, dan tanda petik.',
            },
            {
                name: 'tanggal_lahir',
                label: 'Tanggal lahir',
                required: true,
            },
            {
                name: 'jenis_kelamin',
                label: 'Jenis kelamin',
                required: true,
            },
            {
                name: 'alamat',
                label: 'Alamat',
                required: true,
                pattern: /^[A-Za-z0-9\s\.,'\-\/#]+$/,
                message: 'Alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca yang lazim (titik, koma, tanda hubung, garis miring).',
            },
            {
                name: 'rt_rw',
                label: 'RT / RW',
                required: true,
                pattern: /^[0-9\/\-]+$/,
                message: 'RT / RW hanya boleh berisi angka, garis miring, dan tanda hubung (contoh: 002/005).',
            },
            {
                name: 'kelurahan_desa',
                label: 'Kelurahan / Desa',
                required: true,
                pattern: /^[A-Za-z0-9\s\.,'\-]+$/,
                message: 'Kelurahan / Desa hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            },
            {
                name: 'kecamatan',
                label: 'Kecamatan',
                required: true,
                pattern: /^[A-Za-z0-9\s\.,'\-]+$/,
                message: 'Kecamatan hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            },
            {
                name: 'kabupaten_kota',
                label: 'Kabupaten / Kota',
                required: true,
                pattern: /^[A-Za-z0-9\s\.,'\-]+$/,
                message: 'Kabupaten / Kota hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            },
            {
                name: 'provinsi',
                label: 'Provinsi',
                required: true,
                pattern: /^[A-Za-z0-9\s\.,'\-]+$/,
                message: 'Provinsi hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            },
            {
                name: 'agama',
                label: 'Agama',
                pattern: /^[A-Za-z\s]+$/,
                message: 'Agama hanya boleh berisi huruf dan spasi.',
            },
            {
                name: 'status_perkawinan',
                label: 'Status perkawinan',
                pattern: /^[A-Za-z\s]+$/,
                message: 'Status perkawinan hanya boleh berisi huruf dan spasi.',
            },
            {
                name: 'pekerjaan_ktp',
                label: 'Pekerjaan (di KTP)',
                pattern: /^[A-Za-z0-9\s\.,'\-]+$/,
                message: 'Pekerjaan (di KTP) hanya boleh berisi huruf, angka, spasi, titik, koma, dan tanda hubung.',
            },
        ],
    };

    function attachFormValidation(formId, alertBoxId, alertMessageId, submitLabel, rules) {
        const form = document.getElementById(formId);
        if (!form) return;

        const alertBox = document.getElementById(alertBoxId);
        const alertMessage = document.getElementById(alertMessageId);

        // Tampilkan banner notifikasi gagal di atas form
        function showError(message, field) {
            if (alertBox && alertMessage) {
                alertMessage.textContent = message;
                alertBox.classList.remove('hidden');
                alertBox.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }

            if (field) {
                // Handler inline `oninvalid` pada input selalu menulis pesan
                // "wajib diisi" sehingga pesan dari server (mis. NIK duplikat)
                // tertimpa. Nonaktifkan dulu saat menampilkan pesan notifikasi.
                const inlineOnInvalid = field.oninvalid;
                field.oninvalid = null;
                field.setCustomValidity(message);
                field.focus();
                field.reportValidity();
                field.oninvalid = inlineOnInvalid;
            }
        }

        function resetSubmitButton() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = form.dataset.submitLabel || submitLabel;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        }

        // Validasi isi form: kolom wajib terisi dan karakter yang diperbolehkan.
        function validateFields() {
            for (const rule of rules) {
                const field = form.querySelector('[name="' + rule.name + '"]');
                if (!field) continue;

                const value = String(field.value || '').trim();

                if (rule.required && value === '') {
                    return { message: (rule.label || 'Kolom ini') + ' wajib diisi.', field: field };
                }

                if (rule.pattern && value !== '' && !rule.pattern.test(value)) {
                    return { message: rule.message, field: field };
                }
            }

            return null;
        }

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            // 1) Validasi sisi klien: kolom wajib terisi & karakter diperbolehkan
            const clientError = validateFields();
            if (clientError) {
                resetSubmitButton();
                showError(clientError.message, clientError.field);
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = 'Menyimpan…';
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
            }

            // 2) Kirim via AJAX supaya error server tampil di dalam form
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value || '',
                    },
                    credentials: 'same-origin',
                    redirect: 'manual',
                });

                // Sukses: server mengirim redirect (opaque). Muat ulang halaman
                // supaya flash "berhasil diperbarui" tetap ditampilkan.
                if (response.type === 'opaqueredirect' || response.status === 0) {
                    window.location.reload();
                    return;
                }

                // 422 = validasi server gagal (mis. NIK duplikat / format salah)
                if (response.status === 422) {
                    const payload = await response.json();
                    const errors = payload.errors || {};
                    const firstKey = Object.keys(errors)[0];
                    const message = firstKey ? errors[firstKey][0] : (payload.message || 'Data gagal disimpan.');
                    const field = firstKey ? form.querySelector('[name="' + firstKey + '"]') : null;

                    resetSubmitButton();
                    showError(message, field);
                    return;
                }

                // Error lain (419/500/dll) = tampilkan pesan dari server
                if (!response.ok) {
                    let message = 'Data gagal disimpan. Silakan coba lagi.';
                    try {
                        const payload = await response.json();
                        const errors = payload.errors || {};
                        const firstKey = Object.keys(errors)[0];
                        message = firstKey ? errors[firstKey][0] : (payload.message || message);
                    } catch (e) {
                        // abaikan, pakai pesan default
                    }

                    resetSubmitButton();
                    showError(message, null);
                    return;
                }

                window.location.reload();
            } catch (error) {
                resetSubmitButton();
                showError('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.', null);
            }
        });

        // Sembunyikan notifikasi begitu user mulai mengisi ulang
        form.addEventListener('input', function() {
            if (alertBox) alertBox.classList.add('hidden');
        });
    }

    attachFormValidation('ktp-form', 'ktp-error-alert', 'ktp-error-message', 'Simpan Data KTP', VALIDATION_RULES.ktp);
    attachFormValidation('edit-ktp-form', 'edit-error-alert', 'edit-error-message', 'Simpan data KTP', VALIDATION_RULES.ktp);
    attachFormValidation('edit-form', 'edit-error-alert', 'edit-error-message', 'Simpan perubahan', VALIDATION_RULES.karyawan);
    attachFormValidation('tambah-form', 'tambah-error-alert', 'tambah-error-message', 'Simpan', VALIDATION_RULES.karyawan);

    // ============================================================
    // Auto-buka Modal KTP setelah karyawan baru berhasil disimpan
    // ============================================================
    @if(session('open_ktp_modal'))
        (function() {
            const empId   = {{ session('open_ktp_modal') }};
            const empNama = @json(session('ktp_nama', ''));
            const form    = document.getElementById('ktp-form');
            const namaEl  = document.getElementById('ktp-nama-karyawan');

            // Disimpan agar tombol "Kembali" bisa mengisi ulang form data karyawan
            window.karyawanTerkini = @json(session('ktp_employee', []));

            if (form && namaEl) {
                form.action = `/karyawan/${empId}/ktp`;
                namaEl.textContent = empNama;
                openModal('modal-ktp');
            }
        })();
    @endif
</script>
@endpush
@endsection
