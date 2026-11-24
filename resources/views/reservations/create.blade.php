@extends('layouts.app')

@section('header', 'Formulir Reservasi Kamar')

@section('content')
@php
    $fallbackImg = 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';
    $roomsPayload = $rooms->map(function ($room) use ($fallbackImg) {
        $type = $room->roomType;
        return [
            'id' => (string) $room->id,
            'room_number' => $room->room_number,
            'floor' => $room->floor ?? 1,
            'type_name' => $type?->name ?? 'Standard Suite',
            'description' => $type?->description ?? 'Kamar dengan pencahayaan alami, perlengkapan tidur premium, dan kenyamanan modern khas Svara Hotel.',
            'base_price' => (float) ($type?->base_price ?? 0),
            'capacity' => (int) ($type?->capacity ?? 2),
            'cover_url' => $type?->cover_url ?? $fallbackImg,
            'facilities' => $type?->facilities ? $type->facilities->pluck('name')->values()->all() : [],
        ];
    })->values();

    $initialRoomId = (string) old('room_id', $selectedRoomId ?? ($rooms->first()?->id ?? ''));
    $initialCheckIn = old('check_in_date', $defaultCheckIn ?? now()->toDateString());
    $initialCheckOut = old('check_out_date', $defaultCheckOut ?? now()->addDay()->toDateString());
@endphp

<div
    x-data="reservationBookingForm({
        rooms: @js($roomsPayload),
        initialRoomId: @js($initialRoomId),
        initialCheckIn: @js($initialCheckIn),
        initialCheckOut: @js($initialCheckOut),
        fallbackImg: @js($fallbackImg)
    })"
    class="max-w-6xl mx-auto space-y-6"
>
    <!-- Header Editorial -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-800">Reservasi Akomodasi</p>
            <h1 class="font-editorial text-3xl sm:text-4xl text-stone-900 mt-0.5">Buat Reservasi Baru</h1>
            <p class="text-xs text-stone-500 mt-1">Pilih unit kamar yang tersedia, tentukan tanggal menginap, dan periksa estimasi biaya sebelum konfirmasi.</p>
        </div>

        <a
            href="{{ route('reservations.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-stone-200 text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
        >
            <span>&larr;</span>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <!-- Alert Error Validasi -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs space-y-1">
            <p class="font-semibold">Mohon periksa kembali data reservasi Anda:</p>
            <ul class="list-disc list-inside space-y-0.5 text-rose-800">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reservations.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
        @csrf

        <!-- KOLOM KIRI: FORM INPUT RESERVASI -->
        <div class="lg:col-span-7 bg-white border border-stone-200 rounded-2xl p-6 sm:p-7 space-y-6">
            <!-- Bagian 1: Identitas Pemesan & Unit Kamar -->
            <div class="space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-stone-100">
                    <span class="w-6 h-6 rounded-full bg-[#181512] text-amber-300 text-[11px] font-bold flex items-center justify-center">1</span>
                    <h2 class="text-sm font-semibold text-stone-900">Pilihan Unit Kamar & Pemesan</h2>
                </div>

                @if(in_array(auth()->user()->role ?? '', ['admin', 'resepsionis']) && $guests->isNotEmpty())
                    <div>
                        <label for="user_id" class="block text-xs font-semibold text-stone-700 mb-1.5">Akun Tamu Pemesan</label>
                        <select
                            id="user_id"
                            name="user_id"
                            class="w-full rounded-xl border-stone-300 text-xs font-medium text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                        >
                            @foreach($guests as $guest)
                                <option value="{{ $guest->id }}" @selected(old('user_id', auth()->id()) == $guest->id)>
                                    {{ $guest->name }} ({{ $guest->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-200/80 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-400">Tamu Pemesan</p>
                            <p class="text-xs font-semibold text-stone-900 mt-0.5">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-stone-500">{{ auth()->user()->email }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-[10px] font-semibold uppercase tracking-wider text-emerald-800">
                            Terverifikasi
                        </span>
                    </div>
                @endif

                <div>
                    <label for="room_id" class="block text-xs font-semibold text-stone-700 mb-1.5">
                        Pilih Unit Kamar Tersedia <span class="text-rose-600">*</span>
                    </label>
                    <select
                        id="room_id"
                        name="room_id"
                        x-model="selectedRoomId"
                        required
                        class="w-full rounded-xl border-stone-300 text-xs font-medium text-stone-800 focus:border-amber-800 focus:ring-amber-800"
                    >
                        @forelse($rooms as $room)
                            <option value="{{ $room->id }}">
                                Kamar {{ $room->room_number }} (Lantai {{ $room->floor ?? 1 }}) — {{ $room->roomType?->name }} (Rp {{ number_format($room->roomType?->base_price ?? 0, 0, ',', '.') }}/malam)
                            </option>
                        @empty
                            <option value="">Tidak ada unit kamar tersedia saat ini</option>
                        @endforelse
                    </select>
                    <p class="text-[11px] text-stone-400 mt-1.5">Hanya menampilkan unit kamar yang berstatus siap huni.</p>
                </div>
            </div>

            <!-- Bagian 2: Jadwal Check-In & Check-Out -->
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-2 pb-3 border-b border-stone-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-[#181512] text-amber-300 text-[11px] font-bold flex items-center justify-center">2</span>
                        <h2 class="text-sm font-semibold text-stone-900">Jadwal Menginap</h2>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200 text-[11px] font-semibold text-amber-900 tabular-nums">
                        Durasi: <span x-text="nights"></span> Malam
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="check_in_date" class="block text-xs font-semibold text-stone-700 mb-1.5">
                            Tanggal Check-In <span class="text-rose-600">*</span>
                        </label>
                        <input
                            type="date"
                            id="check_in_date"
                            name="check_in_date"
                            x-model="checkIn"
                            :min="todayStr"
                            @change="syncCheckOut()"
                            required
                            class="w-full rounded-xl border-stone-300 text-xs font-medium text-stone-800 tabular-nums focus:border-amber-800 focus:ring-amber-800"
                        />
                        <p class="text-[11px] text-stone-400 mt-1">Waktu check-in mulai pukul <strong>14:00 WIB</strong></p>
                    </div>

                    <div>
                        <label for="check_out_date" class="block text-xs font-semibold text-stone-700 mb-1.5">
                            Tanggal Check-Out <span class="text-rose-600">*</span>
                        </label>
                        <input
                            type="date"
                            id="check_out_date"
                            name="check_out_date"
                            x-model="checkOut"
                            :min="minCheckOutStr"
                            @change="validateCheckOut()"
                            required
                            class="w-full rounded-xl border-stone-300 text-xs font-medium text-stone-800 tabular-nums focus:border-amber-800 focus:ring-amber-800"
                        />
                        <p class="text-[11px] text-stone-400 mt-1">Maksimal check-out pukul <strong>12:00 WIB</strong></p>
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Permintaan Khusus -->
            <div class="space-y-3">
                <div class="flex items-center gap-2.5 pb-3 border-b border-stone-100">
                    <span class="w-6 h-6 rounded-full bg-[#181512] text-amber-300 text-[11px] font-bold flex items-center justify-center">3</span>
                    <h2 class="text-sm font-semibold text-stone-900">Catatan & Permintaan Khusus</h2>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-semibold text-stone-700 mb-1.5">Catatan Tambahan (Opsional)</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        placeholder="Contoh: Permintaan early check-in, tambahan bantal, atau perkiraan jam tiba..."
                        class="w-full rounded-xl border-stone-300 text-xs text-stone-800 placeholder:text-stone-400 focus:border-amber-800 focus:ring-amber-800"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-stone-100 flex flex-wrap items-center justify-end gap-3">
                <a
                    href="{{ route('reservations.index') }}"
                    class="px-4 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    @disabled($rooms->isEmpty())
                    class="group inline-flex items-center gap-2.5 pl-2.5 pr-5 py-2 rounded-xl bg-[#181512] border border-stone-800 text-amber-50 text-xs font-semibold tracking-wide hover:bg-stone-900 hover:border-amber-700/60 disabled:opacity-50 transition"
                >
                    <span class="w-7 h-7 rounded-lg bg-amber-500/15 border border-amber-400/30 text-amber-300 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </span>
                    <span>Konfirmasi & Buat Reservasi</span>
                </button>
            </div>
        </div>

        <!-- KOLOM KANAN: RINGKASAN SUITE & KALKULASI TARIF LIVE -->
        <aside class="lg:col-span-5 lg:sticky lg:top-24 bg-white border border-stone-200 rounded-2xl overflow-hidden shadow-[0_12px_30px_-12px_rgba(24,21,18,0.08)]">
            <template x-if="currentRoom">
                <div>
                    <!-- Pratinjau Foto Kamar -->
                    <div class="relative h-52 bg-stone-200 overflow-hidden">
                        <img
                            :src="currentRoom.cover_url"
                            :alt="currentRoom.type_name"
                            x-on:error="$event.target.onerror = null; $event.target.src = fallbackImg"
                            class="w-full h-full object-cover"
                        />
                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/90 via-stone-950/25 to-black/15"></div>

                        <div class="absolute top-3.5 left-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-950/90 text-emerald-200 text-[10px] font-semibold uppercase tracking-wider border border-emerald-500/35">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>Unit Siap Huni</span>
                            </span>
                        </div>

                        <div class="absolute top-3.5 right-3.5">
                            <span class="px-2.5 py-1 rounded-md bg-white/95 text-stone-900 text-[11px] font-semibold tabular-nums">
                                Maks. <span x-text="currentRoom.capacity"></span> Tamu
                            </span>
                        </div>

                        <div class="absolute bottom-3.5 left-4 right-4 text-white">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-amber-300">
                                Kamar <span x-text="currentRoom.room_number"></span> &bull; Lantai <span x-text="currentRoom.floor"></span>
                            </p>
                            <h3 class="font-editorial text-2xl text-amber-50 leading-tight mt-0.5" x-text="currentRoom.type_name"></h3>
                        </div>
                    </div>

                    <!-- Rincian Fasilitas & Kalkulasi Harga -->
                    <div class="p-5 sm:p-6 space-y-5">
                        <p class="text-xs text-stone-600 leading-relaxed" x-text="currentRoom.description"></p>

                        <div x-show="currentRoom.facilities.length > 0" class="flex flex-wrap gap-1.5">
                            <template x-for="fac in currentRoom.facilities" :key="fac">
                                <span class="px-2.5 py-1 rounded-md bg-stone-100 border border-stone-200/80 text-[11px] font-medium text-stone-700" x-text="fac"></span>
                            </template>
                        </div>

                        <div class="p-4 rounded-xl bg-stone-50 border border-stone-200/90 space-y-2.5 text-xs tabular-nums">
                            <div class="flex items-center justify-between text-stone-600">
                                <span>Check-In</span>
                                <span class="font-semibold text-stone-900" x-text="formatDateIndo(checkIn) + ' (14:00)'"></span>
                            </div>
                            <div class="flex items-center justify-between text-stone-600">
                                <span>Check-Out</span>
                                <span class="font-semibold text-stone-900" x-text="formatDateIndo(checkOut) + ' (12:00)'"></span>
                            </div>
                            <div class="flex items-center justify-between text-stone-600">
                                <span>Tarif per Malam</span>
                                <span class="font-medium text-stone-900" x-text="formatRupiah(currentRoom.base_price)"></span>
                            </div>
                            <div class="flex items-center justify-between text-stone-600">
                                <span>Durasi Menginap</span>
                                <span class="font-medium text-stone-900"><span x-text="nights"></span> Malam</span>
                            </div>

                            <div class="pt-3 border-t border-stone-200 flex items-baseline justify-between">
                                <span class="text-xs font-semibold text-stone-800">Total Estimasi</span>
                                <span class="text-xl font-bold text-amber-900" x-text="formatRupiah(totalPrice)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!currentRoom">
                <div class="p-8 text-center text-xs text-stone-500">
                    Tidak ada unit kamar yang tersedia untuk dipilih.
                </div>
            </template>
        </aside>
    </form>
</div>

<script>
function reservationBookingForm({ rooms, initialRoomId, initialCheckIn, initialCheckOut, fallbackImg }) {
    const toLocalDateStr = (dateObj) => {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const addDays = (dateStr, days) => {
        const [y, m, d] = dateStr.split('-').map(Number);
        const dt = new Date(y, m - 1, d);
        dt.setDate(dt.getDate() + days);
        return toLocalDateStr(dt);
    };

    const todayStr = toLocalDateStr(new Date());
    const validCheckIn = initialCheckIn && initialCheckIn >= todayStr ? initialCheckIn : todayStr;
    const minOut = addDays(validCheckIn, 1);
    const validCheckOut = initialCheckOut && initialCheckOut >= minOut ? initialCheckOut : minOut;

    return {
        rooms,
        fallbackImg,
        todayStr,
        selectedRoomId: String(initialRoomId || (rooms[0]?.id ?? '')),
        checkIn: validCheckIn,
        checkOut: validCheckOut,

        get currentRoom() {
            return this.rooms.find((r) => String(r.id) === String(this.selectedRoomId)) || this.rooms[0] || null;
        },

        get minCheckOutStr() {
            return addDays(this.checkIn || this.todayStr, 1);
        },

        get nights() {
            if (!this.checkIn || !this.checkOut) return 1;
            const [y1, m1, d1] = this.checkIn.split('-').map(Number);
            const [y2, m2, d2] = this.checkOut.split('-').map(Number);
            const start = new Date(y1, m1 - 1, d1);
            const end = new Date(y2, m2 - 1, d2);
            const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
            return diff > 0 ? diff : 1;
        },

        get totalPrice() {
            if (!this.currentRoom) return 0;
            return this.currentRoom.base_price * this.nights;
        },

        syncCheckOut() {
            if (!this.checkIn || this.checkIn < this.todayStr) {
                this.checkIn = this.todayStr;
            }
            const requiredMinOut = addDays(this.checkIn, 1);
            if (!this.checkOut || this.checkOut < requiredMinOut) {
                this.checkOut = requiredMinOut;
            }
        },

        validateCheckOut() {
            const requiredMinOut = addDays(this.checkIn, 1);
            if (!this.checkOut || this.checkOut < requiredMinOut) {
                this.checkOut = requiredMinOut;
            }
        },

        formatRupiah(amount) {
            return 'Rp ' + Math.round(Number(amount || 0)).toLocaleString('id-ID');
        },

        formatDateIndo(dateStr) {
            if (!dateStr) return '-';
            const [y, m, d] = dateStr.split('-').map(Number);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${String(d).padStart(2, '0')} ${months[m - 1]} ${y}`;
        }
    };
}
</script>
@endsection