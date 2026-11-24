<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $reservation->booking_code }} — Svara Hotel & Suites</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #FAF9F6;
            color: #1C1917;
        }
        .font-editorial {
            font-family: 'Instrument Serif', serif;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm;
            }
            body {
                background-color: #FFFFFF !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .print-sheet {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen py-8 px-4 sm:px-6">
    @php
        $room = $reservation->room;
        $roomType = $room->roomType;
        $statusLabels = [
            'pending'     => ['label' => 'Menunggu Konfirmasi', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200'],
            'confirmed'   => ['label' => 'Terkonfirmasi',       'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
            'checked_in'  => ['label' => 'Checked-In',          'badge' => 'bg-sky-50 text-sky-800 border-sky-200'],
            'checked_out' => ['label' => 'Lunas / Selesai',     'badge' => 'bg-stone-100 text-stone-800 border-stone-300'],
            'cancelled'   => ['label' => 'Dibatalkan',          'badge' => 'bg-rose-50 text-rose-800 border-rose-200'],
        ];
        $currentStatus = $statusLabels[$reservation->status] ?? ['label' => strtoupper($reservation->status), 'badge' => 'bg-stone-100 text-stone-800 border-stone-200'];
    @endphp

    <!-- Bar Navigasi Layar (Otomatis Hilang Saat Dicetak) -->
    <div class="no-print max-w-3xl mx-auto mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('reservations.show', $reservation) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-stone-200 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Detail Reservasi</span>
        </a>

        <button type="button"
                onclick="window.print()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-800 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
            </svg>
            <span>Cetak / Simpan PDF</span>
        </button>
    </div>

    <!-- Lembar Invoice Siap Cetak -->
    <div class="print-sheet max-w-3xl mx-auto bg-white border border-stone-200 rounded-2xl p-8 sm:p-11">
        <!-- Kop Surat Hotel -->
        <div class="pb-7 border-b-2 border-[#181512] flex flex-col sm:flex-row sm:items-start justify-between gap-6">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[0.26em] text-amber-800">Official Folio & Receipt</p>
                <h1 class="font-editorial text-4xl text-[#181512] mt-1 leading-none">Svara Hotel & Suites</h1>
                <p class="text-xs text-stone-500 mt-2.5 leading-relaxed">
                    Jl. Raya Dharmawangsa No. 88, Surabaya — Jawa Timur<br>
                    Telp. (031) 555-0192 &bull; concierge@svarahotel.id
                </p>
            </div>

            <div class="sm:text-right">
                <span class="inline-block px-3 py-1 rounded-full border text-[11px] font-semibold {{ $currentStatus['badge'] }}">
                    {{ $currentStatus['label'] }}
                </span>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-stone-400">Nomor Invoice</p>
                <p class="font-mono text-lg font-bold text-stone-900 tabular-nums">INV-{{ $reservation->booking_code }}</p>
                <p class="text-xs text-stone-500 mt-1 tabular-nums">
                    Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>
        </div>

        <!-- Identitas Tamu & Ringkasan Menginap -->
        <div class="py-7 border-b border-stone-200 grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            <div class="p-4 rounded-xl bg-stone-50/80 border border-stone-200/80">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-400">Ditagihkan Kepada (Tamu)</p>
                <p class="mt-1.5 text-base font-bold text-stone-900">{{ $reservation->user->name }}</p>
                <p class="text-stone-600 mt-0.5">{{ $reservation->user->email }}</p>
                @if($reservation->user->phone)
                    <p class="text-stone-600 mt-0.5 tabular-nums">{{ $reservation->user->phone }}</p>
                @endif
            </div>

            <div class="p-4 rounded-xl bg-stone-50/80 border border-stone-200/80">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-stone-400">Detail Akomodasi</p>
                <p class="mt-1.5 text-base font-bold text-stone-900">
                    Unit Kamar {{ $room->room_number }} &mdash; {{ $roomType->name }}
                </p>
                <p class="text-stone-600 mt-0.5 tabular-nums">
                    Check-In: <strong class="text-stone-800">{{ $reservation->check_in_date->translatedFormat('d F Y') }}</strong> (14:00 WIB)
                </p>
                <p class="text-stone-600 mt-0.5 tabular-nums">
                    Check-Out: <strong class="text-stone-800">{{ $reservation->check_out_date->translatedFormat('d F Y') }}</strong> (12:00 WIB)
                </p>
            </div>
        </div>

        <!-- Tabel Rincian Tagihan -->
        <div class="py-7">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-stone-300 text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                        <th class="py-3">Deskripsi Layanan</th>
                        <th class="py-3 text-right">Tarif Dasar</th>
                        <th class="py-3 text-center">Durasi</th>
                        <th class="py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200 text-sm">
                    <tr>
                        <td class="py-4 pr-4">
                            <p class="font-semibold text-stone-900">{{ $roomType->name }} (Kamar No. {{ $room->room_number }})</p>
                            <p class="text-xs text-stone-500 mt-0.5">
                                Kode Booking: <span class="font-mono font-semibold text-stone-700">{{ $reservation->booking_code }}</span>
                                &bull; Lantai {{ $room->floor }}
                            </p>
                        </td>
                        <td class="py-4 text-right tabular-nums text-stone-700 whitespace-nowrap">
                            Rp {{ number_format($roomType->base_price, 0, ',', '.') }}
                        </td>
                        <td class="py-4 text-center tabular-nums text-stone-700 whitespace-nowrap">
                            {{ $reservation->total_nights }} malam
                        </td>
                        <td class="py-4 text-right font-semibold text-stone-900 tabular-nums whitespace-nowrap">
                            Rp {{ number_format($reservation->total_price, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Ringkasan Total -->
            <div class="mt-6 pt-5 border-t border-stone-200 flex justify-end">
                <div class="w-full sm:w-80 space-y-2 text-xs">
                    <div class="flex justify-between text-stone-600 tabular-nums">
                        <span>Subtotal Akomodasi</span>
                        <span class="font-medium text-stone-900">Rp {{ number_format($reservation->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-stone-500">
                        <span>Pajak Pemerintah & Biaya Layanan</span>
                        <span>Sudah Termasuk</span>
                    </div>
                    <div class="pt-3 border-t-2 border-[#181512] flex items-baseline justify-between">
                        <span class="text-sm font-bold text-stone-900">Total Tagihan</span>
                        <span class="text-xl font-bold text-amber-900 tabular-nums">
                            Rp {{ number_format($reservation->total_price, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            @if($reservation->special_requests)
                <div class="mt-6 p-4 rounded-xl bg-amber-50/50 border border-amber-200/80 text-xs text-stone-700">
                    <span class="font-semibold text-stone-900">Catatan Permintaan Khusus:</span>
                    {{ $reservation->special_requests }}
                </div>
            @endif
        </div>

        <!-- Footer Pengesahan -->
        <div class="pt-6 border-t border-stone-200 flex flex-col sm:flex-row sm:items-end justify-between gap-4 text-[11px] text-stone-500">
            <p class="max-w-md leading-relaxed">
                Bukti reservasi ini sah dan diterbitkan secara elektronik oleh sistem manajemen <strong>Svara Hotel & Suites</strong>. Harap tunjukkan kode booking ini saat proses check-in di meja resepsionis.
            </p>
            <div class="sm:text-right shrink-0">
                <p class="font-semibold text-stone-800">Front Office & Reservations</p>
                <p class="text-stone-400 mt-0.5">Svara Hospitality Group</p>
            </div>
        </div>
    </div>
</body>
</html>