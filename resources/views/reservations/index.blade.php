@extends('layouts.app')
@section('title', 'Daftar Reservasi')
@section('header', $isGuest ? 'Riwayat Reservasi Saya' : 'Manajemen Reservasi Tamu')

@section('content')
<!-- 1. Ringkasan Aktivitas Harian -->
@if($isGuest)
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <x-stat-card title="Total Pemesanan" value="{{ $statusCounts['all'] }} Reservasi" subtitle="Seluruh riwayat transaksi Anda">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Jadwal Aktif" value="{{ $statusCounts['pending'] + $statusCounts['confirmed'] + $statusCounts['checked_in'] }} Jadwal" subtitle="Menunggu kedatangan atau in-house">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Kunjungan Selesai" value="{{ $statusCounts['checked_out'] }} Kunjungan" subtitle="Terima kasih telah menginap di Svara">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('reservations.index', ['status' => 'pending']) }}" class="block group">
            <div class="h-full bg-amber-50/25 border {{ request('status') === 'pending' ? 'border-amber-500 ring-1 ring-amber-400/40' : 'border-amber-300/80 group-hover:border-amber-400' }} shadow-[0_2px_10px_-3px_rgba(146,64,14,0.08)] rounded-xl p-5 flex flex-col justify-between transition">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Perlu Konfirmasi</p>
                    <div class="w-8 h-8 rounded-lg bg-amber-100/90 text-amber-800 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-bold text-stone-900 tracking-tight tabular-nums">{{ $pulse['pending'] }} Booking</p>
                    <p class="mt-1 text-xs text-stone-500">Klik untuk saring status pending</p>
                </div>
            </div>
        </a>

        <x-stat-card title="Check-In Hari Ini" value="{{ $pulse['today_check_in'] }} Tamu" subtitle="Jadwal kedatangan {{ now()->format('d M') }}">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <a href="{{ route('reservations.index', ['status' => 'checked_in']) }}" class="block group">
            <div class="h-full bg-amber-50/25 border {{ request('status') === 'checked_in' ? 'border-amber-500 ring-1 ring-amber-400/40' : 'border-amber-300/80 group-hover:border-amber-400' }} shadow-[0_2px_10px_-3px_rgba(146,64,14,0.08)] rounded-xl p-5 flex flex-col justify-between transition">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">Tamu In-House</p>
                    <div class="w-8 h-8 rounded-lg bg-amber-100/90 text-amber-800 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                </div>
                <div class="mt-3">
                    <p class="text-2xl font-bold text-stone-900 tracking-tight tabular-nums">{{ $pulse['in_house'] }} Kamar</p>
                    <p class="mt-1 text-xs text-stone-500">Sedang menginap saat ini</p>
                </div>
            </div>
        </a>

        <x-stat-card title="Check-Out Hari Ini" value="{{ $pulse['today_check_out'] }} Tamu" subtitle="Jadwal kepulangan {{ now()->format('d M') }}">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>
    </div>
@endif

