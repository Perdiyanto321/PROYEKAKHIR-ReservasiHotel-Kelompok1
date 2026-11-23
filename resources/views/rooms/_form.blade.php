@php
    $isEdit = isset($room);
    $typesPayload = $roomTypes->mapWithKeys(fn ($t) => [
        $t->id => [
            'name' => $t->name,
            'price' => 'Rp ' . number_format($t->base_price, 0, ',', '.'),
            'capacity' => $t->capacity,
            'cover' => $t->cover_url,
        ],
    ]);
    $defaultTypeId = (string) old('room_type_id', $isEdit ? $room->room_type_id : ($roomTypes->first()?->id ?? ''));
@endphp

<div x-data="{
        roomNumber: @js(old('room_number', $isEdit ? $room->room_number : '')),
        floor: {{ (int) old('floor', $isEdit ? $room->floor : 1) }},
        status: @js(old('status', $isEdit ? $room->status : 'tersedia')),
        typeId: @js($defaultTypeId),
        types: @js($typesPayload),
        get activeType() {
            return this.types[this.typeId] || {
                name: 'Pilih Tipe Kamar',
                price: 'Rp 0',
                capacity: 2,
                cover: 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80'
            };
        }
     }"
     class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- KOLOM KIRI: Form Unit Kamar (7 Kolom) -->
    <div class="lg:col-span-7 bg-white border border-stone-200 rounded-2xl p-6 sm:p-7 space-y-5">
        <div class="pb-4 border-b border-stone-100">
            <h2 class="text-base font-semibold text-stone-900">
                {{ $isEdit ? 'Perbarui Informasi Unit Kamar' : 'Registrasi Unit Kamar Baru' }}
            </h2>
            <p class="text-xs text-stone-500 mt-0.5">Tetapkan nomor pintu unik, lokasi lantai, kategori kamar, dan status awal unit.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="room_number" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Nomor Kamar</label>
                <input type="text"
                       id="room_number"
                       name="room_number"
                       x-model="roomNumber"
                       required
                       placeholder="Contoh: 104"
                       class="w-full rounded-lg border-stone-300 font-mono text-sm focus:border-amber-800 focus:ring-amber-800">
            </div>

            <div>
                <label for="floor" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Lokasi Lantai</label>
                <input type="number"
                       id="floor"
                       name="floor"
                       x-model="floor"
                       min="1"
                       max="99"
                       required
                       class="w-full rounded-lg border-stone-300 text-sm tabular-nums focus:border-amber-800 focus:ring-amber-800">
            </div>
        </div>

        <div>
            <label for="room_type_id" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Tipe Kamar & Tarif</label>
            <select id="room_type_id"
                    name="room_type_id"
                    x-model="typeId"
                    required
                    class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}">
                        {{ $type->name }} — Rp {{ number_format($type->base_price, 0, ',', '.') }} / malam (Maks. {{ $type->capacity }} Tamu)
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-2">Status Operasional Unit</label>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                @foreach(['tersedia' => 'Tersedia (Siap Huni)', 'terisi' => 'Terisi (In-House)', 'maintenance' => 'Maintenance'] as $val => $label)
                    <label class="cursor-pointer">
                        <input type="radio" name="status" value="{{ $val }}" x-model="status" class="peer sr-only">
                        <div class="px-3.5 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 text-xs font-medium text-stone-700 peer-checked:border-amber-800 peer-checked:bg-amber-50/70 peer-checked:text-amber-950 peer-checked:font-semibold transition text-center">
                            {{ $label }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        @if(\Illuminate\Support\Facades\Schema::hasColumn('rooms', 'notes'))
            <div>
                <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Catatan Internal Unit (Opsional)</label>
                <textarea id="notes"
                          name="notes"
                          rows="2"
                          placeholder="Contoh: Dekat lift timur, menghadap taman utama..."
                          class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">{{ old('notes', $isEdit ? $room->notes : '') }}</textarea>
            </div>
        @endif

        <div class="pt-5 border-t border-stone-200 flex items-center justify-end gap-3">
            <a href="{{ route('rooms.index') }}"
               class="px-4 py-2.5 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-amber-800 text-white text-xs font-semibold hover:bg-amber-900 transition">
                {{ $isEdit ? 'Simpan Perubahan Unit' : 'Simpan Unit Kamar' }}
            </button>
        </div>
    </div>

    <!-- KOLOM KANAN: Live Preview Kartu Unit (5 Kolom) -->
    <div class="lg:col-span-5 bg-white border border-stone-200 rounded-2xl overflow-hidden lg:sticky lg:top-24">
        <div class="relative aspect-[16/10] bg-stone-200 overflow-hidden">
            <img :src="activeType.cover"
                 alt="Pratinjau Tipe Kamar"
                 class="w-full h-full object-cover transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-950/15 to-transparent"></div>

            <div class="absolute top-3 left-3">
                <span class="px-2.5 py-1 rounded-md bg-stone-950/75 backdrop-blur-xs text-amber-200 font-mono text-xs font-semibold border border-white/10"
                      x-text="'Kamar ' + (roomNumber || '---')">
                </span>
            </div>

            <div class="absolute bottom-3.5 left-4 right-4 flex items-end justify-between text-white">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-stone-300" x-text="'Lantai ' + (floor || 1)"></p>
                    <h4 class="font-editorial text-2xl text-amber-50 leading-tight mt-0.5" x-text="activeType.name"></h4>
                </div>
                <div class="text-right tabular-nums">
                    <p class="text-sm font-bold text-white" x-text="activeType.price"></p>
                    <p class="text-[10px] text-stone-300">/ malam</p>
                </div>
            </div>
        </div>

        <div class="p-5 flex items-center justify-between text-xs text-stone-600">
            <span>Kapasitas: <strong class="text-stone-900" x-text="'Maks. ' + activeType.capacity + ' Tamu'"></strong></span>
            <span class="uppercase font-semibold tracking-wider text-amber-900" x-text="status"></span>
        </div>
    </div>
</div>