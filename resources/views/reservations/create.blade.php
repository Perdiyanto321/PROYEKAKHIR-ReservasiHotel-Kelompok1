@extends('layouts.app')
@section('title', 'Buat Reservasi')
@section('header', 'Formulir Reservasi Kamar')

@section('content')
<div class="bg-white border border-stone-200 rounded-xl p-6 max-w-2xl">
    <form action="{{ route('reservations.store') }}" method="POST" class="space-y-5">
        @csrf

        @if($guests->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Pilih Tamu (Walk-In / Resepsionis)</label>
                <select name="guest_id" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                    <option value="">-- Atas Nama Akun Sendiri --</option>
                    @foreach($guests as $guest)
                        <option value="{{ $guest->id }}" @selected(old('guest_id') == $guest->id)>{{ $guest->name }} ({{ $guest->email }})</option>
                    @endforeach
                </select>
                @error('guest_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Pilih Unit Kamar Tersedia</label>
            <select name="room_id" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                <option value="">-- Pilih Kamar --</option>
                @foreach($rooms as $room)
                    <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>
                        Kamar {{ $room->room_number }} — {{ $room->roomType->name }} (Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }}/malam)
                    </option>
                @endforeach
            </select>
            @error('room_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Check-In</label>
                <input type="date" name="check_in_date" value="{{ old('check_in_date', now()->toDateString()) }}"
                       class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                @error('check_in_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Tanggal Check-Out</label>
                <input type="date" name="check_out_date" value="{{ old('check_out_date', now()->addDay()->toDateString()) }}"
                       class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                @error('check_out_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Permintaan Khusus (Opsional)</label>
            <textarea name="special_requests" rows="3" placeholder="Contoh: Lantai atas, ekstra bantal, atau perkiraan jam tiba"
                      class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">{{ old('special_requests') }}</textarea>
            @error('special_requests') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="pt-2 flex items-center gap-3">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-amber-800 text-white text-sm font-medium hover:bg-amber-900">Konfirmasi Reservasi</button>
            <a href="{{ route('reservations.index') }}" class="px-4 py-2.5 rounded-lg border border-stone-300 text-sm text-stone-700 hover:bg-stone-50">Batal</a>
        </div>
    </form>
</div>
@endsection