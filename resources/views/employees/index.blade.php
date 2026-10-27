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
                onclick="openModal('modal-tambah')"
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

    @if($errors->has('nik'))
        <div style="background:#FEE2E2;border-left:4px solid #DC2626;padding:1rem;border-radius:6px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 4px rgba(220,38,38,0.15);">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="width:24px;height:24px;border-radius:50%;background:#DC2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">✕</span>
                <div>
                    <p style="font-size:14px;font-weight:700;color:#991B1B;margin:0;">NIK sudah terdaftar</p>
                    <p style="font-size:12px;color:#DC2626;margin:2px 0 0;">{{ $errors->first('nik') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" style="color:#DC2626;background:none;border:none;font-size:20px;font-weight:700;cursor:pointer;margin-left:16px;" onmouseover="this.style.color='#991B1B'" onmouseout="this.style.color='#DC2626'">&times;</button>
        </div>
    @elseif($errors->any())
        <div style="background:#FEE2E2;border-left:4px solid #DC2626;padding:1rem;border-radius:6px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 1px 4px rgba(220,38,38,0.15);">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="width:24px;height:24px;border-radius:50%;background:#DC2626;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">✕</span>
                <div>
                    <p style="font-size:14px;font-weight:700;color:#991B1B;margin:0;">Gagal menyimpan data</p>
                    <p style="font-size:12px;color:#DC2626;margin:2px 0 0;">{{ $errors->first() }}</p>
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
                                        <div id="dropdown-{{ $emp->id }}" class="hidden absolute right-0 mt-1 w-44 bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-sm)] shadow-[var(--shadow)] z-20 py-1">
                                            @if($emp->status_kerja === 'aktif')
                                                <button 
                                                    type="button" 
                                                    onclick="openDeactivateModal('{{ $emp->id }}', '{{ $emp->nama }}')"
                                                    class="w-full text-left px-3 py-2 text-xs font-semibold text-[var(--danger-action)] hover:bg-[var(--danger-bg)] transition"
                                                >
                                                    Nonaktifkan karyawan
                                                </button>
                                            @else
                                                <span class="block px-3 py-2 text-xs text-[var(--text-muted)]">
                                                    Berhenti: {{ $emp->tanggal_berhenti ? \Carbon\Carbon::parse($emp->tanggal_berhenti)->translatedFormat('j M Y') : '-' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-[var(--text-muted)]">
                                <p class="text-sm">Belum ada data karyawan.</p>
                                <button onclick="openModal('modal-tambah')" class="mt-2 text-sm text-[var(--primary)] font-semibold hover:underline">
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
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-lg shadow-[var(--shadow)] overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
            <h3 class="text-lg font-bold font-head text-[var(--text)]">Tambah karyawan baru</h3>
            <button onclick="closeModal('modal-tambah')" class="text-[var(--text-muted)] hover:text-[var(--text)] text-xl font-bold">&times;</button>
        </div>

        <form action="{{ route('karyawan.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    NIK <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input 
                    type="text" 
                    name="nik" 
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
                    name="nama" 
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
                <input 
                    type="text" 
                    name="jabatan" 
                    required 
                    placeholder="Contoh: Spesialis SDM" 
                    oninvalid="this.setCustomValidity('Jabatan wajib diisi.')" 
                    oninput="this.setCustomValidity('')" 
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Divisi <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <select 
                    name="divisi" 
                    required 
                    oninvalid="this.setCustomValidity('Divisi wajib dipilih.')" 
                    onchange="this.setCustomValidity('')" 
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
                    <option value="Logistik">Logistik</option>
                    <option value="Keuangan">Keuangan</option>
                    <option value="SDM">SDM</option>
                    <option value="Teknologi Informasi">Teknologi Informasi</option>
                    <option value="Operasional">Operasional</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[var(--text)] mb-1">
                    Status kerja <span class="text-[var(--danger-solid)]">*</span>
                </label>
                <input type="hidden" name="status_kerja" value="aktif">
                <input 
                    type="text" 
                    value="Aktif" 
                    readonly 
                    tabindex="-1"
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface-2)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] cursor-not-allowed focus:outline-none select-none font-medium"
                >
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[var(--line)]">
                <button 
                    type="button" 
                    onclick="closeModal('modal-tambah')"
                    class="h-[40px] px-4 rounded-[var(--radius-sm)] border border-[var(--line-strong)] text-sm font-semibold text-[var(--text)] hover:bg-[var(--surface-2)] transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="h-[40px] px-5 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-sm font-semibold hover:bg-[var(--primary-hover)] transition"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 2: UBAH DATA KARYAWAN                -->
<!-- ========================================== -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-lg shadow-[var(--shadow)] overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
            <h3 class="text-lg font-bold font-head text-[var(--text)]">Ubah data karyawan</h3>
            <button onclick="closeModal('modal-edit')" class="text-[var(--text-muted)] hover:text-[var(--text)] text-xl font-bold">&times;</button>
        </div>

        <form id="edit-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')
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
                <input 
                    type="text" 
                    id="edit-jabatan"
                    name="jabatan" 
                    required 
                    oninvalid="this.setCustomValidity('Jabatan wajib diisi.')" 
                    oninput="this.setCustomValidity('')" 
                    class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
                >
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
                    <option value="Logistik">Logistik</option>
                    <option value="Keuangan">Keuangan</option>
                    <option value="SDM">SDM</option>
                    <option value="Teknologi Informasi">Teknologi Informasi</option>
                    <option value="Operasional">Operasional</option>
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

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-[var(--line)]">
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
</div>

<!-- ========================================== -->
<!-- MODAL 3: DETAIL KARYAWAN                   -->
<!-- ========================================== -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden bg-black/40 flex items-center justify-center p-4">
    <div class="bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-lg)] w-full max-w-md shadow-[var(--shadow)] overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--line)] flex items-center justify-between">
            <h3 class="text-lg font-bold font-head text-[var(--text)]">Detail data karyawan</h3>
            <button onclick="closeModal('modal-detail')" class="text-[var(--text-muted)] hover:text-[var(--text)] text-xl font-bold">&times;</button>
        </div>
        <div class="p-6 space-y-3 text-sm">
            <div class="flex justify-between border-b border-[var(--line)] pb-2">
                <span class="text-[var(--text-muted)]">NIK</span>
                <span id="detail-nik" class="font-mono font-bold text-[var(--text)]"></span>
            </div>
            <div class="flex justify-between border-b border-[var(--line)] pb-2">
                <span class="text-[var(--text-muted)]">Nama lengkap</span>
                <span id="detail-nama" class="font-semibold text-[var(--text)]"></span>
            </div>
            <div class="flex justify-between border-b border-[var(--line)] pb-2">
                <span class="text-[var(--text-muted)]">Jabatan</span>
                <span id="detail-jabatan" class="text-[var(--text)]"></span>
            </div>
            <div class="flex justify-between border-b border-[var(--line)] pb-2">
                <span class="text-[var(--text-muted)]">Divisi</span>
                <span id="detail-divisi" class="text-[var(--text)]"></span>
            </div>
            <div class="flex justify-between border-b border-[var(--line)] pb-2">
                <span class="text-[var(--text-muted)]">Status kerja</span>
                <span id="detail-status"></span>
            </div>
            <div class="flex justify-between border-b border-[var(--line)] pb-2" id="detail-berhenti-row">
                <span class="text-[var(--text-muted)]">Tanggal berhenti</span>
                <span id="detail-berhenti" class="text-[var(--text)]"></span>
            </div>
        </div>
        <div class="px-6 py-3 bg-[var(--surface-2)] flex justify-end">
            <button onclick="closeModal('modal-detail')" class="h-[36px] px-4 rounded-[var(--radius-sm)] bg-[var(--primary)] text-white text-xs font-semibold cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL 4: NONAKTIFKAN KARYAWAN (M12-US2)   -->
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

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function toggleDropdown(id) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            if (el.id !== id) el.classList.add('hidden');
        });
        const el = document.getElementById(id);
        el.classList.toggle('hidden');
    }

    function openEditModal(emp) {
        document.getElementById('edit-nik').value = emp.nik;
        document.getElementById('edit-nama').value = emp.nama;
        document.getElementById('edit-jabatan').value = emp.jabatan;
        document.getElementById('edit-divisi').value = emp.divisi;
        document.getElementById('edit-status').value = emp.status_kerja;
        document.getElementById('edit-form').action = `/karyawan/${emp.id}`;
        openModal('modal-edit');
    }

    function formatTanggalIndo(dateStr) {
        if (!dateStr) return '-';
        const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
    }

    function openDetailModal(emp) {
        document.getElementById('detail-nik').textContent = emp.nik;
        document.getElementById('detail-nama').textContent = emp.nama;
        document.getElementById('detail-jabatan').textContent = emp.jabatan;
        document.getElementById('detail-divisi').textContent = emp.divisi;
        
        const statusEl = document.getElementById('detail-status');
        if (emp.status_kerja === 'aktif') {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 h-[24px] px-2.5 rounded-full text-xs font-semibold bg-[var(--success-bg)] text-[var(--success-fg)]"><span class="w-2 h-2 rounded-full bg-[var(--success-solid)]"></span>Aktif</span>';
            document.getElementById('detail-berhenti-row').classList.add('hidden');
        } else {
            statusEl.innerHTML = '<span class="inline-flex items-center gap-1.5 h-[24px] px-2.5 rounded-full text-xs font-semibold bg-[var(--neutral-bg)] text-[var(--neutral-fg)]"><span class="w-2 h-2 rounded-full bg-[var(--neutral-solid)]"></span>Nonaktif</span>';
            document.getElementById('detail-berhenti-row').classList.remove('hidden');
            document.getElementById('detail-berhenti').textContent = formatTanggalIndo(emp.tanggal_berhenti);
        }

        openModal('modal-detail');
    }

    function openDeactivateModal(id, nama) {
        document.getElementById('deactivate-emp-name').textContent = nama;
        document.getElementById('deactivate-form').action = `/karyawan/${id}/deactivate`;
        openModal('modal-deactivate');
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
</script>
@endpush
@endsection
