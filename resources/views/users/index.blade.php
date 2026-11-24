@extends('layouts.app')

@section('header', 'Manajemen Pengguna')

@section('content')
@php
    $extractRole = function ($user) {
        $raw = $user->role ?? null;
        if (is_object($raw)) {
            return strtolower(trim((string) ($raw->slug ?? $raw->name ?? '')));
        }
        return strtolower(trim((string) $raw));
    };

    $allUsersSnapshot = \App\Models\User::all();
    $totalCount = $totalUsers ?? ($counts['all'] ?? $allUsersSnapshot->count());
    $adminCount = $adminUsers ?? ($counts['admin'] ?? $allUsersSnapshot->filter(fn ($u) => $extractRole($u) === 'admin')->count());
    $resepsionisCount = $resepsionisUsers ?? ($counts['resepsionis'] ?? $allUsersSnapshot->filter(fn ($u) => $extractRole($u) === 'resepsionis')->count());
    $tamuCount = $tamuUsers ?? ($counts['tamu'] ?? $allUsersSnapshot->filter(fn ($u) => $extractRole($u) === 'tamu')->count());
    $noRoleCount = $noRoleUsers ?? ($counts['none'] ?? $allUsersSnapshot->filter(fn ($u) => $extractRole($u) === '')->count());
    $staffCount = $adminCount + $resepsionisCount;

    $activeRole = request('role');
    $searchQuery = request('search', request('q'));

    // Deteksi otomatis nama route & HTTP method untuk update role agar kompatibel dengan routes/web.php
    $roleRouteName = collect(['users.update-role', 'users.updateRole', 'users.role', 'users.update'])
        ->first(fn ($name) => \Illuminate\Support\Facades\Route::has($name));
    $roleRouteObj = $roleRouteName
        ? \Illuminate\Support\Facades\Route::getRoutes()->getByName($roleRouteName)
        : null;
    $roleHttpMethod = $roleRouteObj
        ? collect($roleRouteObj->methods())->first(fn ($m) => in_array($m, ['PATCH', 'PUT', 'POST']))
        : 'PATCH';

    $canCreateStaff = \Illuminate\Support\Facades\Route::has('users.store');
@endphp

<div
    x-data="userRoleManager({ openCreateInitial: @js($errors->any()) })"
    class="max-w-7xl mx-auto space-y-6"
