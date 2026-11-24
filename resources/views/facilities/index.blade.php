@extends('layouts.app')
@section('title', 'Master Fasilitas')
@section('header', 'Master Fasilitas Kamar')

@section('content')
<!-- KPI Ringkas -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Total Fasilitas" value="{{ $stats['total'] }} Item" subtitle="Terdaftar di master data">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Sedang Dipakai" value="{{ $stats['used'] }} Fasilitas" subtitle="Terhubung ke minimal satu tipe kamar">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Belum Dipakai" value="{{ $stats['unused'] }} Fasilitas" subtitle="Aman untuk dihapus">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>
</div>

<!-- Form Tambah Cepat -->
<div class="bg-white border border-stone-200 rounded-2xl p-5 mb-6">
    <h2 class="text-base font-semibold text-stone-900">Tambah Fasilitas Baru</h2>
    <p class="text-xs text-stone-500 mt-0.5 mb-4">Contoh: High-Speed Wi-Fi, King Size Bed, Artisan Breakfast, Private Balcony. Fasilitas dihubungkan ke tipe kamar lewat menu Tipe Kamar &amp; Tarif.</p>

    <form method="POST" action="{{ route('facilities.store') }}" class="flex flex-col sm:flex-row sm:items-start gap-3">
        @csrf
        <div class="flex-1">
            <label for="facility-name" class="sr-only">Nama fasilitas</label>
            <input id="facility-name" type="text" name="name" value="{{ old('name') }}" maxlength="100" required
                   placeholder="Nama fasilitas, mis. Rain Shower"
                   class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        </div>
        <button type="submit"
                class="group inline-flex items-center justify-center gap-3 pl-2 pr-4 py-1.5 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide shadow-[0_2px_10px_-2px_rgba(24,21,18,0.28)] hover:bg-stone-900 hover:border-amber-700/60 transition-all duration-200 shrink-0">
            <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center group-hover:bg-amber-500/25 group-hover:text-amber-200 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                </svg>
            </span>
            <span>Simpan Fasilitas</span>
        </button>
    </form>
</div>

<!-- Tabel Fasilitas -->
<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <div class="p-5 border-b border-stone-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-stone-900">Daftar Fasilitas</h2>
            <p class="text-xs text-stone-500 mt-0.5">Fasilitas yang masih dipakai tipe kamar tidak dapat dihapus.</p>
        </div>

        <form method="GET" action="{{ route('facilities.index') }}" class="flex items-center gap-2 w-full md:max-w-sm">
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama fasilitas..."
                       class="w-full pl-9 pr-3 py-2 rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg border border-stone-300 bg-stone-50 text-sm font-medium text-stone-700 hover:bg-stone-100 transition shrink-0">Cari</button>
            @if(request('cari'))
                <a href="{{ route('facilities.index') }}" class="px-2.5 py-2 text-xs font-medium text-stone-500 hover:text-stone-900 shrink-0">Reset</a>
            @endif
        </form>
    </div>

    @if($facilities->isEmpty())
        <div class="p-12 text-center">
            <p class="text-sm font-semibold text-stone-800">{{ request('cari') ? 'Fasilitas tidak ditemukan.' : 'Belum ada data fasilitas.' }}</p>
            <p class="text-xs text-stone-500 mt-1">{{ request('cari') ? 'Coba kata kunci lain.' : 'Tambahkan fasilitas pertama lewat form di atas.' }}</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50/70 border-b border-stone-200 text-left text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                        <th class="px-5 py-3 w-12">No</th>
                        <th class="px-5 py-3">Nama Fasilitas</th>
                        <th class="px-5 py-3">Dipakai Tipe Kamar</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($facilities as $facility)
                        <tr x-data="{ editing: false }" class="hover:bg-stone-50/50 transition">
                            <td class="px-5 py-3.5 text-stone-400 tabular-nums">{{ $loop->iteration }}</td>

                            <td class="px-5 py-3.5">
                                <span x-show="!editing" class="font-medium text-stone-900">{{ $facility->name }}</span>

                                <form x-show="editing" style="display: none;" method="POST" action="{{ route('facilities.update', $facility) }}" class="flex items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" value="{{ $facility->name }}" maxlength="100" required
                                           class="w-full max-w-xs rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                                    <button type="submit" class="px-3 py-2 rounded-lg bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-900 transition">Simpan</button>
                                    <button type="button" @click="editing = false" class="px-3 py-2 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition">Batal</button>
                                </form>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold tabular-nums {{ $facility->room_types_count > 0 ? 'bg-amber-100 text-amber-900' : 'bg-stone-100 text-stone-500' }}">
                                        {{ $facility->room_types_count }} tipe
                                    </span>
                                    @foreach($facility->roomTypes->take(3) as $type)
                                        <span class="px-2 py-0.5 rounded bg-stone-100 border border-stone-200/80 text-[11px] font-medium text-stone-700">{{ $type->name }}</span>
                                    @endforeach
                                    @if($facility->room_types_count > 3)
                                        <span class="text-[11px] text-stone-500">+{{ $facility->room_types_count - 3 }} lainnya</span>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="editing = true" x-show="!editing"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-800 hover:bg-stone-100 transition">
                                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit
                                    </button>

                                    <form action="{{ route('facilities.destroy', $facility) }}" method="POST"
                                          onsubmit="return confirm(@js('Hapus fasilitas ' . $facility->name . '?'))">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                @disabled($facility->room_types_count > 0)
                                                title="{{ $facility->room_types_count > 0 ? 'Tidak dapat dihapus karena masih dipakai tipe kamar' : 'Hapus fasilitas' }}"
                                                class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition {{ $facility->room_types_count > 0 ? 'border-stone-200 bg-stone-100 text-stone-400 cursor-not-allowed' : 'border-rose-200 bg-white text-rose-700 hover:bg-rose-50' }}">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
