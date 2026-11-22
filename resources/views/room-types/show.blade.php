@extends('layouts.app')
@section('title', $roomType->name)
@section('header', 'Detail Tipe Kamar')

@section('content')
<div class="bg-white border border-stone-200 rounded-xl overflow-hidden max-w-4xl">
    @if($roomType->image_url)
        <img src="{{ $roomType->image_url }}" alt="{{ $roomType->name }}" class="w-full h-64 object-cover">
    @endif
    <div class="p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="font-editorial text-3xl text-stone-900">{{ $roomType->name }}</h2>
                <p class="text-sm text-stone-500 mt-1">Kapasitas Maksimal: {{ $roomType->capacity }} Tamu</p>
            </div>
            <p class="text-xl font-semibold text-amber-800 tabular-nums">Rp {{ number_format($roomType->base_price, 0, ',', '.') }} <span class="text-xs font-normal text-stone-500">/ malam</span></p>
        </div>
        <p class="mt-4 text-sm text-stone-700 leading-relaxed">{{ $roomType->description }}</p>

        <div class="mt-5">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-stone-500 mb-2">Fasilitas Tersedia</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($roomType->facilities as $fac)
                    <span class="px-3 py-1 rounded-full text-xs bg-stone-100 text-stone-800 border border-stone-200">{{ $fac->name }}</span>
                @endforeach
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-stone-200 flex justify-between items-center">
            <span class="text-xs text-stone-500">Total Unit Terdaftar: {{ $roomType->rooms->count() }} kamar</span>
            <a href="{{ route('room-types.index') }}" class="px-4 py-2 rounded-lg border border-stone-300 text-sm text-stone-700 hover:bg-stone-50">Kembali</a>
        </div>
    </div>
</div>
@endsection