<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Tipe Kamar</label>
        <select name="room_type_id" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            <option value="">-- Pilih Tipe Kamar --</option>
            @foreach($roomTypes as $rt)
                <option value="{{ $rt->id }}" @selected(old('room_type_id', $room->room_type_id) == $rt->id)>
                    {{ $rt->name }} (Rp {{ number_format($rt->base_price, 0, ',', '.') }})
                </option>
            @endforeach
        </select>
        @error('room_type_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Nomor Kamar</label>
        <input type="text" name="room_number" value="{{ old('room_number', $room->room_number) }}" placeholder="Contoh: 104"
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('room_number') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Lantai</label>
        <input type="number" name="floor" value="{{ old('floor', $room->floor) }}"
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('floor') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Status Fisik Kamar</label>
        <select name="status" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
            <option value="tersedia" @selected(old('status', $room->status) === 'tersedia')>Tersedia</option>
            <option value="terisi" @selected(old('status', $room->status) === 'terisi')>Terisi</option>
            <option value="maintenance" @selected(old('status', $room->status) === 'maintenance')>Maintenance</option>
        </select>
        @error('status') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-stone-700 mb-1">Catatan Tambahan (Opsional)</label>
        <input type="text" name="notes" value="{{ old('notes', $room->notes) }}" placeholder="Contoh: Dekat lift, pemandangan taman"
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('notes') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>