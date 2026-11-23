@php
    $isEdit = isset($roomType);
    $selectedFacilities = old('facilities', $isEdit ? $roomType->facilities->pluck('id')->toArray() : []);
    $initialPreview = $isEdit ? $roomType->cover_url : 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';
@endphp

<div x-data="{
        price: {{ (int) old('base_price', $isEdit ? $roomType->base_price : 750000) }},
        name: @js(old('name', $isEdit ? $roomType->name : 'Deluxe Garden Suite')),
        capacity: {{ (int) old('capacity', $isEdit ? $roomType->capacity : 2) }},
        previewUrl: @js($initialPreview),
        hasNewFile: false,
        fileName: '',
        removeExisting: false,
        formatRupiah(val) {
            const num = parseInt(val || 0, 10);
            return 'Rp ' + num.toLocaleString('id-ID');
        },
        handleFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.previewUrl = URL.createObjectURL(file);
            this.hasNewFile = true;
            this.fileName = file.name;
            this.removeExisting = false;
        },
        clearNewFile() {
            this.$refs.imageInput.value = '';
            this.previewUrl = @js($initialPreview);
            this.hasNewFile = false;
            this.fileName = '';
        }
     }"
     class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- KOLOM KIRI: Spesifikasi & Fasilitas (7 Kolom) -->
    <div class="lg:col-span-7 bg-white border border-stone-200 rounded-2xl p-6 sm:p-7 space-y-5">
        <div class="pb-4 border-b border-stone-100">
            <h2 class="text-base font-semibold text-stone-900">
                {{ $isEdit ? 'Perbarui Spesifikasi Tipe Kamar' : 'Informasi Tipe Kamar Baru' }}
            </h2>
            <p class="text-xs text-stone-500 mt-0.5">Tentukan nama kategori, tarif dasar per malam, kapasitas maksimal, dan fasilitas unggulan.</p>
        </div>

        <div>
            <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Nama Tipe Kamar</label>
            <input type="text"
                   id="name"
                   name="name"
                   x-model="name"
                   required
                   placeholder="Contoh: Executive Svara Suite"
                   class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="base_price" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Tarif Dasar / Malam (Rp)</label>
                <input type="number"
                       id="base_price"
                       name="base_price"
                       x-model="price"
                       min="0"
                       step="1000"
                       required
                       class="w-full rounded-lg border-stone-300 text-sm tabular-nums focus:border-amber-800 focus:ring-amber-800">
                <p class="mt-1 text-[11px] text-amber-800 font-medium tabular-nums" x-text="'Pratinjau tarif: ' + formatRupiah(price) + ' / malam'"></p>
            </div>

            <div>
                <label for="capacity" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Kapasitas Maksimal (Tamu)</label>
                <input type="number"
                       id="capacity"
                       name="capacity"
                       x-model="capacity"
                       min="1"
                       max="20"
                       required
                       class="w-full rounded-lg border-stone-300 text-sm tabular-nums focus:border-amber-800 focus:ring-amber-800">
                <p class="mt-1 text-[11px] text-stone-400">Jumlah tamu dewasa yang diizinkan per unit</p>
            </div>
        </div>

        <div>
            <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-stone-600 mb-1.5">Deskripsi Editorial Kamar</label>
            <textarea id="description"
                      name="description"
                      rows="3"
                      placeholder="Gambarkan suasana interior, pemandangan jendela, ukuran tempat tidur, dan keunggulan utama suite ini..."
                      class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">{{ old('description', $isEdit ? $roomType->description : '') }}</textarea>
        </div>

        <!-- Pilihan Fasilitas Interaktif -->
        <div class="pt-2">
            <div class="flex items-center justify-between mb-2.5">
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-600">Fasilitas Termasuk</label>
                <span class="text-[11px] text-stone-400">Pilih satu atau lebih fasilitas</span>
            </div>

            @if($facilities->isEmpty())
                <p class="text-xs text-stone-400 italic">Belum ada master fasilitas di database.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @foreach($facilities as $facility)
                        <label class="cursor-pointer">
                            <input type="checkbox"
                                   name="facilities[]"
                                   value="{{ $facility->id }}"
                                   @checked(in_array($facility->id, $selectedFacilities))
                                   class="peer sr-only">
                            <div class="px-3 py-2.5 rounded-xl border border-stone-200 bg-stone-50/50 text-xs font-medium text-stone-700 peer-checked:border-amber-800 peer-checked:bg-amber-50/70 peer-checked:text-amber-950 peer-checked:font-semibold transition flex items-center justify-between gap-2">
                                <span class="truncate">{{ $facility->name }}</span>
                                <span class="w-4 h-4 rounded-full border border-stone-300 flex items-center justify-center shrink-0">
                                    <svg class="w-2.5 h-2.5 text-amber-800" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </span>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tombol Submit -->
        <div class="pt-5 border-t border-stone-200 flex items-center justify-end gap-3">
            <a href="{{ route('room-types.index') }}"
               class="px-4 py-2.5 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-amber-800 text-white text-xs font-semibold hover:bg-amber-900 transition">
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Tipe Kamar' }}
            </button>
        </div>
    </div>

    <!-- KOLOM KANAN: Upload Foto Lokal & Live Card Preview (5 Kolom) -->
    <div class="lg:col-span-5 bg-white border border-stone-200 rounded-2xl overflow-hidden lg:sticky lg:top-24">
        <div class="p-5 border-b border-stone-100">
            <h3 class="text-sm font-semibold text-stone-900">Foto Sampul Tipe Kamar</h3>
            <p class="text-xs text-stone-500 mt-0.5">Format JPG, PNG, atau WEBP (maks. 2 MB). Tersimpan lokal di server.</p>
        </div>

        <!-- Live Preview Card -->
        <div class="relative aspect-[16/10] bg-stone-200 overflow-hidden">
            <img :src="previewUrl"
                 alt="Pratinjau Foto Kamar"
                 class="w-full h-full object-cover transition-all duration-300">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-950/15 to-transparent"></div>

            <div class="absolute top-3 left-3">
                <span class="px-2.5 py-1 rounded-md bg-stone-950/75 backdrop-blur-xs text-amber-200 text-[10px] font-semibold uppercase tracking-wider border border-white/10"
                      x-text="hasNewFile ? 'Pratinjau Foto Baru' : '{{ $isEdit && $roomType->has_local_image ? 'Foto Lokal Tersimpan' : 'Pratinjau Kartu' }}'">
                </span>
            </div>

            <div class="absolute bottom-3.5 left-4 right-4 flex items-end justify-between text-white">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-stone-300" x-text="'Maks. ' + (capacity || 1) + ' Tamu'"></p>
                    <h4 class="font-editorial text-2xl text-amber-50 leading-tight mt-0.5" x-text="name || 'Nama Tipe Kamar'"></h4>
                </div>
                <div class="text-right tabular-nums">
                    <p class="text-sm font-bold text-white" x-text="formatRupiah(price)"></p>
                    <p class="text-[10px] text-stone-300">/ malam</p>
                </div>
            </div>
        </div>

        <!-- Dropzone Input File -->
        <div class="p-5 space-y-3">
            <label for="image"
                   class="block border-2 border-dashed border-stone-300 hover:border-amber-800 rounded-xl p-5 text-center cursor-pointer bg-stone-50/60 hover:bg-amber-50/20 transition">
                <input type="file"
                       id="image"
                       name="image"
                       x-ref="imageInput"
                       @change="handleFile($event)"
                       accept="image/jpeg,image/png,image/webp"
                       class="sr-only">
                <div class="w-10 h-10 rounded-full bg-amber-100/80 text-amber-800 mx-auto flex items-center justify-center mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-stone-800" x-text="hasNewFile ? fileName : 'Klik untuk memilih foto dari perangkat'"></p>
                <p class="text-[11px] text-stone-500 mt-1">Rasio lanskap 16:10 direkomendasikan</p>
            </label>

            <div x-show="hasNewFile" style="display: none;" class="flex items-center justify-between text-xs bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                <span class="text-amber-900 truncate pr-2">File siap diunggah saat disimpan.</span>
                <button type="button" @click="clearNewFile()" class="font-semibold text-rose-700 hover:underline shrink-0">Batal Pilih</button>
            </div>

            @if($isEdit && $roomType->has_local_image)
                <label class="flex items-center gap-2 text-xs text-stone-600 pt-1 cursor-pointer">
                    <input type="checkbox" name="remove_image" value="1" x-model="removeExisting" class="rounded border-stone-300 text-rose-700 focus:ring-rose-700">
                    <span>Hapus foto lokal yang tersimpan saat ini</span>
                </label>
            @endif
        </div>
    </div>
</div>