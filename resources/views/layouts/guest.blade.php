<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Akses Portal') — Svara Hotel & Suites</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #181512; color: #1C1917; }
        .font-editorial { font-family: 'Instrument Serif', Georgia, serif; }
    </style>
</head>
<body class="antialiased min-h-screen bg-[#181512] text-stone-900">
    @if(request()->routeIs('login', 'register'))
        @include('auth._sliding-auth', [
            'initialMode' => request()->routeIs('register') ? 'register' : 'login'
        ])
    @else
        <div class="min-h-screen flex flex-col justify-center items-center p-5 bg-[#FAF9F6]">
            <div class="w-full max-w-md bg-white border border-stone-200 rounded-2xl p-7 sm:p-8 shadow-xl">
                <div class="flex items-center justify-between mb-6">
                    <a href="{{ route('explore') }}" class="font-editorial text-2xl text-stone-900">Svara Hotel</a>
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-amber-800 hover:underline">Kembali Masuk</a>
                </div>
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </div>
    @endif
</body>
</html>