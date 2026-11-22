<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Akses Portal — Svara Hotel & Suites</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FAF9F6; color: #1C1917; }
        .font-editorial { font-family: 'Instrument Serif', Georgia, serif; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col justify-center items-center p-6">
    <div class="text-center mb-6">
        <span class="font-editorial text-4xl text-stone-900">Svara Hotel & Suites</span>
        <p class="text-xs uppercase tracking-[0.2em] text-stone-500 mt-1">Sistem Manajemen & Reservasi</p>
    </div>

    <div class="w-full sm:max-w-md bg-white border border-stone-200 px-7 py-7 rounded-2xl">
        {{ $slot }}
    </div>
</body>
</html>