@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('header', 'Manajemen Akun Pengguna')

@section('content')
@php
    $roleStyles = [
        'admin'       => 'bg-[#181512] text-amber-50 border-stone-800',
        'resepsionis' => 'bg-amber-50 text-amber-900 border-amber-300/70',
        'tamu'        => 'bg-stone-100 text-stone-700 border-stone-300/80',
    ];
    $roleLabels = ['admin' => 'Admin', 'resepsionis' => 'Resepsionis', 'tamu' => 'Tamu'];
    $tabs = [
        '' => ['label' => 'Semua', 'count' => $counts['all']],
        'admin' => ['label' => 'Admin', 'count' => $counts['admin']],
        'resepsionis' => ['label' => 'Resepsionis', 'count' => $counts['resepsionis']],
        'tamu' => ['label' => 'Tamu', 'count' => $counts['tamu']],
    ];
    if ($counts['none'] > 0 || $filterRole === 'none') {
        $tabs['none'] = ['label' => 'Tanpa Role', 'count' => $counts['none']];
    }
    $activeRole = (string) ($filterRole ?? '');
@endphp

<!-- KPI Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Total Akun" value="{{ $counts['all'] }} Pengguna" subtitle="Seluruh akun terdaftar">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Akun Staf" value="{{ $counts['admin'] + $counts['resepsionis'] }} Staf" subtitle="{{ $counts['admin'] }} admin · {{ $counts['resepsionis'] }} resepsionis">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Akun Tamu" value="{{ $counts['tamu'] }} Tamu" subtitle="Pengguna portal reservasi">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>
</div>

@if($counts['none'] > 0)
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-300/80 text-amber-950 text-xs">
        <span class="font-semibold">{{ $counts['none'] }} akun belum memiliki role.</span>
        Akun tersebut tidak bisa dipakai dengan benar sampai Anda menetapkan role (lihat tab "Tanpa Role").
    </div>
@endif

