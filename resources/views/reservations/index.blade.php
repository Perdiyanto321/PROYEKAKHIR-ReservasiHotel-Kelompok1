@extends('layouts.app')
@section('title', 'Daftar Reservasi')
@section('header', auth()->user()->hasRole('tamu') ? 'Riwayat Reservasi Saya' : 'Manajemen Reservasi Tamu')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <form method="GET" action="{{ route('reservations.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Kode booking / nama tamu..."
               class="rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        <select name="status" class="rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            <option value="">Semua Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="confirmed" @selected(request('status') === 'confirmed')>Confirmed</option>
            <option value="checked_in" @selected(request('status') === 'checked_in')>Checked-In</option>
            <option value="checked_out" @selected(request('status') === 'checked_out')>Checked-Out</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
        </select>
        <button class="px-4 py-2 rounded-lg border border-stone-300 bg-white text-sm font-medium hover:bg-stone-50">Filter</button>
    </form>

    <a href="{{ route('reservations.create') }}" class="px-4 py-2 rounded-lg bg-amber-800 text-white text-sm font-medium hover:bg-amber-900">
        + Buat Reservasi
    </a>
</div>

<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-6 py-3">Kode Booking</th>
                    <th class="px-6 py-3">Tamu</th>
                    <th class="px-6 py-3">Kamar</th>
                    <th class="px-6 py-3">Check-In / Out</th>
                    <th class="px-6 py-3">Total Biaya</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse($reservations as $rsv)
                    <tr>
                        <td class="px-6 py-4 font-semibold tabular-nums">{{ $rsv->booking_code }}</td>
                        <td class="px-6 py-4">{{ $rsv->user->name }}</td>
                        <td class="px-6 py-4">No. {{ $rsv->room->room_number }} <span class="text-xs text-stone-500">({{ $rsv->room->roomType->name }})</span></td>
                        <td class="px-6 py-4 tabular-nums text-stone-600">
                            {{ $rsv->check_in_date->format('d M Y') }} – {{ $rsv->check_out_date->format('d M Y') }}
                            <span class="block text-xs text-stone-400">{{ $rsv->total_nights }} malam</span>
                        </td>
                        <td class="px-6 py-4 font-medium tabular-nums">Rp {{ number_format($rsv->total_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$rsv->status" /></td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('reservations.show', $rsv) }}" class="text-xs font-medium text-amber-800 hover:underline">Kelola / Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-stone-500">Belum ada data reservasi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $reservations->links() }}</div>
@endsection