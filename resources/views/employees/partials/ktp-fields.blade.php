{{--
    Kolom data diri sesuai KTP.

    Dipakai oleh dua form:
    - "Lengkapi Data KTP" (modal-ktp) untuk karyawan yang baru ditambahkan.
    - "Ubah Data KTP" di dalam modal ubah data karyawan.

    Variabel:
    - $required (bool): menandai kolom sebagai wajib diisi. Default true.
--}}
@php($required = $required ?? true)

{{-- NIK KTP --}}
<div>
    <label class="block text-sm font-semibold text-[var(--text)] mb-1">
        NIK KTP @if($required)<span class="text-[var(--danger-solid)]">*</span> <span class="text-xs text-[var(--text-muted)] font-normal">(16 digit angka)</span>@endif
    </label>
    <input
        type="text"
        name="nik_ktp"
        @if($required) required @endif
        maxlength="16"
        inputmode="numeric"
        pattern="[0-9]{16}"
        placeholder="Contoh: 3273015503920001"
        oninvalid="this.setCustomValidity(this.value.trim() === '' ? 'NIK KTP wajib diisi.' : 'NIK KTP harus terdiri dari 16 digit angka.')"
        oninput="this.setCustomValidity('')"
        class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
    >
</div>

{{-- Tempat & Tanggal Lahir (2 kolom) --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Tempat Lahir @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <input
            type="text"
            name="tempat_lahir"
            @if($required) required @endif
            placeholder="Contoh: Bandung"
            oninvalid="this.setCustomValidity('Tempat lahir wajib diisi.')"
            oninput="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Tanggal Lahir @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <input
            type="date"
            name="tanggal_lahir"
            @if($required) required @endif
            oninvalid="this.setCustomValidity('Tanggal lahir wajib diisi.')"
            oninput="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
</div>

{{-- Jenis Kelamin & Golongan Darah (2 kolom) --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Jenis Kelamin @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <select
            name="jenis_kelamin"
            @if($required) required @endif
            oninvalid="this.setCustomValidity('Jenis kelamin wajib dipilih.')"
            onchange="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
            <option value="">— Pilih —</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Golongan Darah</label>
        <select
            name="golongan_darah"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
            <option value="">— Pilih —</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="AB">AB</option>
            <option value="O">O</option>
            <option value="-">Tidak diketahui</option>
        </select>
    </div>
</div>

{{-- Alamat --}}
<div>
    <label class="block text-sm font-semibold text-[var(--text)] mb-1">Alamat Sesuai KTP @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
    <input
        type="text"
        name="alamat"
        @if($required) required @endif
        placeholder="Contoh: Jl. Dr. Setiabudhi No. 193"
        oninvalid="this.setCustomValidity('Alamat wajib diisi.')"
        oninput="this.setCustomValidity('')"
        class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
    >
</div>

{{-- RT/RW & Kelurahan/Desa (2 kolom) --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">RT / RW @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <input
            type="text"
            name="rt_rw"
            @if($required) required @endif
            placeholder="Contoh: 002/005"
            oninvalid="this.setCustomValidity('RT / RW wajib diisi.')"
            oninput="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Kelurahan / Desa @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <input
            type="text"
            name="kelurahan_desa"
            @if($required) required @endif
            placeholder="Contoh: Gegerkalong"
            oninvalid="this.setCustomValidity('Kelurahan / Desa wajib diisi.')"
            oninput="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
</div>

{{-- Kecamatan & Kabupaten/Kota (2 kolom) --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Kecamatan @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <input
            type="text"
            name="kecamatan"
            @if($required) required @endif
            placeholder="Contoh: Sukasari"
            oninvalid="this.setCustomValidity('Kecamatan wajib diisi.')"
            oninput="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Kabupaten / Kota @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
        <input
            type="text"
            name="kabupaten_kota"
            @if($required) required @endif
            placeholder="Contoh: Kota Bandung"
            oninvalid="this.setCustomValidity('Kabupaten / Kota wajib diisi.')"
            oninput="this.setCustomValidity('')"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
</div>

{{-- Provinsi --}}
<div>
    <label class="block text-sm font-semibold text-[var(--text)] mb-1">Provinsi @if($required)<span class="text-[var(--danger-solid)]">*</span>@endif</label>
    <input
        type="text"
        name="provinsi"
        @if($required) required @endif
        placeholder="Contoh: Jawa Barat"
        oninvalid="this.setCustomValidity('Provinsi wajib diisi.')"
        oninput="this.setCustomValidity('')"
        class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
    >
</div>

{{-- Agama & Status Perkawinan (2 kolom) --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Agama</label>
        <select
            name="agama"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
            <option value="">— Pilih —</option>
            <option value="Islam">Islam</option>
            <option value="Kristen Protestan">Kristen Protestan</option>
            <option value="Kristen Katolik">Kristen Katolik</option>
            <option value="Hindu">Hindu</option>
            <option value="Buddha">Buddha</option>
            <option value="Konghucu">Konghucu</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Status Perkawinan</label>
        <select
            name="status_perkawinan"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
            <option value="">— Pilih —</option>
            <option value="Belum Kawin">Belum Kawin</option>
            <option value="Kawin">Kawin</option>
            <option value="Cerai Hidup">Cerai Hidup</option>
            <option value="Cerai Mati">Cerai Mati</option>
        </select>
    </div>
</div>

{{-- Pekerjaan KTP & Kewarganegaraan (2 kolom) --}}
<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Pekerjaan (di KTP)</label>
        <input
            type="text"
            name="pekerjaan_ktp"
            placeholder="Contoh: Karyawan Swasta"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
    </div>
    <div>
        <label class="block text-sm font-semibold text-[var(--text)] mb-1">Kewarganegaraan</label>
        <select
            name="kewarganegaraan"
            class="w-full h-[40px] px-3 text-sm bg-[var(--surface)] border border-[var(--line-strong)] rounded-[var(--radius-sm)] text-[var(--text)] focus:outline-none focus:border-[var(--primary)]"
        >
            <option value="WNI" selected>WNI</option>
            <option value="WNA">WNA</option>
        </select>
    </div>
</div>
