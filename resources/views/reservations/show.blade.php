@extends('layouts.app')
@section('title', $reservation->booking_code)
@section('header', 'Detail Reservasi #' . $reservation->booking_code)

@section('content')
<div class="bg-white border border-stone-200 rounded-xl p-6 max-w-3xl">
    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
        <div>
            <p class="text-xs uppercase tracking-wider text-stone-500">Kode Pemesanan</p>
            <h2 class="text-2xl font-bold text-stone-900 tabular-nums mt-0.5">{{ $reservation->booking_code }}</h2>
            <p class="text-sm text-stone-600 mt-1">Tamu: <strong>{{ $reservation->user->name }}</strong> ({{ $reservation->user->email }})</p>
        </div>
        <x-status-badge :status="$reservation->status" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-5 border-b border-stone-200 text-sm">
        <div>
            <p class="text-xs uppercase text-stone-500">Informasi Kamar</p>
            <p class="font-semibold text-stone-900 mt-1">Kamar {{ $reservation->room->room_number }} — {{ $reservation->room->roomType->name }}</p>
            <p class="text-stone-600">Lantai {{ $reservation->room->floor }} &bull; Rp {{ number_format($reservation->room->roomType->base_price, 0, ',', '.') }} / malam</p>
        </div>
        <div>
            <p class="text-xs uppercase text-stone-500">Durasi & Total Biaya</p>
            <p class="font-semibold text-stone-900 mt-1 tabular-nums">{{ $reservation->check_in_date->format('d M Y') }} – {{ $reservation->check_out_date->format('d M Y') }} ({{ $reservation->total_nights }} Malam)</p>
            <p class="text-lg font-bold text-amber-800 tabular-nums mt-0.5">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</p>
        </div>
        <div class="sm:col-span-2">
            <p class="text-xs uppercase text-stone-500">Permintaan Khusus</p>
            <p class="text-stone-700 mt-1">{{ $reservation->special_requests ?: 'Tidak ada permintaan khusus.' }}</p>
        </div>
    </div>

    <!-- Panel Aksi Berdasarkan Policy (Modul 6) -->
    <div class="pt-5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            @can('update', $reservation)
                @if($reservation->status === 'pending')
                    <form action="{{ route('reservations.status', $reservation) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button class="px-4 py-2 rounded-lg bg-emerald-700 text-white text-xs font-medium hover:bg-emerald-800">Konfirmasi Booking</button>
                    </form>
                @endif
                @if(in_array($reservation->status, ['pending', 'confirmed']))
                    <form action="{{ route('reservations.status', $reservation) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="checked_in">
                        <button class="px-4 py-2 rounded-lg bg-sky-700 text-white text-xs font-medium hover:bg-sky-800">Proses Check-In</button>
                    </form>
                @endif
                @if($reservation->status === 'checked_in')
                    <form action="{{ route('reservations.status', $reservation) }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="checked_out">
                        <button class="px-4 py-2 rounded-lg bg-stone-800 text-white text-xs font-medium hover:bg-stone-900">Proses Check-Out</button>
                    </form>
                @endif
            @endcan

            @can('cancel', $reservation)
                <form action="{{ route('reservations.cancel', $reservation) }}" method="POST" onsubmit="return confirm('Batalkan reservasi ini?')">
                    @csrf @method('PATCH')
                    <button class="px-4 py-2 rounded-lg border border-rose-300 text-rose-700 text-xs font-medium hover:bg-rose-50">Batalkan Reservasi</button>
                </form>
            @endcan

            @can('delete', $reservation)
                <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" onsubmit="return confirm('Hapus permanen data reservasi ini?')">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 rounded-lg bg-rose-700 text-white text-xs font-medium hover:bg-rose-800">Hapus Permanen</button>
                </form>
            @endcan
        </div>

        <a href="{{ route('reservations.index') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-xs font-medium text-stone-700 hover:bg-stone-50">Kembali</a>
    </div>
</div>
@endsection