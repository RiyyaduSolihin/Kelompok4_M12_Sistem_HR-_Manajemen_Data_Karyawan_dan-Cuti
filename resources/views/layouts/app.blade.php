<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Araya Business Hub - Sistem HR')</title>

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Top Navbar (Tinggi 56px tetap - Sesuai Panduan Hal 10) -->
    <header class="h-[56px] bg-[var(--surface)] border-b border-[var(--line)] px-4 sm:px-6 flex items-center justify-between sticky top-0 z-40">
        <!-- Brand / Logo & Tombol Hamburger Mobile -->
        <div class="flex items-center gap-3 w-auto lg:w-[240px]">
            <!-- Tombol Menu Hamburger (Aktif di layar < 1024px) -->
            <button 
                type="button" 
                onclick="toggleSidebar()" 
                class="lg:hidden w-9 h-9 flex items-center justify-center rounded-[var(--radius-sm)] text-[var(--text-muted)] hover:bg-[var(--surface-2)] transition cursor-pointer"
                title="Buka menu navigasi"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <a href="{{ url('/') }}" class="text-xl font-bold font-head text-[var(--primary)] tracking-tight whitespace-nowrap">
                Araya Business Hub
            </a>
        </div>

        <!-- Global Search Bar (Tengah) -->
        <div class="hidden sm:block flex-1 max-w-xl mx-4 lg:mx-8">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[var(--text-muted)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    placeholder="Cari nama karyawan, NIK, atau departemen...." 
                    class="w-full h-[40px] pl-9 pr-4 text-sm bg-[var(--surface)] border border-[var(--line)] rounded-[var(--radius-sm)] text-[var(--text)] placeholder-[var(--text-muted)] focus:outline-none focus:ring-2 focus:ring-[var(--primary)] transition"
                >
            </div>
        </div>

        <!-- Akun & Notifikasi (Kanan) -->
        <div class="flex items-center gap-2 sm:gap-4">
            <!-- Bell Notifikasi -->
            <button class="w-9 h-9 rounded-full flex items-center justify-center text-[var(--text-muted)] hover:bg-[var(--surface-2)] transition relative cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
            </button>

            <!-- Profil User -->
            <div class="flex items-center gap-2.5 py-1 px-2 rounded-[var(--radius-sm)] hover:bg-[var(--surface-2)] transition cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-[var(--primary-soft)] text-[var(--primary)] flex items-center justify-center font-semibold text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-[var(--text)] hidden sm:inline">Admin HR</span>
            </div>
        </div>
    </header>

    <!-- Main Wrapper (Sidebar + Konten) -->
    <div class="flex-1 flex relative">
        <!-- Backdrop Laci Mobile (< 1024px) -->
        <div id="sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-black/30 z-40 hidden lg:hidden transition-opacity"></div>

        <!-- Sidebar (Lebar 240px tetap, jadi Laci di < 1024px - Sesuai Panduan Hal 10) -->
        <aside id="main-sidebar" class="w-[240px] shrink-0 bg-[var(--surface)] border-r border-[var(--line)] py-5 px-3 flex flex-col justify-between fixed lg:static inset-y-0 left-0 z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 lg:transition-none shadow-lg lg:shadow-none h-full">
            <div class="space-y-6">
                <!-- Group 1: Modul SDM & Cuti -->
                <div>
                    <h3 class="px-3 text-xs font-semibold text-[var(--text-muted)] tracking-wider mb-2 font-head">
                        Modul SDM &amp; Cuti
                    </h3>
                    <nav class="space-y-1">
                        <!-- Data karyawan (Aktif) -->
                        <a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm font-semibold bg-[var(--primary-soft)] text-[var(--primary)]">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <span>Data karyawan</span>
                        </a>

                        <!-- Jatah cuti tahunan -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm font-normal text-[var(--text)] hover:bg-[var(--surface-2)] transition">
                            <svg class="w-4 h-4 shrink-0 text-[var(--text-muted)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            <span>Jatah cuti tahunan</span>
                        </a>

                        <!-- Pengajuan cuti -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm font-normal text-[var(--text)] hover:bg-[var(--surface-2)] transition">
                            <svg class="w-4 h-4 shrink-0 text-[var(--text-muted)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                            <span>Pengajuan cuti</span>
                        </a>

                        <!-- Persetujuan cuti -->
                        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm font-normal text-[var(--text)] hover:bg-[var(--surface-2)] transition">
                            <svg class="w-4 h-4 shrink-0 text-[var(--text-muted)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>Persetujuan cuti</span>
                        </a>
                    </nav>
                </div>

                <!-- Group 2: Pengaturan -->
                <div>
                    <h3 class="px-3 text-xs font-semibold text-[var(--text-muted)] tracking-wider mb-2 font-head">
                        Pengaturan
                    </h3>
                    <nav class="space-y-1">
                        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-[var(--radius-sm)] text-sm font-normal text-[var(--text)] hover:bg-[var(--surface-2)] transition">
                            <svg class="w-4 h-4 shrink-0 text-[var(--text-muted)]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span>Pengaturan</span>
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Area Konten Utama (Padding 16px di mobile < 640px, 24px di desktop, Lebar max 1280px rata tengah) -->
        <main class="flex-1 p-4 sm:p-6 overflow-y-auto min-w-0">
            <div class="max-w-[1280px] mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Script Drawer Laci Sidebar (Layar < 1024px) -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
