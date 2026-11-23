@extends('layouts.app')
@section('title', 'Tipe Kamar & Tarif')
@section('header', 'Master Tipe Kamar & Tarif')

@section('content')
@php
    $totalTypes = $roomTypes->count();
    $totalUnits = $roomTypes->sum('rooms_count');
    $avgPrice = $totalTypes > 0 ? $roomTypes->avg('base_price') : 0;
@endphp

<!-- KPI Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Kategori Tipe Kamar" value="{{ $totalTypes }} Tipe" subtitle="Varian akomodasi terdaftar">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Rata-Rata Tarif / Malam" value="Rp {{ number_format($avgPrice, 0, ',', '.') }}" subtitle="Tarif dasar sebelum promo">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Total Inventaris Unit" value="{{ $totalUnits }} Kamar" subtitle="Tersebar di seluruh tipe kamar">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 16h20"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>
</div>

<!-- Header Bar -->
<div class="bg-white border border-stone-200 rounded-xl p-5 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-base font-semibold text-stone-900">Daftar Tipe Kamar & Paket Fasilitas</h2>
        <p class="text-xs text-stone-500 mt-0.5">Unggah foto lokal pada setiap tipe kamar agar tampil optimal di Katalog Tamu dan Detail Reservasi.</p>
    </div>

    <a href="{{ route('room-types.create') }}"
       class="group inline-flex items-center justify-between sm:justify-center gap-3 pl-2 pr-4 py-1.5 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide shadow-[0_2px_10px_-2px_rgba(24,21,18,0.28)] hover:bg-stone-900 hover:border-amber-700/60 transition-all duration-200 shrink-0">
        <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center group-hover:bg-amber-500/25 group-hover:text-amber-200 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9h1M9 13h1M9 17h1"/>
            </svg>
        </span>
        <span>Tipe Kamar Baru</span>
        <svg class="w-3.5 h-3.5 text-stone-400 group-hover:text-amber-300 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </a>
</div>

<!-- Grid Kartu Tipe Kamar -->
@if($roomTypes->isEmpty())
    <div class="bg-white border border-stone-200 rounded-2xl p-12 text-center">
        <p class="text-sm font-semibold text-stone-800">Belum ada data tipe kamar.</p>
        <p class="text-xs text-stone-500 mt-1">Tambahkan tipe kamar pertama untuk mulai mengelola unit dan tarif hotel.</p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roomTypes as $type)
            <div class="bg-white border border-stone-200 rounded-2xl overflow-hidden flex flex-col justify-between transition hover:border-stone-300">
                <div>
                    <!-- Foto Sampul Tipe Kamar -->
                    <div class="relative aspect-[16/10] bg-stone-200 overflow-hidden">
                        <img src="{{ $type->cover_url }}"
                             alt="{{ $type->name }}"
                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-950/10 to-transparent"></div>

                        <!-- Badge Sumber Foto -->
                        <div class="absolute top-3 left-3">
                            @if($type->has_local_image)
                                <span class="px-2.5 py-1 rounded-md bg-emerald-950/80 backdrop-blur-xs text-emerald-200 text-[10px] font-semibold tracking-wider uppercase border border-emerald-500/30">
                                    Foto Lokal
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-md bg-stone-950/75 backdrop-blur-xs text-amber-200 text-[10px] font-semibold tracking-wider uppercase border border-white/10">
                                    Foto Default
                                </span>
                            @endif
                        </div>

                        <!-- Badge Jumlah Unit -->
                        <div class="absolute top-3 right-3">
                            <span class="px-2.5 py-1 rounded-md bg-white/90 backdrop-blur-xs text-stone-900 text-[11px] font-semibold tabular-nums">
                                {{ $type->rooms_count }} Unit
                            </span>
                        </div>

                        <!-- Judul & Tarif di Atas Gambar -->
                        <div class="absolute bottom-3.5 left-4 right-4 flex items-end justify-between gap-2 text-white">
                            <h3 class="font-editorial text-2xl text-amber-50 leading-tight">{{ $type->name }}</h3>
                            <div class="text-right shrink-0 tabular-nums">
                                <p class="text-sm font-bold text-white">Rp {{ number_format($type->base_price, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-stone-300">/ malam</p>
                            </div>
                        </div>
                    </div>

                    <!-- Spesifikasi & Fasilitas -->
                    <div class="p-5 space-y-4">
                        <div class="flex items-center gap-4 text-xs text-stone-600 pb-3.5 border-b border-stone-100">
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4 text-amber-800" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                                </svg>
                                Maks. {{ $type->capacity }} Tamu
                            </span>
                            <span class="text-stone-300">&bull;</span>
                            <span class="inline-flex items-center gap-1.5 font-medium">
                                <svg class="w-4 h-4 text-amber-800" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                {{ $type->facilities->count() }} Fasilitas
                            </span>
                        </div>

                        <p class="text-xs text-stone-600 leading-relaxed line-clamp-2 min-h-[2.5rem]">
                            {{ $type->description ?: 'Belum ada deskripsi untuk tipe kamar ini.' }}
                        </p>

                        @if($type->facilities->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($type->facilities->take(5) as $facility)
                                    <span class="px-2 py-0.5 rounded bg-stone-100 border border-stone-200/80 text-[11px] font-medium text-stone-700">
                                        {{ $facility->name }}
                                    </span>
                                @endforeach
                                @if($type->facilities->count() > 5)
                                    <span class="px-2 py-0.5 rounded bg-amber-50 border border-amber-200 text-[11px] font-medium text-amber-900">
                                        +{{ $type->facilities->count() - 5 }} lainnya
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Aksi Seimbang -->
                <div class="px-5 py-3.5 bg-stone-50/70 border-t border-stone-200 flex items-center justify-between gap-2.5">
                    <a href="{{ route('room-types.edit', $type) }}"
                       class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-800 hover:bg-stone-100 transition">
                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        <span>Edit & Unggah Foto</span>
                    </a>

                    <form action="{{ route('room-types.destroy', $type) }}" method="POST"
                          onsubmit="return confirm('Hapus tipe kamar {{ $type->name }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                @disabled($type->rooms_count > 0)
                                title="{{ $type->rooms_count > 0 ? 'Tidak dapat dihapus karena masih memiliki unit kamar' : 'Hapus tipe kamar' }}"
                                class="px-3 py-2 rounded-lg border text-xs font-semibold transition {{ $type->rooms_count > 0 ? 'border-stone-200 bg-stone-100 text-stone-400 cursor-not-allowed' : 'border-rose-200 bg-white text-rose-700 hover:bg-rose-50' }}">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection