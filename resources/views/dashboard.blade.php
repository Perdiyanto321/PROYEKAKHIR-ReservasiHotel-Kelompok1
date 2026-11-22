@extends('layouts.app')
@section('title', 'Dashboard')
@section('header', 'Ringkasan Operasional')

@section('content')
@if($isGuest)
    <div class="bg-white border border-stone-200 rounded-xl p-6 mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-amber-800 font-semibold">Selamat Datang Kembali</p>
            <h2 class="font-editorial text-3xl text-stone-900 mt-1">{{ auth()->user()->name }}</h2>
            <p class="text-sm text-stone-600 mt-1">Kelola jadwal menginap Anda atau pesan kamar baru dengan mudah.</p>
        </div>
        <a href="{{ route('reservations.create') }}" class="px-5 py-2.5 rounded-lg bg-amber-800 text-white text-sm font-medium hover:bg-amber-900 transition">
            Pesan Kamar Sekarang
        </a>
    </div>

    <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
            <h3 class="font-semibold text-stone-900">Reservasi Terakhir Anda</h3>
            <a href="{{ route('reservations.index') }}" class="text-xs font-medium text-amber-800 hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-6 py-3">Kode Booking</th>
                        <th class="px-6 py-3">Kamar</th>
                        <th class="px-6 py-3">Check-In / Out</th>
                        <th class="px-6 py-3">Total Biaya</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($myReservations as $rsv)
                        <tr>
                            <td class="px-6 py-4 font-medium tabular-nums">{{ $rsv->booking_code }}</td>
                            <td class="px-6 py-4">Kamar {{ $rsv->room->room_number }} ({{ $rsv->room->roomType->name }})</td>
                            <td class="px-6 py-4 tabular-nums text-stone-600">
                                {{ $rsv->check_in_date->format('d M Y') }} – {{ $rsv->check_out_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 font-medium tabular-nums">Rp {{ number_format($rsv->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$rsv->status" /></td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('reservations.show', $rsv) }}" class="text-xs font-medium text-amber-800 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-stone-500">Anda belum memiliki riwayat reservasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <!-- KPI Strip Admin & Resepsionis -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Tingkat Okupansi" value="{{ $occupancyRate }}%" subtitle="{{ $occupiedRooms }} dari {{ $totalRooms }} kamar terisi" />
        <x-stat-card title="Kamar Tersedia" value="{{ $availableRooms }} Unit" subtitle="Siap menerima tamu hari ini" />
        <x-stat-card title="Check-In Hari Ini" value="{{ $todayCheckIns }} Tamu" subtitle="Jadwal kedatangan aktif" />
        <x-stat-card title="Total Pendapatan" value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}" subtitle="Akumulasi reservasi terkonfirmasi" />
    </div>

    <div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
            <h3 class="font-semibold text-stone-900">Aktivitas Reservasi Terbaru</h3>
            <a href="{{ route('reservations.index') }}" class="text-xs font-medium text-amber-800 hover:underline">Kelola Semua Reservasi</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-6 py-3">Nama Tamu</th>
                        <th class="px-6 py-3">Kamar</th>
                        <th class="px-6 py-3">Jadwal Inap</th>
                        <th class="px-6 py-3">Total</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($recentReservations as $rsv)
                        <tr>
                            <td class="px-6 py-4 font-medium tabular-nums">{{ $rsv->booking_code }}</td>
                            <td class="px-6 py-4">{{ $rsv->user->name }}</td>
                            <td class="px-6 py-4">No. {{ $rsv->room->room_number }} <span class="text-stone-400">({{ $rsv->room->roomType->name }})</span></td>
                            <td class="px-6 py-4 tabular-nums text-stone-600">{{ $rsv->check_in_date->format('d/m/Y') }} – {{ $rsv->check_out_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-medium tabular-nums">Rp {{ number_format($rsv->total_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4"><x-status-badge :status="$rsv->status" /></td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('reservations.show', $rsv) }}" class="text-xs font-medium text-amber-800 hover:underline">Detail</a>
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