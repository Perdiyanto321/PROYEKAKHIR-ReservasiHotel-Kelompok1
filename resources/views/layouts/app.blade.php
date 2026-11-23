<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Svara Hotel & Suites</title>

    <script>
        (function () {
            const saved = localStorage.getItem('svara_sidebar_open');
            const isOpen = saved !== null ? saved === 'true' : window.innerWidth >= 768;
            document.documentElement.dataset.sidebar = isOpen ? 'open' : 'closed';
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scrollbar-gutter: stable; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF9F6; color: #1C1917; }
        .font-editorial { font-family: 'Instrument Serif', Georgia, serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        @media (min-width: 768px) {
            html[data-sidebar="open"] aside[data-app-sidebar] {
                width: 16rem;
                min-width: 16rem;
            }
            html[data-sidebar="closed"] aside[data-app-sidebar] {
                width: 0;
                min-width: 0;
                overflow: hidden;
                border-right-width: 0;
            }
        }
        @media (max-width: 767px) {
            html[data-sidebar="closed"] aside[data-app-sidebar] {
                transform: translateX(-100%);
            }
        }
    </style>
</head>
<body x-data="{
          sidebarOpen: document.documentElement.dataset.sidebar === 'open',
          ready: false,
          setSidebar(val) {
              this.sidebarOpen = val;
              localStorage.setItem('svara_sidebar_open', val ? 'true' : 'false');
              document.documentElement.dataset.sidebar = val ? 'open' : 'closed';
          }
      }"
      x-init="setTimeout(() => ready = true, 60)"
      class="antialiased min-h-screen flex bg-[#FAF9F6] text-stone-900">

    <!-- Backdrop Mobile -->
    <div x-show="sidebarOpen"
         x-transition.opacity
         @click="setSidebar(false)"
         class="fixed inset-0 z-30 bg-stone-950/60 backdrop-blur-sm md:hidden"
         style="display: none;"></div>

    <!-- Sidebar Navigasi -->
    <aside data-app-sidebar
           :class="[
               ready ? 'transition-[width,min-width,transform] duration-200 ease-out' : '',
               sidebarOpen ? 'translate-x-0 w-64 md:w-64 md:min-w-64' : '-translate-x-full w-64 md:translate-x-0 md:w-0 md:min-w-0 md:overflow-hidden md:border-r-0'
           ]"
           class="fixed inset-y-0 left-0 z-40 w-64 bg-[#181512] text-stone-300 flex flex-col justify-between md:sticky md:top-0 md:h-screen shrink-0 border-r border-stone-800/80">

        <!-- Header Brand + Tombol Close Sidebar -->
        <div class="px-5 py-4 border-b border-stone-800/80 flex items-center justify-between shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-amber-800/20 border border-amber-700/40 flex items-center justify-center text-amber-400 font-editorial text-lg shrink-0">
                    S
                </div>
                <div class="truncate">
                    <span class="font-editorial text-xl tracking-wide text-amber-50 leading-none block">Svara Hotel</span>
                    <span class="block text-[9px] uppercase tracking-[0.22em] text-stone-400 mt-1">Hospitality Suite</span>
                </div>
            </a>
            <button @click="setSidebar(false)" title="Tutup Sidebar" class="p-1.5 rounded-lg text-stone-400 hover:text-white hover:bg-stone-800 transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        <!-- Menu Navigasi -->
        <nav @click="if (window.innerWidth < 768) setSidebar(false)" class="flex-1 overflow-y-auto no-scrollbar px-3 py-4 space-y-1 text-sm">
            <p class="px-3 pb-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-500">Menu Utama</p>

            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                <span>Ringkasan & Dashboard</span>
            </a>

            <a href="{{ route('reservations.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('reservations.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                <span>{{ auth()->user()->hasRole('tamu') ? 'Reservasi Saya' : 'Data Reservasi' }}</span>
            </a>

            @can('operasional-frontdesk')
                <div class="pt-3.5 pb-1 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-500 whitespace-nowrap">
                    Operasional Hotel
                </div>
                <a href="{{ route('rooms.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('rooms.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                    <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 16h20"/>
                    </svg>
                    <span>Unit & Status Kamar</span>
                </a>
                <a href="{{ route('reports.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('reports.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                    <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    <span>Laporan Operasional</span>
                </a>
            @endcan

            @can('kelola-master-kamar')
                <div class="pt-3.5 pb-1 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-500 whitespace-nowrap">
                    Master Data
                </div>
                <a href="{{ route('room-types.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('room-types.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                    <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
                    </svg>
                    <span>Tipe Kamar & Tarif</span>
                </a>
                <a href="{{ route('facilities.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('facilities.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                    <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Master Fasilitas</span>
                </a>
            @endcan

            @can('kelola-pengguna')
                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg transition whitespace-nowrap {{ request()->routeIs('users.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800/70 hover:text-white' }}">
                    <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>Manajemen Pengguna</span>
                </a>
            @endcan

            <div class="pt-3.5 pb-1 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-stone-500 whitespace-nowrap">
                Portal Tamu
            </div>
            <a href="{{ route('explore') }}"
               class="flex items-center justify-between px-3 py-2 rounded-lg text-stone-300 hover:bg-stone-800/70 hover:text-white transition whitespace-nowrap">
                <span class="flex items-center gap-3">
                    <svg class="w-[17px] h-[17px] shrink-0 opacity-85" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
                    </svg>
                    <span>Katalog Suites (SPA)</span>
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-stone-800 text-amber-300 border border-stone-700">Publik</span>
            </a>
        </nav>

        <!-- Kartu Pengguna & Logout -->
        <div class="p-3.5 border-t border-stone-800/80 bg-stone-950/60 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-900/40 border border-amber-700/50 text-amber-200 flex items-center justify-center text-xs font-semibold uppercase shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-amber-400/90 capitalize truncate">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" title="Pengaturan Profil" class="p-1.5 rounded-lg text-stone-400 hover:text-white hover:bg-stone-800 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </a>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-1.5 rounded-lg border border-stone-800 text-xs font-medium text-stone-300 hover:bg-stone-800 hover:text-white transition whitespace-nowrap">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Keluar dari Sesi</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Area Konten Utama -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navbar Editorial -->
        <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-b border-stone-200 px-5 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 min-w-0">
                <button @click="setSidebar(!sidebarOpen)"
                        title="Buka / Tutup Sidebar"
                        class="p-2 rounded-lg border border-stone-200 text-stone-600 hover:text-stone-900 hover:bg-stone-100 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/>
                    </svg>
                </button>

                <div class="flex items-center gap-2 text-sm min-w-0">
                    <a href="{{ route('dashboard') }}" class="text-stone-400 hover:text-stone-700 hidden sm:inline">Svara</a>
                    <span class="text-stone-300 hidden sm:inline">/</span>
                    <h1 class="font-semibold text-stone-900 truncate">@yield('header', 'Ringkasan Operasional')</h1>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
                <span class="hidden lg:inline-flex items-center gap-1.5 text-xs text-stone-500 bg-stone-100/80 border border-stone-200/80 px-3 py-1.5 rounded-lg tabular-nums">
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    {{ now()->format('d M Y') }}
                </span>

                <a href="{{ route('explore') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 text-xs font-medium text-stone-700 hover:bg-stone-50 transition">
                    <span>Lihat Katalog</span>
                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/>
                    </svg>
                </a>

                <div class="h-5 w-px bg-stone-200 hidden sm:block"></div>

                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-stone-100/80 border border-stone-200/90 hover:bg-stone-100 transition">
                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-stone-700">
                        {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                    </span>
                </a>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-700 hover:text-emerald-950 text-xs font-medium">Tutup</button>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm">
                    <p class="font-semibold mb-1">Periksa kembali isian formulir Anda:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>