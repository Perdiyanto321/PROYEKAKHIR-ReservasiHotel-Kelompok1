@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Ringkasan Operasional')

@section('content')
@if($isGuest)
    <!-- Editorial Hero Banner untuk Tamu -->
    <div class="bg-[#181512] text-stone-100 rounded-2xl p-7 sm:p-8 mb-7 border border-stone-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div class="max-w-xl">
            <span class="inline-block text-[11px] uppercase tracking-[0.2em] text-amber-400 font-semibold">Svara Guest Sanctuary</span>
            <h2 class="font-editorial text-3xl sm:text-4xl text-amber-50 mt-2 leading-tight">
                Selamat datang kembali, {{ auth()->user()->name }}.
            </h2>
            <p class="text-sm text-stone-300 mt-2 leading-relaxed">
                Nikmati ketenangan menginap dengan standar pelayanan personal. Pantau jadwal reservasi Anda atau jelajahi pilihan suite terbaru kami.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <a href="{{ route('explore') }}" class="px-4 py-2.5 rounded-lg border border-stone-700 text-stone-200 text-sm font-medium hover:bg-stone-800 transition">
                Jelajahi Katalog Kamar
            </a>
            <a href="{{ route('reservations.create') }}" class="px-5 py-2.5 rounded-lg bg-amber-800 text-white text-sm font-medium hover:bg-amber-700 transition">
                Pesan Kamar Sekarang
            </a>
        </div>
    </div>

    <!-- Riwayat Reservasi Tamu -->
    <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-stone-900">Reservasi Terakhir Anda</h3>
                <p class="text-xs text-stone-500 mt-0.5">Klik detail untuk melihat kode booking atau membatalkan jadwal yang belum check-in.</p>
            </div>
            <a href="{{ route('reservations.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 bg-stone-50/70 text-xs font-semibold text-stone-700 hover:bg-stone-100 hover:border-stone-300 hover:text-stone-900 transition whitespace-nowrap">
                <span>Lihat Semua</span>
                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50/80 border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
                    <tr>
                        <th class="px-6 py-3.5">Kode Booking</th>
                        <th class="px-6 py-3.5">Unit Kamar</th>
                        <th class="px-6 py-3.5">Jadwal Menginap</th>
                        <th class="px-6 py-3.5">Total Biaya</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($myReservations as $rsv)
                        <tr class="hover:bg-stone-50/60 transition">
                            <td class="px-6 py-4 font-semibold text-stone-900 tabular-nums whitespace-nowrap">{{ $rsv->booking_code }}</td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-stone-900">Kamar {{ $rsv->room->room_number }}</span>
                                <span class="block text-xs text-stone-500">{{ $rsv->room->roomType->name }}</span>
                            </td>
                            <td class="px-6 py-4 tabular-nums text-stone-600 whitespace-nowrap">
                                {{ $rsv->check_in_date->format('d M Y') }} – {{ $rsv->check_out_date->format('d M Y') }}
                                <span class="block text-xs text-stone-400">{{ $rsv->total_nights }} malam</span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-stone-900 tabular-nums whitespace-nowrap">Rp {{ number_format($rsv->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$rsv->status" /></td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('reservations.show', $rsv) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-stone-200 text-xs font-medium text-stone-700 hover:bg-stone-100 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center">
                                <p class="text-sm font-medium text-stone-700">Anda belum memiliki riwayat reservasi.</p>
                                <p class="text-xs text-stone-500 mt-1">Mulai rencanakan pengalaman menginap Anda bersama Svara Hotel.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    @php
        $maintenanceRooms = max(0, $totalRooms - $availableRooms - $occupiedRooms);
        $availPct = $totalRooms > 0 ? round(($availableRooms / $totalRooms) * 100) : 0;
        $occPct = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
        $maintPct = $totalRooms > 0 ? max(0, 100 - $availPct - $occPct) : 0;

        // Geometri Lingkaran SVG (radius = 46, keliling = 2 * pi * 46 = 289.03)
        $circumference = 289.03;
        $availArc = $totalRooms > 0 ? ($availableRooms / $totalRooms) * $circumference : 0;
        $occArc   = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * $circumference : 0;
        $maintArc = $totalRooms > 0 ? ($maintenanceRooms / $totalRooms) * $circumference : 0;

        $roomsSnapshot = \App\Models\Room::with('roomType')
            ->orderBy('room_number')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'number' => $r->room_number,
                'type' => $r->roomType->name,
                'floor' => $r->floor,
                'status' => $r->status,
                'url' => route('rooms.show', $r),
            ]);
    @endphp

    <!-- KPI Strip Admin & Resepsionis -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Tingkat Okupansi" value="{{ $occupancyRate }}%" subtitle="{{ $occupiedRooms }} dari {{ $totalRooms }} unit terisi">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M18 20V10M12 20V4M6 20v-6"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Kamar Tersedia" value="{{ $availableRooms }} Unit" subtitle="Siap menerima tamu hari ini">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Check-In Hari Ini" value="{{ $todayCheckIns }} Tamu" subtitle="Jadwal kedatangan aktif">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>

        <x-stat-card title="Total Pendapatan" value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}" subtitle="Reservasi terkonfirmasi & selesai">
            <x-slot:icon>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </x-slot:icon>
        </x-stat-card>
    </div>

    <!-- Diagram Donat Interaktif (Click-Only) -->
    <div x-data="{
            selected: 'all',
            rooms: @js($roomsSnapshot),
            stats: {
                all: { label: 'Total Unit', count: {{ $totalRooms }}, pct: '100%', color: 'text-stone-900' },
                tersedia: { label: 'Tersedia', count: {{ $availableRooms }}, pct: '{{ $availPct }}%', color: 'text-emerald-700' },
                terisi: { label: 'Terisi', count: {{ $occupiedRooms }}, pct: '{{ $occPct }}%', color: 'text-sky-700' },
                maintenance: { label: 'Maintenance', count: {{ $maintenanceRooms }}, pct: '{{ $maintPct }}%', color: 'text-rose-700' }
            },
            get activeStat() {
                return this.stats[this.selected];
            },
            get filteredRooms() {
                if (this.selected === 'all') return this.rooms;
                return this.rooms.filter(r => r.status === this.selected);
            },
            toggleSelect(key) {
                this.selected = (this.selected === key) ? 'all' : key;
            }
         }"
         class="bg-white border border-stone-200 rounded-xl p-6 mb-6">

        <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-stone-100">
            <div>
                <h3 class="text-base font-semibold text-stone-900">Diagram Komposisi Status Kamar</h3>
                <p class="text-xs text-stone-500 mt-0.5">Klik segmen grafik atau kartu status untuk menyaring daftar nomor kamar</p>
            </div>
            <div class="flex items-center gap-2.5">
                <button x-show="selected !== 'all'"
                        @click="selected = 'all'"
                        style="display: none;"
                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-xs font-semibold text-amber-900 hover:bg-amber-100 transition">
                    <span>Reset Filter</span>
                </button>
                <a href="{{ route('rooms.index') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 bg-stone-50/70 text-xs font-semibold text-stone-700 hover:bg-stone-100 hover:border-stone-300 hover:text-stone-900 transition whitespace-nowrap">
                    <span>Kelola Unit Kamar</span>
                    <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch pt-5">
            <!-- Kiri: Grafik SVG Donut (4 Kolom) -->
            <div class="lg:col-span-4 min-h-[244px] flex flex-col items-center justify-center">
                <div class="relative w-48 h-48 select-none">
                    <svg viewBox="0 0 120 120" class="w-full h-full overflow-visible">
                        <!-- Track Lingkaran Dasar -->
                        <circle cx="60" cy="60" r="46" fill="none" stroke="#F5F5F4" stroke-width="14" />

                        @if($totalRooms > 0)
                            <g transform="rotate(-90 60 60)">
                                @if($availArc > 0)
                                    <circle cx="60" cy="60" r="46" fill="none"
                                            stroke="#15803D"
                                            stroke-dasharray="{{ $availArc }} {{ $circumference }}"
                                            stroke-dashoffset="0"
                                            :stroke-width="selected === 'tersedia' ? 18 : 14"
                                            :opacity="selected !== 'all' && selected !== 'tersedia' ? 0.25 : 1"
                                            @click="toggleSelect('tersedia')"
                                            class="cursor-pointer transition-all duration-300 ease-out" />
                                @endif

                                @if($occArc > 0)
                                    <circle cx="60" cy="60" r="46" fill="none"
                                            stroke="#0369A1"
                                            stroke-dasharray="{{ $occArc }} {{ $circumference }}"
                                            stroke-dashoffset="-{{ $availArc }}"
                                            :stroke-width="selected === 'terisi' ? 18 : 14"
                                            :opacity="selected !== 'all' && selected !== 'terisi' ? 0.25 : 1"
                                            @click="toggleSelect('terisi')"
                                            class="cursor-pointer transition-all duration-300 ease-out" />
                                @endif

                                @if($maintArc > 0)
                                    <circle cx="60" cy="60" r="46" fill="none"
                                            stroke="#BE123C"
                                            stroke-dasharray="{{ $maintArc }} {{ $circumference }}"
                                            stroke-dashoffset="-{{ $availArc + $occArc }}"
                                            :stroke-width="selected === 'maintenance' ? 18 : 14"
                                            :opacity="selected !== 'all' && selected !== 'maintenance' ? 0.25 : 1"
                                            @click="toggleSelect('maintenance')"
                                            class="cursor-pointer transition-all duration-300 ease-out" />
                                @endif
                            </g>
                        @endif
                    </svg>

                    <!-- Informasi Pusat Grafik (Klik untuk reset ke Total) -->
                    <button type="button"
                            @click="selected = 'all'"
                            title="Klik untuk tampilkan semua unit"
                            class="absolute inset-6 rounded-full flex flex-col items-center justify-center text-center focus:outline-none">
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-stone-400" x-text="activeStat.label"></span>
                        <span class="text-2xl font-bold tabular-nums mt-0.5 transition-colors duration-200" :class="activeStat.color" x-text="activeStat.count + ' Unit'"></span>
                        <span class="text-xs font-medium text-stone-500 tabular-nums" x-text="activeStat.pct"></span>
                    </button>
                </div>
            </div>

            <!-- Tengah: Legenda Klik Interaktif (4 Kolom) -->
            <div class="lg:col-span-4 min-h-[244px] flex flex-col justify-between gap-2.5">
                <button type="button"
                        @click="toggleSelect('tersedia')"
                        :class="selected === 'tersedia' ? 'border-emerald-400 bg-emerald-50/70 ring-1 ring-emerald-400/30' : 'border-stone-200 bg-stone-50/40 hover:bg-stone-50 hover:border-stone-300'"
                        class="w-full flex-1 flex items-center justify-between px-4 py-3 rounded-xl border text-left transition-all duration-200 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-sm bg-emerald-700 shrink-0"></span>
                        <div>
                            <p class="text-sm font-semibold text-stone-900">Tersedia</p>
                            <p class="text-[11px] text-stone-500">Siap untuk check-in tamu</p>
                        </div>
                    </div>
                    <div class="text-right tabular-nums">
                        <p class="text-sm font-bold text-stone-900">{{ $availableRooms }} Unit</p>
                        <p class="text-[11px] text-emerald-700 font-semibold">{{ $availPct }}%</p>
                    </div>
                </button>

                <button type="button"
                        @click="toggleSelect('terisi')"
                        :class="selected === 'terisi' ? 'border-sky-400 bg-sky-50/70 ring-1 ring-sky-400/30' : 'border-stone-200 bg-stone-50/40 hover:bg-stone-50 hover:border-stone-300'"
                        class="w-full flex-1 flex items-center justify-between px-4 py-3 rounded-xl border text-left transition-all duration-200 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-sm bg-sky-700 shrink-0"></span>
                        <div>
                            <p class="text-sm font-semibold text-stone-900">Terisi</p>
                            <p class="text-[11px] text-stone-500">Sedang dihuni tamu aktif</p>
                        </div>
                    </div>
                    <div class="text-right tabular-nums">
                        <p class="text-sm font-bold text-stone-900">{{ $occupiedRooms }} Unit</p>
                        <p class="text-[11px] text-sky-700 font-semibold">{{ $occPct }}%</p>
                    </div>
                </button>

                <button type="button"
                        @click="toggleSelect('maintenance')"
                        :class="selected === 'maintenance' ? 'border-rose-400 bg-rose-50/70 ring-1 ring-rose-400/30' : 'border-stone-200 bg-stone-50/40 hover:bg-stone-50 hover:border-stone-300'"
                        class="w-full flex-1 flex items-center justify-between px-4 py-3 rounded-xl border text-left transition-all duration-200 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-sm bg-rose-700 shrink-0"></span>
                        <div>
                            <p class="text-sm font-semibold text-stone-900">Maintenance</p>
                            <p class="text-[11px] text-stone-500">Perbaikan / pembersihan</p>
                        </div>
                    </div>
                    <div class="text-right tabular-nums">
                        <p class="text-sm font-bold text-stone-900">{{ $maintenanceRooms }} Unit</p>
                        <p class="text-[11px] text-rose-700 font-semibold">{{ $maintPct }}%</p>
                    </div>
                </button>
            </div>

            <!-- Kanan: Pratinjau Unit Kamar (4 Kolom, Tinggi Terkunci) -->
            <div class="lg:col-span-4 h-[244px] bg-stone-50/80 border border-stone-200/80 rounded-xl p-4 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-2.5 shrink-0">
                    <span class="text-xs font-semibold uppercase tracking-wider text-stone-500">
                        Daftar Unit (<span x-text="activeStat.label"></span>)
                    </span>
                    <span class="text-xs font-medium text-stone-600 tabular-nums" x-text="filteredRooms.length + ' unit'"></span>
                </div>

                <div class="grid grid-cols-2 gap-2 content-start flex-1 overflow-y-auto no-scrollbar">
                    <template x-for="room in filteredRooms" :key="room.id">
                        <a :href="room.url"
                           class="p-2.5 rounded-lg bg-white border border-stone-200 hover:border-amber-700 transition-colors duration-150 flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-stone-900 tabular-nums" x-text="'Kamar ' + room.number"></p>
                                <p class="text-[10px] text-stone-500 truncate" x-text="room.type"></p>
                            </div>
                            <span class="w-2 h-2 rounded-full shrink-0"
                                  :class="{
                                      'bg-emerald-600': room.status === 'tersedia',
                                      'bg-sky-600': room.status === 'terisi',
                                      'bg-rose-600': room.status === 'maintenance'
                                  }"></span>
                        </a>
                    </template>
                </div>

                <p class="text-[11px] text-stone-400 mt-2.5 pt-2 border-t border-stone-200/70 shrink-0">
                    Klik kartu nomor kamar untuk membuka detail unit.
                </p>
            </div>
        </div>
    </div>

    <!-- Tabel Aktivitas Reservasi Terbaru -->
    <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-stone-900">Aktivitas Reservasi Terbaru</h3>
                <p class="text-xs text-stone-500 mt-0.5">Daftar transaksi pemesanan masuk dan status tamu menginap</p>
            </div>
            <a href="{{ route('reservations.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-200 bg-stone-50/70 text-xs font-semibold text-stone-700 hover:bg-stone-100 hover:border-stone-300 hover:text-stone-900 transition whitespace-nowrap">
                <span>Kelola Semua</span>
                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50/80 border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
                    <tr>
                        <th class="px-6 py-3.5">Kode</th>
                        <th class="px-6 py-3.5">Nama Tamu</th>
                        <th class="px-6 py-3.5">Kamar</th>
                        <th class="px-6 py-3.5">Jadwal Inap</th>
                        <th class="px-6 py-3.5">Total</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($recentReservations as $rsv)
                        <tr class="hover:bg-stone-50/60 transition">
                            <td class="px-6 py-4 font-semibold text-stone-900 tabular-nums whitespace-nowrap">{{ $rsv->booking_code }}</td>
                            <td class="px-6 py-4 font-medium text-stone-800 whitespace-nowrap">{{ $rsv->user->name }}</td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-stone-900 whitespace-nowrap">No. {{ $rsv->room->room_number }}</span>
                                <span class="text-xs text-stone-500 block">{{ $rsv->room->roomType->name }}</span>
                            </td>
                            <td class="px-6 py-4 tabular-nums text-stone-600 whitespace-nowrap">
                                {{ $rsv->check_in_date->format('d/m/Y') }} – {{ $rsv->check_out_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-stone-900 tabular-nums whitespace-nowrap">Rp {{ number_format($rsv->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$rsv->status" /></td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('reservations.show', $rsv) }}" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-stone-200 text-xs font-medium text-stone-700 hover:bg-stone-100 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-stone-500">Belum ada data reservasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection