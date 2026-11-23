@extends('layouts.app')
@section('title', 'Detail Reservasi ' . $reservation->booking_code)
@section('header', 'Detail Reservasi #' . $reservation->booking_code)

@section('content')
@php
    $room = $reservation->room;
    $roomType = $room->roomType;

    // Resolusi gambar kamar (dari DB jika tersedia, atau fallback kurasi sesuai tipe kamar)
    $rawImage = $roomType->image_url ?? $roomType->image ?? null;
    if ($rawImage) {
        $roomImage = \Illuminate\Support\Str::startsWith($rawImage, ['http://', 'https://'])
            ? $rawImage
            : asset('storage/' . ltrim($rawImage, '/'));
    } else {
        $typeName = strtolower($roomType->name);
        $roomImage = match (true) {
            str_contains($typeName, 'suite') || str_contains($typeName, 'executive')
                => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1200&q=80',
            str_contains($typeName, 'superior')
                => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1200&q=80',
            default
                => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80',
        };
    }

    // Tahapan progres reservasi
    $steps = [
        'pending'     => ['step' => 1, 'label' => 'Menunggu Konfirmasi'],
        'confirmed'   => ['step' => 2, 'label' => 'Terkonfirmasi'],
        'checked_in'  => ['step' => 3, 'label' => 'Checked-In'],
        'checked_out' => ['step' => 4, 'label' => 'Checked-Out'],
    ];
    $currentStep = $steps[$reservation->status]['step'] ?? 0;
@endphp