<!-- 2. Container Utama: Pencarian, Status Tabs, & Tabel Presisi -->
<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <!-- Toolbar Pencarian & CTA -->
    <div class="p-5 border-b border-stone-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" action="{{ route('reservations.index') }}" class="flex items-center gap-2 w-full md:max-w-md">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text"
                       name="cari"
                       value="{{ request('cari') }}"
                       placeholder="Cari kode booking, nama tamu, atau no. kamar..."
                       class="w-full pl-9 pr-3 py-2 rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg border border-stone-300 bg-stone-50 text-sm font-medium text-stone-700 hover:bg-stone-100 transition shrink-0">
                Cari
            </button>
            @if(request('cari') || request('status'))
                <a href="{{ route('reservations.index') }}" class="px-2.5 py-2 text-xs font-medium text-stone-500 hover:text-stone-900 shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('reservations.create') }}"
           class="group inline-flex items-center justify-between sm:justify-center gap-3 pl-2 pr-4 py-1.5 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide shadow-[0_2px_10px_-2px_rgba(24,21,18,0.28)] hover:bg-stone-900 hover:border-amber-700/60 transition-all duration-200 shrink-0">
            <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center group-hover:bg-amber-500/25 group-hover:text-amber-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/>
                </svg>
            </span>
            <span>{{ $isGuest ? 'Pesan Kamar Baru' : 'Reservasi Baru' }}</span>
            <svg class="w-3.5 h-3.5 text-stone-400 group-hover:text-amber-300 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- Barisan Tab Status dengan Counter -->
    @php
        $tabs = [
            '' => ['label' => 'Semua', 'count' => $statusCounts['all']],
            'pending' => ['label' => 'Pending', 'count' => $statusCounts['pending']],
            'confirmed' => ['label' => 'Confirmed', 'count' => $statusCounts['confirmed']],
            'checked_in' => ['label' => 'Checked-In', 'count' => $statusCounts['checked_in']],
            'checked_out' => ['label' => 'Checked-Out', 'count' => $statusCounts['checked_out']],
            'cancelled' => ['label' => 'Dibatalkan', 'count' => $statusCounts['cancelled']],
        ];
        $activeStatus = (string) request('status', '');
    @endphp
    <div class="px-5 bg-stone-50/60 border-b border-stone-200 flex items-center gap-1 overflow-x-auto no-scrollbar">
        @foreach($tabs as $key => $tab)
            @php $isActive = ($activeStatus === $key); @endphp
            <a href="{{ route('reservations.index', array_filter(['status' => $key ?: null, 'cari' => request('cari')])) }}"
               class="inline-flex items-center gap-2 px-3.5 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap {{ $isActive ? 'border-amber-800 text-amber-900 font-semibold' : 'border-transparent text-stone-600 hover:text-stone-900 hover:border-stone-300' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[11px] tabular-nums {{ $isActive ? 'bg-amber-100 text-amber-900' : 'bg-stone-200/70 text-stone-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Tabel Reservasi Seimbang -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-stone-50/80 border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3.5 whitespace-nowrap">Kode Booking</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Tamu</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Kamar</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Jadwal Inap</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Total</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">Status</th>
                    <th class="px-4 py-3.5 whitespace-nowrap">
                        <div class="{{ $isGuest ? 'w-20' : 'w-44' }} ml-auto text-center">Aksi</div>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse($reservations as $rsv)
                    <tr class="hover:bg-stone-50/70 transition">
                        <!-- Kode Booking -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono text-xs font-semibold px-2 py-1 rounded bg-stone-100 text-stone-800 border border-stone-200/80">
                                    {{ $rsv->booking_code }}
                                </span>
                                @if($rsv->special_requests)
                                    <span title="Permintaan khusus: {{ $rsv->special_requests }}"
                                          class="w-5 h-5 rounded bg-amber-50 border border-amber-200 text-amber-800 inline-flex items-center justify-center cursor-help">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 8l4-4h6a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h2v4z"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Tamu -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-stone-100 border border-stone-200 text-stone-700 flex items-center justify-center text-[11px] font-semibold uppercase shrink-0">
                                    {{ substr($rsv->user->name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-stone-900 leading-tight">{{ $rsv->user->name }}</p>
                                    <p class="text-[11px] text-stone-500 mt-0.5">{{ $rsv->user->email }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Kamar -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <p class="font-semibold text-stone-900">Kamar {{ $rsv->room->room_number }}</p>
                            <p class="text-xs text-stone-500">{{ $rsv->room->roomType->name }} &bull; Lt. {{ $rsv->room->floor }}</p>
                        </td>

                        <!-- Jadwal -->
                        <td class="px-4 py-3.5 tabular-nums whitespace-nowrap">
                            <p class="text-stone-800 font-medium">{{ $rsv->check_in_date->format('d M Y') }} – {{ $rsv->check_out_date->format('d M Y') }}</p>
                            <p class="text-xs text-stone-500 mt-0.5">{{ $rsv->total_nights }} malam</p>
                        </td>

                        <!-- Total -->
                        <td class="px-4 py-3.5 font-semibold text-stone-900 tabular-nums whitespace-nowrap">
                            Rp {{ number_format($rsv->total_price, 0, ',', '.') }}
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <x-status-badge :status="$rsv->status" />
                        </td>

                        <!-- Aksi Seimbang (Lebar Identik dengan Header) -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="{{ $isGuest ? 'w-20' : 'w-44' }} ml-auto flex items-center justify-end gap-2">
                                @can('update', $rsv)
                                    @if($rsv->status === 'pending')
                                        <form action="{{ route('reservations.status', $rsv) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="w-24 py-1.5 rounded-lg bg-emerald-800 text-white text-xs font-medium hover:bg-emerald-900 transition text-center">
                                                Konfirmasi
                                            </button>
                                        </form>
                                    @elseif($rsv->status === 'confirmed')
                                        <form action="{{ route('reservations.status', $rsv) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="checked_in">
                                            <button type="submit" class="w-24 py-1.5 rounded-lg bg-amber-800 text-white text-xs font-medium hover:bg-amber-900 transition text-center">
                                                Check-In
                                            </button>
                                        </form>
                                    @elseif($rsv->status === 'checked_in')
                                        <form action="{{ route('reservations.status', $rsv) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="checked_out">
                                            <button type="submit" class="w-24 py-1.5 rounded-lg bg-stone-800 text-white text-xs font-medium hover:bg-stone-900 transition text-center">
                                                Check-Out
                                            </button>
                                        </form>
                                    @else
                                        <span class="w-24 py-1.5 text-center text-[11px] font-medium text-stone-400 select-none">
                                            {{ $rsv->status === 'checked_out' ? 'Selesai' : 'Dibatalkan' }}
                                        </span>
                                    @endif
                                @endcan

                                <a href="{{ route('reservations.show', $rsv) }}"
                                   class="w-[72px] py-1.5 rounded-lg border border-stone-200 bg-white text-xs font-medium text-stone-700 hover:bg-stone-100 hover:border-stone-300 transition text-center">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <p class="text-sm font-medium text-stone-700">Tidak ada data reservasi yang sesuai filter.</p>
                            <p class="text-xs text-stone-500 mt-1">Coba pilih tab status lain atau buat reservasi baru.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $reservations->links() }}</div>
@endsection