<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <!-- Toolbar -->
    <div class="p-5 border-b border-stone-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('users.index') }}" class="flex items-center gap-2 w-full md:max-w-md">
            @if($filterRole)
                <input type="hidden" name="role" value="{{ $filterRole }}">
            @endif
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama atau email..."
                       class="w-full pl-9 pr-3 py-2 rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg border border-stone-300 bg-stone-50 text-sm font-medium text-stone-700 hover:bg-stone-100 transition shrink-0">Cari</button>
            @if(request('cari') || $filterRole)
                <a href="{{ route('users.index') }}" class="px-2.5 py-2 text-xs font-medium text-stone-500 hover:text-stone-900 shrink-0">Reset</a>
            @endif
        </form>

        <button type="button" @click="$dispatch('open-modal', 'create-staff')"
                class="group inline-flex items-center justify-between sm:justify-center gap-3 pl-2 pr-4 py-1.5 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide shadow-[0_2px_10px_-2px_rgba(24,21,18,0.28)] hover:bg-stone-900 hover:border-amber-700/60 transition-all duration-200 shrink-0">
            <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center group-hover:bg-amber-500/25 group-hover:text-amber-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/>
                </svg>
            </span>
            <span>Akun Staf Baru</span>
        </button>
    </div>

    <!-- Tab Role -->
    <div class="px-5 bg-stone-50/60 border-b border-stone-200 flex items-center gap-1 overflow-x-auto no-scrollbar">
        @foreach($tabs as $key => $tab)
            @php $isActive = ($activeRole === (string) $key); @endphp
            <a href="{{ route('users.index', array_filter(['role' => $key ?: null, 'cari' => request('cari')])) }}"
               class="inline-flex items-center gap-2 px-3.5 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap {{ $isActive ? 'border-amber-800 text-amber-900 font-semibold' : 'border-transparent text-stone-600 hover:text-stone-900 hover:border-stone-300' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[11px] tabular-nums {{ $isActive ? 'bg-amber-100 text-amber-900' : 'bg-stone-200/70 text-stone-600' }}">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>

    @if($users->isEmpty())
        <div class="p-12 text-center">
            <p class="text-sm font-semibold text-stone-800">Pengguna tidak ditemukan.</p>
            <p class="text-xs text-stone-500 mt-1">Ubah kata kunci atau filter role.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50/70 border-b border-stone-200 text-left text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                        <th class="px-5 py-3">Pengguna</th>
                        <th class="px-5 py-3">Telepon</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Terdaftar</th>
                        <th class="px-5 py-3 text-right">Ubah Role</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($users as $item)
                        @php
                            $currentRole = $item->roles->first()?->name;
                            $isSelf = $item->id === auth()->id();
                        @endphp
                        <tr class="hover:bg-stone-50/50 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-amber-900/10 border border-amber-800/20 text-amber-900 flex items-center justify-center text-xs font-semibold uppercase shrink-0">
                                        {{ mb_substr($item->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-stone-900 truncate">
                                            {{ $item->name }}
                                            @if($isSelf)
                                                <span class="ml-1 px-1.5 py-0.5 rounded bg-amber-100 text-amber-900 text-[10px] font-semibold align-middle">Anda</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-stone-500 truncate">{{ $item->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-stone-600 tabular-nums">{{ $item->phone ?: '—' }}</td>
                            <td class="px-5 py-3.5">
                                @if($currentRole)
                                    <span class="inline-flex items-center justify-center w-28 py-1 rounded-md text-[11px] font-semibold tracking-wide border whitespace-nowrap {{ $roleStyles[$currentRole] ?? 'bg-stone-100 text-stone-700 border-stone-300' }}">
                                        {{ $roleLabels[$currentRole] ?? ucfirst($currentRole) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-28 py-1 rounded-md text-[11px] font-semibold tracking-wide border whitespace-nowrap bg-rose-950/[0.04] text-rose-900 border-rose-800/25">Tanpa Role</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-stone-600 tabular-nums whitespace-nowrap">{{ $item->created_at?->format('d M Y') }}</td>
                            <td class="px-5 py-3.5">
                                @if($isSelf)
                                    <p class="text-right text-xs text-stone-400">Tidak dapat mengubah role sendiri</p>
                                @else
                                    <form method="POST" action="{{ route('users.role', $item) }}"
                                          onsubmit="return confirm(@js('Ubah role ' . $item->name . '?'))"
                                          class="flex items-center justify-end gap-2">
                                        @csrf @method('PATCH')
                                        <select name="role" aria-label="Role untuk {{ $item->name }}"
                                                class="rounded-lg border-stone-300 text-xs py-1.5 pl-3 pr-8 focus:border-amber-800 focus:ring-amber-800">
                                            @foreach($roleLabels as $value => $label)
                                                <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-800 hover:bg-stone-100 transition">Ubah</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-5 py-4 border-t border-stone-200">{{ $users->links() }}</div>
        @endif
    @endif
</div>

<!-- Modal: Buat Akun Staf Baru -->
<x-modal name="create-staff" :show="old('_modal') === 'create-staff'" maxWidth="lg">
    <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4">
        @csrf
        <input type="hidden" name="_modal" value="create-staff">

        <div>
            <h2 class="font-editorial text-2xl text-stone-900">Akun Staf Baru</h2>
            <p class="text-xs text-stone-500 mt-1">Buat akun untuk admin atau resepsionis hotel.</p>
        </div>

        <div>
            <label for="staff-name" class="block text-xs font-semibold text-stone-700 mb-1">Nama lengkap</label>
            <input id="staff-name" type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                   class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="staff-email" class="block text-xs font-semibold text-stone-700 mb-1">Email</label>
                <input id="staff-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255"
                       class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
            <div>
                <label for="staff-phone" class="block text-xs font-semibold text-stone-700 mb-1">Telepon <span class="font-normal text-stone-400">(opsional)</span></label>
                <input id="staff-phone" type="text" name="phone" value="{{ old('phone') }}" maxlength="20"
                       class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
        </div>

        <div>
            <label for="staff-role" class="block text-xs font-semibold text-stone-700 mb-1">Role</label>
            <select id="staff-role" name="role" required
                    class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                <option value="resepsionis" @selected(old('role', 'resepsionis') === 'resepsionis')>Resepsionis</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="staff-password" class="block text-xs font-semibold text-stone-700 mb-1">Kata sandi</label>
                <input id="staff-password" type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
            <div>
                <label for="staff-password-confirm" class="block text-xs font-semibold text-stone-700 mb-1">Ulangi kata sandi</label>
                <input id="staff-password-confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" @click="$dispatch('close-modal', 'create-staff')"
                    class="px-4 py-2 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition">Batal</button>
            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-900 transition">Buat Akun</button>
        </div>
    </form>
</x-modal>
@endsection