<!-- Navigasi Kembali -->
<div class="mb-5 flex items-center justify-between gap-4">
    <a href="{{ route('reservations.index') }}"
       class="inline-flex items-center gap-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Daftar Reservasi</span>
    </a>

    <span class="text-xs text-stone-400 tabular-nums">
        Dibuat {{ $reservation->created_at->format('d M Y, H:i') }}
    </span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <!-- KOLOM KIRI: Kartu Folio & Rincian Reservasi (7 Kolom) -->
    <div class="lg:col-span-7 bg-white border border-stone-200 rounded-2xl overflow-hidden">
        <!-- Header Kode Pemesanan & Status -->
        <div class="p-6 sm:p-7 border-b border-stone-200 flex flex-wrap items-start justify-between gap-4 bg-stone-50/40">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-stone-400">Kode Pemesanan</p>
                <div class="mt-1.5 flex items-center gap-3">
                    <h2 class="font-mono text-2xl sm:text-3xl font-bold tracking-tight text-stone-900">
                        {{ $reservation->booking_code }}
                    </h2>
                </div>
                <p class="mt-1.5 text-xs text-stone-500">
                    Dipesan oleh <span class="font-semibold text-stone-800">{{ $reservation->user->name }}</span>
                    &bull; {{ $reservation->user->email }}
                </p>
            </div>

            <x-status-badge :status="$reservation->status" class="px-3 py-1.5 text-xs" />
        </div>

        <!-- Stepper Status Perjalanan Menginap -->
        <div class="px-6 sm:px-7 py-4 border-b border-stone-200 bg-white">
            @if($reservation->status === 'cancelled')
                <div class="flex items-center gap-2.5 text-xs text-rose-800 bg-rose-50/70 border border-rose-200/80 rounded-xl px-4 py-3">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span>Reservasi ini telah <strong>dibatalkan</strong> dan unit kamar telah dikembalikan ke status tersedia.</span>
                </div>
            @else
                <div class="grid grid-cols-4 gap-2">
                    @foreach($steps as $key => $info)
                        @php
                            $isDone = $currentStep >= $info['step'];
                            $isCurrent = $currentStep === $info['step'];
                        @endphp
                        <div>
                            <div class="h-1.5 rounded-full transition-colors {{ $isDone ? 'bg-amber-800' : 'bg-stone-200' }}"></div>
                            <p class="mt-2 text-[11px] leading-tight {{ $isCurrent ? 'font-semibold text-stone-900' : ($isDone ? 'font-medium text-stone-600' : 'text-stone-400') }}">
                                {{ $info['label'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Jadwal Check-In & Check-Out -->
        <div class="p-6 sm:p-7 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-stone-50/80 border border-stone-200/80">
                    <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-stone-400">
                        <svg class="w-3.5 h-3.5 text-amber-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <span>Jadwal Check-In</span>
                    </div>
                    <p class="mt-2 text-base font-bold text-stone-900 tabular-nums">
                        {{ $reservation->check_in_date->translatedFormat('d F Y') }}
                    </p>
                    <p class="mt-0.5 text-xs text-stone-500">Mulai pukul 14:00 WIB</p>
                </div>

                <div class="p-4 rounded-xl bg-stone-50/80 border border-stone-200/80">
                    <div class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-wider text-stone-400">
                        <svg class="w-3.5 h-3.5 text-amber-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        <span>Jadwal Check-Out</span>
                    </div>
                    <p class="mt-2 text-base font-bold text-stone-900 tabular-nums">
                        {{ $reservation->check_out_date->translatedFormat('d F Y') }}
                    </p>
                    <p class="mt-0.5 text-xs text-stone-500">Maksimal pukul 12:00 WIB</p>
                </div>
            </div>

            <!-- Permintaan Khusus -->
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-2">Permintaan Khusus Tamu</p>
                @if($reservation->special_requests)
                    <div class="p-4 rounded-xl bg-amber-50/40 border border-amber-200/80 text-sm text-stone-800 flex items-start gap-3">
                        <svg class="w-4 h-4 text-amber-800 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6m-6 8l4-4h6a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h2v4z"/>
                        </svg>
                        <p class="leading-relaxed">{{ $reservation->special_requests }}</p>
                    </div>
                @else
                    <p class="text-sm text-stone-400 italic">Tidak ada catatan permintaan khusus untuk reservasi ini.</p>
                @endif
            </div>

            <!-- Rincian Folio Pembayaran -->
            <div class="pt-5 border-t border-stone-200">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-3">Rincian Biaya Menginap</p>
                <div class="space-y-2.5 text-sm">
                    <div class="flex items-center justify-between text-stone-600 tabular-nums">
                        <span>Rp {{ number_format($roomType->base_price, 0, ',', '.') }} &times; {{ $reservation->total_nights }} malam ({{ $roomType->name }})</span>
                        <span class="font-medium text-stone-900">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-stone-500 text-xs">
                        <span>Pajak & Biaya Layanan Hotel</span>
                        <span>Sudah Termasuk</span>
                    </div>
                    <div class="pt-3 border-t border-dashed border-stone-200 flex items-baseline justify-between">
                        <span class="text-sm font-semibold text-stone-900">Total Pembayaran</span>
                        <span class="text-2xl font-bold text-amber-900 tabular-nums">
                            Rp {{ number_format($reservation->total_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Aksi Operasional -->
        <div class="px-6 sm:px-7 py-4 bg-stone-50/70 border-t border-stone-200 flex flex-wrap items-center justify-between gap-3">
            <!-- Aksi Destruktif (Hapus / Batalkan) -->
            <div class="flex items-center gap-2.5">
                @can('delete', $reservation)
                    <form action="{{ route('reservations.destroy', $reservation) }}" method="POST"
                          onsubmit="return confirm('Hapus permanen data reservasi {{ $reservation->booking_code }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 rounded-lg border border-rose-200 bg-white text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                            Hapus Permanen
                        </button>
                    </form>
                @endcan

                @cannot('update', $reservation)
                    @can('cancel', $reservation)
                        <form action="{{ route('reservations.cancel', $reservation) }}" method="POST"
                              onsubmit="return confirm('Batalkan reservasi {{ $reservation->booking_code }}?')">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg border border-rose-200 bg-white text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                Batalkan Reservasi
                            </button>
                        </form>
                    @endcan
                @endcannot
            </div>

            <!-- Aksi Transisi Status untuk Staf -->
            <div class="flex flex-wrap items-center gap-2.5 ml-auto">
                @can('update', $reservation)
                    @if(in_array($reservation->status, ['pending', 'confirmed'], true))
                        <form action="{{ route('reservations.status', $reservation) }}" method="POST"
                              onsubmit="return confirm('Batalkan reservasi {{ $reservation->booking_code }}?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-100 transition">
                                Batalkan
                            </button>
                        </form>
                    @endif

                    @if($reservation->status === 'pending')
                        <form action="{{ route('reservations.status', $reservation) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-emerald-800 text-white text-xs font-semibold hover:bg-emerald-900 transition">
                                Konfirmasi Reservasi
                            </button>
                        </form>
                    @elseif($reservation->status === 'confirmed')
                        <form action="{{ route('reservations.status', $reservation) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="checked_in">
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-amber-800 text-white text-xs font-semibold hover:bg-amber-900 transition">
                                Proses Check-In
                            </button>
                        </form>
                    @elseif($reservation->status === 'checked_in')
                        <form action="{{ route('reservations.status', $reservation) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="checked_out">
                            <button type="submit"
                                    class="px-5 py-2 rounded-lg bg-stone-900 text-white text-xs font-semibold hover:bg-stone-800 transition">
                                Proses Check-Out
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: Kartu Visual & Spesifikasi Kamar (5 Kolom) -->
    <div class="lg:col-span-5 bg-white border border-stone-200 rounded-2xl overflow-hidden lg:sticky lg:top-24">
        <!-- Foto Kamar -->
        <div class="relative aspect-[16/10] bg-stone-200 overflow-hidden">
            <img src="{{ $roomImage }}"
                 alt="Kamar {{ $room->room_number }} - {{ $roomType->name }}"
                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=80';"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-950/75 via-stone-950/15 to-transparent"></div>

            <div class="absolute top-3.5 left-3.5">
                <span class="px-2.5 py-1 rounded-md bg-stone-950/75 backdrop-blur-xs text-amber-200 text-[11px] font-semibold tracking-wider uppercase border border-white/10">
                    Unit Kamar {{ $room->room_number }}
                </span>
            </div>

            <div class="absolute bottom-3.5 left-4 right-4 flex items-end justify-between text-white">
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-stone-300">Lantai {{ $room->floor }}</p>
                    <h3 class="font-editorial text-2xl text-amber-50 leading-tight mt-0.5">{{ $roomType->name }}</h3>
                </div>
                <div class="text-right tabular-nums">
                    <p class="text-sm font-bold text-white">Rp {{ number_format($roomType->base_price, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-stone-300">per malam</p>
                </div>
            </div>
        </div>

        <!-- Detail & Fasilitas Kamar -->
        <div class="p-5 sm:p-6 space-y-5">
            <div class="grid grid-cols-2 gap-3 pb-4 border-b border-stone-100 text-xs">
                <div class="flex items-center gap-2.5 text-stone-600">
                    <div class="w-8 h-8 rounded-lg bg-stone-100 text-stone-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-stone-400 font-semibold">Kapasitas</p>
                        <p class="font-semibold text-stone-900">Maks. {{ $roomType->capacity ?? 2 }} Tamu</p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 text-stone-600">
                    <div class="w-8 h-8 rounded-lg bg-stone-100 text-stone-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path d="M2 20v-8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v8"/><path d="M4 10V6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v4"/><path d="M2 16h20"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider text-stone-400 font-semibold">Status Fisik</p>
                        <p class="font-semibold text-stone-900 capitalize">{{ $room->status }}</p>
                    </div>
                </div>
            </div>

            @if($roomType->description)
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-1.5">Deskripsi Suite</p>
                    <p class="text-xs text-stone-600 leading-relaxed">{{ $roomType->description }}</p>
                </div>
            @endif

            @if($roomType->facilities && $roomType->facilities->isNotEmpty())
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-400 mb-2">Fasilitas Kamar</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($roomType->facilities as $facility)
                            <span class="px-2.5 py-1 rounded-md bg-stone-100/90 border border-stone-200/80 text-[11px] font-medium text-stone-700">
                                {{ $facility->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            @can('operasional-frontdesk')
                <div class="pt-4 border-t border-stone-100">
                    <a href="{{ route('rooms.show', $room) }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50/70 text-xs font-semibold text-stone-700 hover:bg-stone-100 hover:border-stone-300 transition">
                        <span>Lihat Detail Unit Kamar {{ $room->room_number }}</span>
                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection