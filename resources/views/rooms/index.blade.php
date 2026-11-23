@extends('layouts.app')
@section('title', 'Unit & Status Kamar')
@section('header', 'Unit & Status Kamar')

@section('content')
@php
    $occupancyPct = $statusCounts['all'] > 0
        ? round(($statusCounts['terisi'] / $statusCounts['all']) * 100)
        : 0;
@endphp

<!-- 1. KPI Strip Unit Kamar -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Unit Kamar" value="{{ $statusCounts['all'] }} Unit" subtitle="Tersebar di {{ $floors->count() }} lantai">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 16h20"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Siap Huni (Tersedia)" value="{{ $statusCounts['tersedia'] }} Unit" subtitle="Bersih & siap untuk check-in">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Sedang Terisi" value="{{ $statusCounts['terisi'] }} Unit" subtitle="Okupansi fisik saat ini {{ $occupancyPct }}%">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Dalam Maintenance" value="{{ $statusCounts['maintenance'] }} Unit" subtitle="Perbaikan atau pembersihan">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>
</div>

<!-- 2. Filter Bar, Tab Status, & Mode Tampilan -->
<div x-data="{
        viewMode: localStorage.getItem('svara_rooms_view') || 'matrix',
        setView(mode) {
            this.viewMode = mode;
            localStorage.setItem('svara_rooms_view', mode);
        }
     }"
     class="space-y-6">

    <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <!-- Toolbar Atas -->
        <div class="p-5 border-b border-stone-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <form method="GET" action="{{ route('rooms.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="relative w-full sm:w-48">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="text"
                           name="cari"
                           value="{{ request('cari') }}"
                           placeholder="No. kamar..."
                           class="w-full pl-9 pr-3 py-2 rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                </div>

                <select name="room_type_id" class="rounded-lg border-stone-300 text-sm py-2 pr-8 focus:border-amber-800 focus:ring-amber-800">
                    <option value="">Semua Tipe Kamar</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) request('room_type_id') === (string) $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>

                <select name="floor" class="rounded-lg border-stone-300 text-sm py-2 pr-8 focus:border-amber-800 focus:ring-amber-800">
                    <option value="">Semua Lantai</option>
                    @foreach($floors as $floor)
                        <option value="{{ $floor }}" @selected((string) request('floor') === (string) $floor)>
                            Lantai {{ $floor }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 rounded-lg border border-stone-300 bg-stone-50 text-sm font-medium text-stone-700 hover:bg-stone-100 transition">
                    Terapkan
                </button>

                @if(request()->hasAny(['cari', 'status', 'room_type_id', 'floor']))
                    <a href="{{ route('rooms.index') }}" class="px-2.5 py-2 text-xs font-medium text-stone-500 hover:text-stone-900">
                        Reset
                    </a>
                @endif
            </form>

            <!-- Switcher Mode Tampilan + Tombol Tambah Unit -->
            <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                <div class="inline-flex rounded-lg border border-stone-200 bg-stone-100/80 p-1">
                    <button type="button"
                            @click="setView('matrix')"
                            :class="viewMode === 'matrix' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500 hover:text-stone-800'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>
                        </svg>
                        <span>Matriks</span>
                    </button>
                    <button type="button"
                            @click="setView('table')"
                            :class="viewMode === 'table' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500 hover:text-stone-800'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                        </svg>
                        <span>Tabel</span>
                    </button>
                </div>

                @can('kelola-master-kamar')
                    <a href="{{ route('rooms.create') }}"
                       class="group inline-flex items-center justify-between sm:justify-center gap-3 pl-2 pr-4 py-1.5 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide shadow-[0_2px_10px_-2px_rgba(24,21,18,0.28)] hover:bg-stone-900 hover:border-amber-700/60 transition-all duration-200 shrink-0">
                        <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center group-hover:bg-amber-500/25 group-hover:text-amber-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 16h20"/>
                            </svg>
                        </span>
                        <span>Unit Kamar Baru</span>
                        <svg class="w-3.5 h-3.5 text-stone-400 group-hover:text-amber-300 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @endcan
            </div>
        </div>

        <!-- Barisan Tab Status -->
        @php
            $tabs = [
                '' => ['label' => 'Semua Unit', 'count' => $statusCounts['all']],
                'tersedia' => ['label' => 'Tersedia', 'count' => $statusCounts['tersedia']],
                'terisi' => ['label' => 'Terisi', 'count' => $statusCounts['terisi']],
                'maintenance' => ['label' => 'Maintenance', 'count' => $statusCounts['maintenance']],
            ];
            $activeStatus = (string) request('status', '');
        @endphp
        <div class="px-5 bg-stone-50/60 flex items-center gap-1 overflow-x-auto no-scrollbar">
            @foreach($tabs as $key => $tab)
                @php $isActive = ($activeStatus === $key); @endphp
                <a href="{{ route('rooms.index', array_filter(['status' => $key ?: null, 'cari' => request('cari'), 'room_type_id' => request('room_type_id'), 'floor' => request('floor')])) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-3 text-xs font-medium border-b-2 transition whitespace-nowrap {{ $isActive ? 'border-amber-800 text-amber-900 font-semibold' : 'border-transparent text-stone-600 hover:text-stone-900 hover:border-stone-300' }}">
                    <span>{{ $tab['label'] }}</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[11px] tabular-nums {{ $isActive ? 'bg-amber-100 text-amber-900' : 'bg-stone-200/70 text-stone-600' }}">
                        {{ $tab['count'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    @if($rooms->isEmpty())
        <div class="bg-white border border-stone-200 rounded-2xl p-12 text-center">
            <p class="text-sm font-semibold text-stone-800">Tidak ada unit kamar yang sesuai filter.</p>
            <p class="text-xs text-stone-500 mt-1">Ubah kriteria pencarian atau tambahkan unit kamar baru.</p>
        </div>
    @else
        <!-- MODE A: MATRIKS KAMAR PER LANTAI -->
        <div x-show="viewMode === 'matrix'" class="space-y-6">
            @foreach($rooms->groupBy('floor') as $floorNumber => $floorRooms)
                <div class="bg-white border border-stone-200 rounded-2xl p-5 sm:p-6">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-stone-100">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-[#181512] text-amber-300 font-editorial text-lg flex items-center justify-center">
                                {{ $floorNumber }}
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-stone-900">Lantai {{ $floorNumber }}</h3>
                                <p class="text-xs text-stone-500">{{ $floorRooms->count() }} unit terdaftar di lantai ini</p>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-stone-500 tabular-nums">
                            {{ $floorRooms->where('status', 'tersedia')->count() }} tersedia
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($floorRooms as $room)
                            @php
                                $activeGuest = $room->reservations->firstWhere('status', 'checked_in');
                                $nextBooking = $room->reservations->firstWhere('status', 'confirmed');
                            @endphp
                            <div class="rounded-xl border border-stone-200 bg-stone-50/40 hover:bg-white hover:border-stone-300 transition flex flex-col justify-between overflow-hidden">
                                <div class="p-4 space-y-3">
                                    <!-- Baris Nomor Kamar & Status -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] font-semibold uppercase tracking-wider text-stone-400">Unit</span>
                                            <h4 class="font-mono text-xl font-bold text-stone-900 tracking-tight leading-none mt-0.5">
                                                {{ $room->room_number }}
                                            </h4>
                                        </div>
                                        <x-status-badge :status="$room->status" />
                                    </div>

                                    <!-- Tipe & Tarif -->
                                    <div class="pt-2 border-t border-stone-200/70">
                                        <p class="text-xs font-semibold text-stone-800 truncate">{{ $room->roomType->name }}</p>
                                        <p class="text-xs text-stone-500 tabular-nums mt-0.5">
                                            Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }} / malam
                                        </p>
                                    </div>

                                    <!-- Konteks Penghuni / Status Operasional -->
                                    <div class="min-h-[2.75rem] flex items-center">
                                        @if($room->status === 'terisi' && $activeGuest)
                                            <div class="w-full px-3 py-2 rounded-lg bg-amber-900/[0.06] border border-amber-800/20 text-xs">
                                                <p class="font-semibold text-stone-900 truncate">{{ $activeGuest->user->name }}</p>
                                                <p class="text-[11px] text-stone-500 tabular-nums">Check-out {{ $activeGuest->check_out_date->format('d M') }}</p>
                                            </div>
                                        @elseif($room->status === 'tersedia' && $nextBooking)
                                            <div class="w-full px-3 py-2 rounded-lg bg-stone-100 border border-stone-200/80 text-xs">
                                                <p class="text-[11px] text-stone-500">Booking berikutnya:</p>
                                                <p class="font-medium text-stone-800 truncate">{{ $nextBooking->user->name }} ({{ $nextBooking->check_in_date->format('d M') }})</p>
                                            </div>
                                        @elseif($room->status === 'maintenance')
                                            <p class="text-[11px] text-rose-800/90 leading-snug">
                                                Unit sedang dalam pemeliharaan / pembersihan.
                                            </p>
                                        @else
                                            <p class="text-[11px] text-stone-400">
                                                Siap untuk reservasi baru atau walk-in.
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Footer Aksi Kartu -->
                                <div class="px-4 py-2.5 bg-white border-t border-stone-200/80 flex items-center justify-between gap-2">
                                    @if($room->status === 'terisi' && $activeGuest)
                                        <a href="{{ route('reservations.show', $activeGuest) }}"
                                           class="flex-1 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px] font-semibold text-center hover:bg-amber-100 transition">
                                            Folio Tamu
                                        </a>
                                    @elseif($room->status === 'tersedia')
                                        <form action="{{ route('rooms.update', $room) }}" method="POST" class="flex-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                            <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                            <input type="hidden" name="floor" value="{{ $room->floor }}">
                                            <input type="hidden" name="status" value="maintenance">
                                            <input type="hidden" name="quick_status" value="1">
                                            <button type="submit" class="w-full py-1.5 rounded-lg border border-stone-200 bg-stone-50 text-stone-700 text-[11px] font-semibold hover:bg-rose-50 hover:text-rose-800 hover:border-rose-200 transition">
                                                Maintenance
                                            </button>
                                        </form>
                                    @elseif($room->status === 'maintenance')
                                        <form action="{{ route('rooms.update', $room) }}" method="POST" class="flex-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                            <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                            <input type="hidden" name="floor" value="{{ $room->floor }}">
                                            <input type="hidden" name="status" value="tersedia">
                                            <input type="hidden" name="quick_status" value="1">
                                            <button type="submit" class="w-full py-1.5 rounded-lg bg-emerald-800 text-white text-[11px] font-semibold hover:bg-emerald-900 transition">
                                                Set Tersedia
                                            </button>
                                        </form>
                                    @else
                                        <span class="flex-1 text-center text-[11px] text-stone-400">Terisi</span>
                                    @endif

                                    <a href="{{ route('rooms.show', $room) }}"
                                       class="px-3 py-1.5 rounded-lg border border-stone-200 bg-white text-[11px] font-semibold text-stone-700 hover:bg-stone-100 transition">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- MODE B: TABEL UNIT KAMAR -->
        <div x-show="viewMode === 'table'" style="display: none;" class="bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-stone-50/80 border-b border-stone-200 text-[11px] uppercase tracking-wider text-stone-500">
                        <tr>
                            <th class="px-5 py-3.5 whitespace-nowrap">No. Kamar</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Lantai</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Tipe Kamar</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Tarif / Malam</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Tamu Aktif</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">Status</th>
                            <th class="px-5 py-3.5 whitespace-nowrap">
                                <div class="w-44 ml-auto text-center">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach($rooms as $room)
                            @php $activeGuest = $room->reservations->firstWhere('status', 'checked_in'); @endphp
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="font-mono text-xs font-bold px-2.5 py-1 rounded bg-stone-100 text-stone-900 border border-stone-200/80">
                                        Kamar {{ $room->room_number }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-stone-600 tabular-nums whitespace-nowrap">Lantai {{ $room->floor }}</td>
                                <td class="px-5 py-3.5 font-medium text-stone-900 whitespace-nowrap">{{ $room->roomType->name }}</td>
                                <td class="px-5 py-3.5 font-semibold text-stone-900 tabular-nums whitespace-nowrap">
                                    Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if($activeGuest)
                                        <span class="text-xs font-medium text-stone-800">{{ $activeGuest->user->name }}</span>
                                        <span class="block text-[11px] text-stone-400">s/d {{ $activeGuest->check_out_date->format('d M Y') }}</span>
                                    @else
                                        <span class="text-xs text-stone-400">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <x-status-badge :status="$room->status" />
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="w-44 ml-auto flex items-center justify-end gap-2">
                                        @if($room->status === 'tersedia')
                                            <form action="{{ route('rooms.update', $room) }}" method="POST" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                                <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                                <input type="hidden" name="floor" value="{{ $room->floor }}">
                                                <input type="hidden" name="status" value="maintenance">
                                                <input type="hidden" name="quick_status" value="1">
                                                <button type="submit" class="w-24 py-1.5 rounded-lg border border-stone-200 bg-stone-50 text-xs font-medium text-stone-700 hover:bg-rose-50 hover:text-rose-800 transition text-center">
                                                    Maintenance
                                                </button>
                                            </form>
                                        @elseif($room->status === 'maintenance')
                                            <form action="{{ route('rooms.update', $room) }}" method="POST" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="room_number" value="{{ $room->room_number }}">
                                                <input type="hidden" name="room_type_id" value="{{ $room->room_type_id }}">
                                                <input type="hidden" name="floor" value="{{ $room->floor }}">
                                                <input type="hidden" name="status" value="tersedia">
                                                <input type="hidden" name="quick_status" value="1">
                                                <button type="submit" class="w-24 py-1.5 rounded-lg bg-emerald-800 text-white text-xs font-medium hover:bg-emerald-900 transition text-center">
                                                    Set Tersedia
                                                </button>
                                            </form>
                                        @else
                                            <span class="w-24 py-1.5 text-center text-[11px] font-medium text-stone-400">In-House</span>
                                        @endif

                                        <a href="{{ route('rooms.show', $room) }}"
                                           class="w-[72px] py-1.5 rounded-lg border border-stone-200 bg-white text-xs font-medium text-stone-700 hover:bg-stone-100 transition text-center">
                                            Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection