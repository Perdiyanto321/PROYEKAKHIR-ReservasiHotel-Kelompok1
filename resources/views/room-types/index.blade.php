@extends('layouts.app')
@section('title', 'Tipe Kamar')
@section('header', 'Master Tipe Kamar & Tarif')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <form method="GET" action="{{ route('room-types.index') }}" class="flex gap-2 w-full sm:w-auto">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari tipe kamar..."
               class="rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        <button class="px-4 py-2 rounded-lg border border-stone-300 bg-white text-sm font-medium hover:bg-stone-50">Cari</button>
    </form>
    <a href="{{ route('room-types.create') }}" class="px-4 py-2 rounded-lg bg-stone-900 text-white text-sm font-medium hover:bg-stone-800">
        + Tambah Tipe Kamar
    </a>
</div>

<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-6 py-3">Tipe Kamar</th>
                    <th class="px-6 py-3">Tarif / Malam</th>
                    <th class="px-6 py-3">Kapasitas</th>
                    <th class="px-6 py-3">Jumlah Unit</th>
                    <th class="px-6 py-3">Fasilitas</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse($roomTypes as $type)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-stone-900">{{ $type->name }}</p>
                            <p class="text-xs text-stone-500">{{ $type->slug }}</p>
                        </td>
                        <td class="px-6 py-4 font-medium tabular-nums">Rp {{ number_format($type->base_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 tabular-nums">{{ $type->capacity }} Tamu</td>
                        <td class="px-6 py-4 tabular-nums">{{ $type->rooms_count }} Unit</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($type->facilities as $fac)
                                    <span class="px-2 py-0.5 text-xs bg-stone-100 text-stone-700 rounded">{{ $fac->name }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('room-types.show', $type) }}" class="text-xs text-stone-600 hover:underline">Detail</a>
                            <a href="{{ route('room-types.edit', $type) }}" class="text-xs text-amber-800 font-medium hover:underline">Ubah</a>
                            <form action="{{ route('room-types.destroy', $type) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tipe kamar ini beserta seluruh unitnya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-700 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-stone-500">Belum ada data tipe kamar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $roomTypes->links() }}</div>
@endsection