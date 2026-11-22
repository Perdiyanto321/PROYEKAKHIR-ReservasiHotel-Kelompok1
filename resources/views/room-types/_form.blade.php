<div class="grid grid-cols-1 md:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Nama Tipe Kamar</label>
        <input type="text" name="name" value="{{ old('name', $roomType->name) }}"
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Tarif Dasar per Malam (Rp)</label>
        <input type="number" name="base_price" value="{{ old('base_price', $roomType->base_price) }}"
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('base_price') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">Kapasitas Maksimal (Orang)</label>
        <input type="number" name="capacity" value="{{ old('capacity', $roomType->capacity ?? 2) }}"
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('capacity') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-stone-700 mb-1">URL Foto Kamar (HTTPS)</label>
        <input type="url" name="image_url" value="{{ old('image_url', $roomType->image_url) }}" placeholder="https://images.unsplash.com/..."
               class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        @error('image_url') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-stone-700 mb-1">Deskripsi Kamar</label>
        <textarea name="description" rows="3" class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">{{ old('description', $roomType->description) }}</textarea>
        @error('description') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-stone-700 mb-2">Fasilitas Kamar</label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
            @php $checkedIds = old('facilities', $selectedFacilities); @endphp
            @foreach($facilities as $facility)
                <label class="flex items-center gap-2 p-2.5 rounded-lg border border-stone-200 bg-stone-50/60 cursor-pointer text-sm">
                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}"
                           @checked(in_array($facility->id, $checkedIds))
                           class="rounded border-stone-300 text-amber-800 focus:ring-amber-800">
                    <span>{{ $facility->name }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>