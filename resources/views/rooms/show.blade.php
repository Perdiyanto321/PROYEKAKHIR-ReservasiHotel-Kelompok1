@extends('layouts.app')
@section('title', 'Kamar ' . $room->room_number)
@section('header', 'Detail Unit Kamar ' . $room->room_number)

@section('content')
<div class="bg-white border border-stone-200 rounded-xl p-6 max-w-3xl">
    <div class="flex items-center justify-between border-b border-stone-200 pb-4">
        <div>
            <h2 class="text-xl font-semibold text-stone-900">Kamar {{ $room->room_number }} — {{ $room->roomType->name }}</h2>
            <p class="text-sm text-stone-500">Lantai {{ $room->floor }} &bull; Kapasitas {{ $room->roomType->capacity }} Tamu</p>
        </div>
        <x-status-badge :status="$room->status" />
    </div>
    <div class="py-4 text-sm space-y-2">
        <p><span class="text-stone-500">Tarif per Malam:</span> <strong class="tabular-nums">Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }}</strong></p>
        <p><span class="text-stone-500">Catatan:</span> {{ $room->notes ?? 'Tidak ada catatan khusus' }}</p>
    </div>
    <div class="mt-4">
        <a href="{{ route('rooms.index') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm text-stone-700 hover:bg-stone-50">Kembali</a>
    </div>
</div>
@endsection