>
    <!-- Notifikasi Flash Message -->
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-[11px] font-bold shrink-0">&#10003;</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" x-on:click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold px-1.5">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 text-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-6 h-6 rounded-lg bg-rose-600 text-white flex items-center justify-center text-[11px] font-bold shrink-0">!</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" x-on:click="$el.parentElement.remove()" class="text-rose-700 hover:text-rose-950 font-bold px-1.5">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-1">
            <p class="font-semibold">Mohon periksa kembali isian formulir:</p>
            <ul class="list-disc list-inside space-y-0.5 text-rose-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Kartu Ringkasan KPI -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Total Akun</p>
                <p class="text-2xl font-bold text-stone-900 mt-1.5 tabular-nums">{{ $totalCount }} Pengguna</p>
                <p class="text-xs text-stone-500 mt-1">Seluruh akun terdaftar</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-stone-100 border border-stone-200/80 text-stone-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Tim Operasional</p>
                <p class="text-2xl font-bold text-stone-900 mt-1.5 tabular-nums">{{ $staffCount }} Staf</p>
                <p class="text-xs text-stone-500 mt-1 tabular-nums">{{ $adminCount }} admin &middot; {{ $resepsionisCount }} resepsionis</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Akun Tamu</p>
                <p class="text-2xl font-bold text-stone-900 mt-1.5 tabular-nums">{{ $tamuCount }} Tamu</p>
                <p class="text-xs text-stone-500 mt-1">Pengguna portal reservasi</p>
            </div>
            <span class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 text-emerald-800 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
        </div>
    </div>

    <!-- Peringatan Akun Tanpa Role -->
    @if ($noRoleCount > 0)
        <div class="p-4 rounded-2xl bg-amber-50/90 border border-amber-200/90 flex flex-wrap items-center justify-between gap-3 text-xs text-amber-950">
            <p class="leading-relaxed">
                <strong>{{ $noRoleCount }} akun belum memiliki role.</strong> Akun tersebut tidak bisa dipakai dengan benar sampai Anda menetapkan role (lihat tab <strong>"Tanpa Role"</strong>).
            </p>
            @if ($activeRole !== 'none')
                <a
                    href="{{ route('users.index', ['role' => 'none']) }}"
                    class="px-3 py-1.5 rounded-lg bg-amber-900 text-amber-50 text-[11px] font-semibold hover:bg-amber-950 transition shrink-0"
                >
                    Tinjau Sekarang &rarr;
                </a>
            @endif
        </div>
    @endif

    <!-- Kontainer Utama: Filter, Pencarian & Tabel Pengguna -->
    <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden">
        <!-- Bar Pencarian & Tombol Tambah Staf -->
        <div class="p-5 sm:p-6 border-b border-stone-100 flex flex-wrap items-center justify-between gap-4">
            <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                @if ($activeRole)
                    <input type="hidden" name="role" value="{{ $activeRole }}" />
                @endif

                <div class="relative flex-1 sm:w-72">
                    <span class="absolute inset-y-0 left-3.5 flex items-center text-stone-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $searchQuery }}"
                        placeholder="Cari nama atau email..."
                        class="w-full pl-9 pr-3.5 py-2 rounded-xl border-stone-300 text-xs text-stone-800 placeholder:text-stone-400 focus:border-amber-800 focus:ring-amber-800"
                    />
                </div>

                <button
                    type="submit"
                    class="px-4 py-2 rounded-xl bg-stone-100 border border-stone-300 text-xs font-semibold text-stone-800 hover:bg-stone-200/70 transition"
                >
                    Cari
                </button>

                @if ($searchQuery || $activeRole)
                    <a
                        href="{{ route('users.index') }}"
                        class="px-3 py-2 text-xs font-medium text-stone-500 hover:text-stone-900 transition"
                    >
                        Reset
                    </a>
                @endif
            </form>

            @if ($canCreateStaff)
                <button
                    type="button"
                    x-on:click="createModalOpen = true"
                    class="inline-flex items-center gap-2.5 pl-2.5 pr-4 py-2 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide hover:bg-stone-900 hover:border-amber-700/60 transition"
                >
                    <span class="w-6 h-6 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center text-sm font-bold">
                        +
                    </span>
                    <span>Akun Staf Baru</span>
                </button>
            @endif
        </div>

        <!-- Tab Filter Role -->
        <div class="px-5 sm:px-6 border-b border-stone-200 flex items-center gap-6 overflow-x-auto text-xs">
            @php
                $tabs = [
                    ['key' => null, 'label' => 'Semua', 'count' => $totalCount],
                    ['key' => 'admin', 'label' => 'Admin', 'count' => $adminCount],
                    ['key' => 'resepsionis', 'label' => 'Resepsionis', 'count' => $resepsionisCount],
                    ['key' => 'tamu', 'label' => 'Tamu', 'count' => $tamuCount],
                    ['key' => 'none', 'label' => 'Tanpa Role', 'count' => $noRoleCount],
                ];
            @endphp

            @foreach ($tabs as $tab)
                @php
                    $isCurrent = $activeRole === $tab['key'];
                    $tabParams = array_filter([
                        'role' => $tab['key'],
                        'search' => $searchQuery,
                    ]);
                @endphp
                <a
                    href="{{ route('users.index', $tabParams) }}"
                    class="py-3.5 border-b-2 inline-flex items-center gap-2 whitespace-nowrap transition {{ $isCurrent ? 'border-amber-800 text-stone-900 font-semibold' : 'border-transparent text-stone-500 hover:text-stone-800 font-medium' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold tabular-nums {{ $isCurrent ? 'bg-amber-100 text-amber-950' : 'bg-stone-100 text-stone-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>

        <!-- Tabel Daftar Pengguna -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50/70 text-[10px] font-semibold uppercase tracking-[0.14em] text-stone-400">
                        <th class="py-3.5 px-6">Pengguna</th>
                        <th class="py-3.5 px-4">Telepon</th>
                        <th class="py-3.5 px-4">Role</th>
                        <th class="py-3.5 px-4">Terdaftar</th>
                        <th class="py-3.5 px-6 text-right">Ubah Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-xs">
                    @forelse ($users as $user)
                        @php
                            $initials = collect(explode(' ', trim($user->name)))
                                ->filter()
                                ->take(2)
                                ->map(fn ($part) => strtoupper(mb_substr($part, 0, 1)))
                                ->implode('');
                            $initials = $initials ?: 'U';

                            $roleValue = $extractRole($user);
                            $roleBadgeLabel = match ($roleValue) {
                                'admin' => 'Admin',
                                'resepsionis' => 'Resepsionis',
                                'tamu' => 'Tamu',
                                default => 'Tanpa Role',
                            };
                            $roleBadgeClass = match ($roleValue) {
                                'admin' => 'bg-stone-900 text-amber-200 border-stone-800',
                                'resepsionis' => 'bg-amber-50 text-amber-900 border-amber-200',
                                'tamu' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                default => 'bg-rose-50 text-rose-800 border-rose-200',
                            };

                            $updateUrl = $roleRouteName ? route($roleRouteName, $user) : '#';
                            $isSelf = auth()->id() === $user->id;
                        @endphp
                        <tr class="hover:bg-stone-50/70 transition">
                            <!-- Kolom Pengguna -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-full bg-stone-200/80 border border-stone-300/70 text-stone-700 text-xs font-bold flex items-center justify-center shrink-0">
                                        {{ $initials }}
                                    </span>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-stone-900">{{ $user->name }}</p>
                                            @if ($isSelf)
                                                <span class="px-1.5 py-0.5 rounded bg-stone-100 border border-stone-200 text-[10px] font-medium text-stone-500">Anda</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-stone-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Telepon -->
                            <td class="py-4 px-4 text-stone-600 tabular-nums">
                                {{ $user->phone ?: '—' }}
                            </td>

                            <!-- Kolom Role Saat Ini -->
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center justify-center w-28 py-1 rounded-full border text-[11px] font-semibold {{ $roleBadgeClass }}">
                                    {{ $roleBadgeLabel }}
                                </span>
                            </td>

                            <!-- Kolom Tanggal Terdaftar -->
                            <td class="py-4 px-4 text-stone-600 tabular-nums">
                                {{ $user->created_at?->format('d M Y') ?? '—' }}
                            </td>

                            <!-- Kolom Ubah Role (Trigger Modal Konfirmasi) -->
                            <td class="py-4 px-6 text-right">
                                @if ($isSelf)
                                    <span class="text-[11px] text-stone-400 italic">Akun sedang aktif</span>
                                @elseif ($roleRouteName)
                                    <form
                                        action="{{ $updateUrl }}"
                                        method="POST"
                                        x-on:submit.prevent="openRoleConfirm($event, @js($user->name), @js($user->email), @js($roleBadgeLabel))"
                                        class="inline-flex items-center justify-end gap-2"
                                    >
                                        @csrf
                                        @if ($roleHttpMethod !== 'POST')
                                            @method($roleHttpMethod)
                                        @endif

                                        <select
                                            name="role"
                                            aria-label="Pilih role baru untuk {{ $user->name }}"
                                            class="rounded-xl border-stone-300 text-xs font-medium text-stone-800 py-1.5 pl-3 pr-8 focus:border-amber-800 focus:ring-amber-800"
                                        >
                                            <option value="resepsionis" @selected($roleValue === 'resepsionis' || $roleValue === '')>Resepsionis</option>
                                            <option value="admin" @selected($roleValue === 'admin')>Admin</option>
                                            <option value="tamu" @selected($roleValue === 'tamu')>Tamu</option>
                                        </select>

                                        <button
                                            type="submit"
                                            class="px-3.5 py-1.5 rounded-xl bg-stone-100 border border-stone-300 text-xs font-semibold text-stone-800 hover:bg-[#181512] hover:text-amber-50 hover:border-[#181512] transition"
                                        >
                                            Ubah
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 px-6 text-center text-xs text-stone-500">
                                Tidak ada data pengguna yang sesuai dengan filter saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($users, 'links') && $users->hasPages())
            <div class="px-6 py-4 border-t border-stone-100">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 1: KONFIRMASI UBAH ROLE PENGGUNA (PENGGANTI POPUP BROWSER)    -->
    <!-- =================================================================== -->
    <div
        x-show="roleModal.open"
        x-cloak
        x-on:keydown.escape.window="closeRoleConfirm()"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/60 backdrop-blur-sm"
        style="display: none;"
    >
        <div
            x-show="roleModal.open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            x-on:click.outside="closeRoleConfirm()"
            class="w-full max-w-md bg-white border border-stone-200 rounded-2xl shadow-2xl overflow-hidden"
        >
            <!-- Header Modal -->
            <div class="px-6 pt-6 pb-4 border-b border-stone-100 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-800">
                        Otorisasi Hak Akses
                    </p>
                    <h3 class="font-editorial text-2xl text-stone-900 mt-0.5">
                        Konfirmasi Perubahan Role
                    </h3>
                </div>

                <button
                    type="button"
                    x-on:click="closeRoleConfirm()"
                    class="w-8 h-8 rounded-lg border border-stone-200 text-stone-500 hover:text-stone-900 hover:bg-stone-100 flex items-center justify-center transition"
                >
                    &times;
                </button>
            </div>

            <!-- Body Modal -->
            <div class="p-6 space-y-4">
                <p class="text-xs text-stone-600 leading-relaxed">
                    Anda akan memperbarui hak akses akun berikut di sistem <strong class="text-stone-900">Svara Hotel &amp; Suites</strong>:
                </p>

                <!-- Kartu Detail Perpindahan Role -->
                <div class="p-4 rounded-xl bg-[#FAF9F6] border border-stone-200 space-y-3">
                    <div>
                        <p class="text-xs font-semibold text-stone-900" x-text="roleModal.userName"></p>
                        <p class="text-[11px] text-stone-500" x-text="roleModal.userEmail"></p>
                    </div>

                    <div class="pt-2.5 border-t border-stone-200/80 flex items-center justify-between gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-md bg-stone-200/70 text-stone-700 font-medium" x-text="roleModal.currentRole"></span>
                        <span class="text-stone-400 font-semibold">&rarr;</span>
                        <span class="px-2.5 py-1 rounded-md bg-amber-100 border border-amber-300 text-amber-950 font-semibold" x-text="roleModal.newRoleLabel"></span>
                    </div>
                </div>

                <p class="text-[11px] text-stone-500 leading-relaxed">
                    Perubahan role akan langsung memengaruhi menu dan halaman yang dapat diakses oleh pengguna tersebut.
                </p>
            </div>

            <!-- Footer Modal -->
            <div class="px-6 py-4 bg-stone-50 border-t border-stone-100 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    x-on:click="closeRoleConfirm()"
                    class="px-4 py-2 rounded-xl bg-white border border-stone-300 text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
                >
                    Batal
                </button>

                <button
                    type="button"
                    x-on:click="submitRoleConfirm()"
                    class="inline-flex items-center gap-2 pl-2.5 pr-4 py-2 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold hover:bg-stone-900 hover:border-amber-700/60 transition"
                >
                    <span class="w-5 h-5 rounded-md bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center text-[11px]">
                        &#10003;
                    </span>
                    <span>Ya, Ubah Role</span>
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 2: TAMBAH AKUN STAF BARU (ADMIN / RESEPSIONIS)                -->
    <!-- =================================================================== -->
    @if ($canCreateStaff)
        <div
            x-show="createModalOpen"
            x-cloak
            x-on:keydown.escape.window="createModalOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/60 backdrop-blur-sm"
            style="display: none;"
        >
            <div
                x-show="createModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                x-on:click.outside="createModalOpen = false"
                class="w-full max-w-lg bg-white border border-stone-200 rounded-2xl shadow-2xl overflow-hidden"
            >
                <div class="px-6 pt-6 pb-4 border-b border-stone-100 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-amber-800">
                            Registrasi Tim Internal
                        </p>
                        <h3 class="font-editorial text-2xl text-stone-900 mt-0.5">
                            Buat Akun Staf Baru
                        </h3>
                    </div>

                    <button
                        type="button"
                        x-on:click="createModalOpen = false"
                        class="w-8 h-8 rounded-lg border border-stone-200 text-stone-500 hover:text-stone-900 hover:bg-stone-100 flex items-center justify-center transition"
                    >
                        &times;
                    </button>
                </div>

                <form action="{{ route('users.store') }}" method="POST" class="p-6 space-y-4 text-xs">
                    @csrf

                    <div>
                        <label for="staff_name" class="block font-semibold text-stone-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-600">*</span>
                        </label>
                        <input
                            type="text"
                            id="staff_name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            placeholder="Contoh: Nadia Aflahatunnisa"
                            class="w-full rounded-xl border-stone-300 text-xs text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                        />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="staff_email" class="block font-semibold text-stone-700 mb-1.5">
                                Alamat Email <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="email"
                                id="staff_email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="staf@svara.test"
                                class="w-full rounded-xl border-stone-300 text-xs text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                            />
                        </div>

                        <div>
                            <label for="staff_phone" class="block font-semibold text-stone-700 mb-1.5">
                                Nomor Telepon
                            </label>
                            <input
                                type="text"
                                id="staff_phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="0812xxxxxxxx"
                                class="w-full rounded-xl border-stone-300 text-xs text-stone-800 tabular-nums focus:border-amber-800 focus:ring-amber-800"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="staff_role" class="block font-semibold text-stone-700 mb-1.5">
                            Role Akses <span class="text-rose-600">*</span>
                        </label>
                        <select
                            id="staff_role"
                            name="role"
                            required
                            class="w-full rounded-xl border-stone-300 text-xs font-medium text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                        >
                            <option value="resepsionis" @selected(old('role') === 'resepsionis')>Resepsionis</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="staff_password" class="block font-semibold text-stone-700 mb-1.5">
                                Kata Sandi <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="password"
                                id="staff_password"
                                name="password"
                                required
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-xl border-stone-300 text-xs text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                            />
                        </div>

                        <div>
                            <label for="staff_password_confirmation" class="block font-semibold text-stone-700 mb-1.5">
                                Konfirmasi Sandi <span class="text-rose-600">*</span>
                            </label>
                            <input
                                type="password"
                                id="staff_password_confirmation"
                                name="password_confirmation"
                                required
                                placeholder="Ulangi kata sandi"
                                class="w-full rounded-xl border-stone-300 text-xs text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                            />
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-100 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            x-on:click="createModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-white border border-stone-300 text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 pl-2.5 pr-4 py-2 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold hover:bg-stone-900 hover:border-amber-700/60 transition"
                        >
                            <span class="w-5 h-5 rounded-md bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center text-[11px]">
                                &#10003;
                            </span>
                            <span>Simpan Akun Staf</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
function userRoleManager({ openCreateInitial = false } = {}) {
    return {
        createModalOpen: openCreateInitial,
        roleModal: {
            open: false,
            userName: '',
            userEmail: '',
            currentRole: '',
            newRoleLabel: '',
            formEl: null,
        },

        openRoleConfirm(event, userName, userEmail, currentRole) {
            const form = event.target;
            const select = form.querySelector('select[name="role"]');
            const newRoleLabel = select && select.selectedIndex >= 0
                ? select.options[select.selectedIndex].text.trim()
                : 'Role Baru';

            this.roleModal = {
                open: true,
                userName,
                userEmail,
                currentRole,
                newRoleLabel,
                formEl: form,
            };
        },

        closeRoleConfirm() {
            this.roleModal.open = false;
            this.roleModal.formEl = null;
        },

        submitRoleConfirm() {
            if (this.roleModal.formEl) {
                this.roleModal.formEl.submit();
            }
        }
    };
}
</script>
@endsection