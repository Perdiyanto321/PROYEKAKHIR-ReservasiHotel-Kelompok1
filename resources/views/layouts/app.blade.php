<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Svara Hotel & Suites</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF9F6; color: #1C1917; }
        .font-editorial { font-family: 'Instrument Serif', Georgia, serif; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Sidebar Navigasi -->
    <aside class="w-full md:w-64 bg-stone-900 text-stone-200 flex-shrink-0 flex flex-col justify-between">
        <div>
            <div class="px-6 py-6 border-b border-stone-800">
                <a href="{{ route('dashboard') }}" class="block">
                    <span class="font-editorial text-2xl tracking-wide text-amber-50">Svara Hotel</span>
                    <span class="block text-[11px] uppercase tracking-[0.2em] text-stone-400 mt-0.5">Hospitality Suite</span>
                </a>
            </div>

            <nav class="px-3 py-5 space-y-1 text-sm">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                    <span>Ringkasan & Dashboard</span>
                </a>

                <a href="{{ route('reservations.index') }}"
                   class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('reservations.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                    <span>{{ auth()->user()->hasRole('tamu') ? 'Reservasi Saya' : 'Data Reservasi' }}</span>
                </a>

                @can('operasional-frontdesk')
                    <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                        Operasional Hotel
                    </div>
                    <a href="{{ route('rooms.index') }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('rooms.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span>Unit & Status Kamar</span>
                    </a>
                @endcan

                @can('kelola-master-kamar')
                    <div class="pt-4 pb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                        Master Data
                    </div>
                    <a href="{{ route('room-types.index') }}"
                       class="flex items-center justify-between px-3 py-2.5 rounded-lg transition {{ request()->routeIs('room-types.*') ? 'bg-amber-800 text-white font-medium' : 'text-stone-300 hover:bg-stone-800 hover:text-white' }}">
                        <span>Tipe Kamar & Tarif</span>
                    </a>
                @endcan
            </nav>
        </div>

        <!-- Profil & Logout -->
        <div class="p-4 border-t border-stone-800 bg-stone-950/40">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-amber-400/90 capitalize">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="text-xs text-stone-400 hover:text-white underline">Profil</a>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-1.5 rounded-md text-xs font-medium text-stone-300 hover:bg-stone-800 hover:text-white transition">
                    Keluar dari Sesi
                </button>
            </form>
        </div>
    </aside>

    <!-- Konten Utama -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-stone-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-stone-900">@yield('header', 'Svara Management System')</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('reservations.create') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-800 text-white text-sm font-medium hover:bg-amber-900 transition">
                    + Reservasi Baru
                </a>
            </div>
        </header>

        <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
            @if (session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm">
                    <p class="font-semibold mb-1">Terdapat kesalahan pada isian formulir:</p>
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