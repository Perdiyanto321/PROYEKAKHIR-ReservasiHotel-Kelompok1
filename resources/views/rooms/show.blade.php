@extends('layouts.app')
@section('title', 'Detail Kamar ' . $room->room_number)
@section('header', 'Detail Unit Kamar ' . $room->room_number)

@section('content')
@php
    $roomType = $room->roomType;
@endphp

<!-- Navigasi Kembali -->
<div class="mb-5 flex items-center justify-between gap-4">
    <a href="{{ route('rooms.index') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Daftar Unit Kamar</span>
    </a>

    @can('kelola-master-kamar')
        <a href="{{ route('rooms.edit', $room) }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-800 hover:bg-stone-100 transition">
            <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <span>Edit Konfigurasi Unit</span>
        </a>
    @endcan
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- KOLOM KIRI: Status Operasional & Riwayat Hunian (7 Kolom) -->
    <div class="lg:col-span-7 space-y-6">
        <!-- Kartu Identitas & Kontrol Status Unit -->
        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden">
            <div class="p-6 sm:p-7 border-b border-stone-200 flex flex-wrap items-start justify-between gap-4 bg-stone-50/40">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Nomor Unit Kamar</p>
                    <h2 class="font-mono text-3xl font-bold tracking-tight text-stone-900 mt-1">
                        Kamar {{ $room->room_number }}
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        Lantai {{ $room->floor }} &bull; Kategori <span class="font-semibold text-stone-800">{{ $roomType->name }}</span>
                    </p>
                </div>

                <x-status-badge :status="$room->status" class="px-3 py-1.5 text-xs" />
            </div>

            <div class="p-6 sm:p-7 space-y-5">
                <!-- Panel Status Tamu Aktif / Mendatang -->
                @if($activeReservation)
                    <div class="p-4 rounded-xl bg-amber-900/[0.05] border border-amber-800/25 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-amber-900">Tamu Sedang Menginap</span>
                            <p class="text-sm font-bold text-stone-900 mt-0.5">{{ $activeReservation->user->name }}</p>
                            <p class="text-xs text-stone-600 tabular-nums mt-0.5">
                                {{ $activeReservation->check_in_date->format('d M Y') }} – {{ $activeReservation->check_out_date->format('d M Y') }}
                                ({{ $activeReservation->booking_code }})
                            </p>
                        </div>
                        <a href="{{ route('reservations.show', $activeReservation) }}"
                           class="px-3.5 py-2 rounded-lg bg-amber-800 text-white text-xs font-semibold hover:bg-amber-900 transition">
                            Buka Folio Reservasi
                        </a>
                    </div>
                @elseif($upcomingReservation)
                    <div class="p-4 rounded-xl bg-stone-50 border border-stone-200 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-stone-400">Reservasi Terkonfirmasi Berikutnya</span>
                            <p class="text-sm font-semibold text-stone-900 mt-0.5">{{ $upcomingReservation->user->name }}</p>
                            <p class="text-xs text-stone-500 tabular-nums mt-0.5">
                                Check-in {{ $upcomingReservation->check_in_date->format('d M Y') }} ({{ $upcomingReservation->booking_code }})
                            </p>
                        </div>
                        <a href="{{ route('reservations.show', $upcomingReservation) }}"
                           class="px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition">
                            Lihat Detail
                        </a>
                    </div>
                @endif

                @if(! empty($room->notes))
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-1.5">Catatan Unit</p>
                        <p class="text-xs text-stone-700 bg-stone-50 border border-stone-200/80 rounded-xl p-3.5 leading-relaxed">{{ $room->notes }}</p>
                    </div>
                @endif

                <!-- Kontrol Status Housekeeping -->
                @if($room->status !== 'terisi')
                    <div class="pt-4 border-t border-stone-100">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-2.5">Ubah Status Kesiapan Kamar</p>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <form action="{{ route('rooms.update', $room) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                <input type="hidden" name="floor" value="{{ $room->floor }}">
                                <input type="hidden" name="status" value="tersedia">
                                <input type="hidden" name="quick_status" value="1">
                                <button type="submit"
                                        @disabled($room->status === 'tersedia')
                                        class="px-4 py-2 rounded-lg text-xs font-semibold transition {{ $room->status === 'tersedia' ? 'bg-emerald-950/[0.06] text-emerald-900 border border-emerald-800/30 cursor-default' : 'bg-emerald-800 text-white hover:bg-emerald-900' }}">
                                    Tandai Tersedia
                                </button>
                            </form>

                            <form action="{{ route('rooms.update', $room) }}" method="POST">
                                @csrf @method('PUT')
                                <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                <input type="hidden" name="floor" value="{{ $room->floor }}">
                                <input type="hidden" name="status" value="maintenance">
                                <input type="hidden" name="quick_status" value="1">
                                <button type="submit"
                                        @disabled($room->status === 'maintenance')
                                        class="px-4 py-2 rounded-lg text-xs font-semibold transition {{ $room->status === 'maintenance' ? 'bg-rose-950/[0.06] text-rose-900 border border-rose-800/30 cursor-default' : 'border border-rose-200 bg-white text-rose-800 hover:bg-rose-50' }}">
                                    Tandai Maintenance
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            @can('kelola-master-kamar')
                <div class="px-6 sm:px-7 py-4 bg-stone-50/70 border-t border-stone-200 flex items-center justify-between">
                    <span class="text-xs text-stone-400">Hapus unit jika sudah tidak beroperasi</span>
                    <form action="{{ route('rooms.destroy', $room) }}" method="POST"
                          onsubmit="return confirm('Hapus permanen unit Kamar {{ $room->room_number }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-lg border border-rose-200 bg-white text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                            Hapus Unit
                        </button>
                    </form>
                </div>
            @endcan
        </div>

        <!-- Riwayat Reservasi pada Kamar Ini -->
        <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-200">
                <h3 class="text-sm font-semibold text-stone-900">Riwayat Reservasi Unit Ini</h3>
                <p class="text-xs text-stone-500 mt-0.5">12 transaksi terakhir pada Kamar {{ $room->room_number }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-stone-50/80 border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Tamu</th>
                            <th class="px-5 py-3">Jadwal</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @forelse($room->reservations as $rsv)
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="px-5 py-3.5 font-mono text-xs font-semibold text-stone-800 whitespace-nowrap">{{ $rsv->booking_code }}</td>
                                <td class="px-5 py-3.5 font-medium text-stone-900 whitespace-nowrap">{{ $rsv->user->name }}</td>
                                <td class="px-5 py-3.5 text-xs text-stone-600 tabular-nums whitespace-nowrap">
                                    {{ $rsv->check_in_date->format('d/m/Y') }} – {{ $rsv->check_out_date->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap"><x-status-badge :status="$rsv->status" /></td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('reservations.show', $rsv) }}" class="text-xs font-semibold text-amber-800 hover:underline">
                                        Folio &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-xs text-stone-400">
                                    Belum ada riwayat transaksi pada unit kamar ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: Kartu Visual Tipe Kamar (5 Kolom) -->
    <div class="lg:col-span-5 bg-white border border-stone-200 rounded-2xl overflow-hidden lg:sticky lg:top-24">
        <div class="relative aspect-[16/10] bg-stone-200 overflow-hidden">
            <img src="{{ $roomType->cover_url }}"
                 alt="{{ $roomType->name }}"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-950/15 to-transparent"></div>

            <div class="absolute bottom-3.5 left-4 right-4 flex items-end justify-between text-white">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-stone-300">Spesifikasi Kategori</p>
                    <h3 class="font-editorial text-2xl text-amber-50 leading-tight mt-0.5">{{ $roomType->name }}</h3>
                </div>
                <div class="text-right tabular-nums">
                    <p class="text-sm font-bold text-white">Rp {{ number_format($roomType->base_price, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-stone-300">/ malam</p>
                </div>
            </div>
        </div>

        <div class="p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between text-xs pb-3.5 border-b border-stone-100">
                <span class="text-stone-500">Kapasitas Maksimal</span>
                <span class="font-semibold text-stone-900">{{ $roomType->capacity }} Tamu</span>
            </div>

            @if($roomType->description)
                <p class="text-xs text-stone-600 leading-relaxed">{{ $roomType->description }}</p>
            @endif

            @if($roomType->facilities->isNotEmpty())
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-2">Fasilitas Kamar</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($roomType->facilities as $facility)
                            <span class="px-2.5 py-1 rounded-md bg-stone-100 border border-stone-200/80 text-[11px] font-medium text-stone-700">
                                {{ $facility->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection