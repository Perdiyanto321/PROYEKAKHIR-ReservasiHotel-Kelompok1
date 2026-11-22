@extends('layouts.app')
@section('title', 'Unit Kamar')
@section('header', 'Manajemen Unit & Status Kamar')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-5">
    <form method="GET" action="{{ route('rooms.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Nomor kamar..."
               class="rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        <select name="tipe" class="rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            <option value="">Semua Tipe</option>
            @foreach($roomTypes as $rt)
                <option value="{{ $rt->id }}" @selected(request('tipe') == $rt->id)>{{ $rt->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            <option value="">Semua Status</option>
            <option value="tersedia" @selected(request('status') === 'tersedia')>Tersedia</option>
            <option value="terisi" @selected(request('status') === 'terisi')>Terisi</option>
            <option value="maintenance" @selected(request('status') === 'maintenance')>Maintenance</option>
        </select>
        <button class="px-4 py-2 rounded-lg border border-stone-300 bg-white text-sm font-medium hover:bg-stone-50">Filter</button>
    </form>

    @can('kelola-master-kamar')
        <a href="{{ route('rooms.create') }}" class="px-4 py-2 rounded-lg bg-stone-900 text-white text-sm font-medium hover:bg-stone-800">
            + Tambah Unit Kamar
        </a>
    @endcan
</div>

<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-stone-50 border-b border-stone-200 text-xs uppercase text-stone-500">
                <tr>
                    <th class="px-6 py-3">No. Kamar</th>
                    <th class="px-6 py-3">Tipe Kamar</th>
                    <th class="px-6 py-3">Lantai</th>
                    <th class="px-6 py-3">Tarif / Malam</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Catatan</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200">
                @forelse($rooms as $room)
                    <tr>
                        <td class="px-6 py-4 font-semibold tabular-nums">{{ $room->room_number }}</td>
                        <td class="px-6 py-4">{{ $room->roomType->name }}</td>
                        <td class="px-6 py-4 tabular-nums">Lantai {{ $room->floor }}</td>
                        <td class="px-6 py-4 tabular-nums">Rp {{ number_format($room->roomType->base_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4"><x-status-badge :status="$room->status" /></td>
                        <td class="px-6 py-4 text-xs text-stone-500">{{ $room->notes ?? '—' }}</td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('rooms.show', $room) }}" class="text-xs text-stone-600 hover:underline">Detail</a>
                            @can('kelola-master-kamar')
                                <a href="{{ route('rooms.edit', $room) }}" class="text-xs text-amber-800 font-medium hover:underline">Ubah</a>
                                <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="inline" onsubmit="return confirm('Hapus unit kamar ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-700 hover:underline">Hapus</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-stone-500">Data unit kamar tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $rooms->links() }}</div>
@endsection