@extends('layouts.app')
@section('title', 'Laporan Operasional')
@section('header', 'Laporan Pendapatan & Okupansi')

@section('content')
@php
    $period = $start->format('d M Y') . ' – ' . $end->format('d M Y');
    $exportUrl = route('reports.export', request()->except(['page', 'print']));
    $printUrl = route('reports.index', array_merge(request()->except(['page', 'print']), ['print' => 1]));
@endphp

<style>
    @media print {
        aside[data-app-sidebar], header.sticky { display: none !important; }
        body { background: #fff !important; }
        main { padding: 0 !important; max-width: none !important; }
    }
</style>

<!-- Kop laporan (hanya tampil saat dicetak) -->
<div class="hidden print:block mb-6 pb-4 border-b border-stone-300">
    <p class="font-editorial text-3xl text-stone-900">Svara Hotel &amp; Suites</p>
    <p class="text-sm font-semibold text-stone-800 mt-1">Laporan Pendapatan &amp; Okupansi Hotel</p>
    <p class="text-xs text-stone-600 mt-1 tabular-nums">
        Periode check-in: {{ $period }}
        @if($status) &middot; Status tabel: {{ $statusLabels[$status] }} @endif
    </p>
    <p class="text-[11px] text-stone-500 mt-0.5 tabular-nums">Dicetak {{ now()->format('d M Y H:i') }} oleh {{ auth()->user()->name }}</p>
</div>

<!-- Filter -->
<div class="print:hidden bg-white border border-stone-200 rounded-xl p-5 mb-6">
    <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_auto] gap-4 items-end">
        <div>
            <label for="start_date" class="block text-xs font-semibold text-stone-700 mb-1">Tanggal mulai</label>
            <input id="start_date" type="date" name="start_date" value="{{ $start->toDateString() }}"
                   class="w-full rounded-lg border-stone-300 text-sm tabular-nums focus:border-amber-800 focus:ring-amber-800">
        </div>
        <div>
            <label for="end_date" class="block text-xs font-semibold text-stone-700 mb-1">Tanggal akhir</label>
            <input id="end_date" type="date" name="end_date" value="{{ $end->toDateString() }}"
                   class="w-full rounded-lg border-stone-300 text-sm tabular-nums focus:border-amber-800 focus:ring-amber-800">
        </div>
        <div>
            <label for="status" class="block text-xs font-semibold text-stone-700 mb-1">Status reservasi (tabel)</label>
            <select id="status" name="status"
                    class="w-full rounded-lg border-stone-300 text-sm focus:border-amber-800 focus:ring-amber-800">
                <option value="">Semua status</option>
                @foreach($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="px-4 py-2 rounded-lg bg-[#181512] text-amber-50 text-sm font-semibold hover:bg-stone-900 transition">Terapkan</button>
            <a href="{{ route('reports.index') }}" class="px-3 py-2 text-xs font-medium text-stone-500 hover:text-stone-900">Reset</a>
        </div>
    </form>
    <p class="text-[11px] text-stone-500 mt-3">
        Rentang tanggal memakai <span class="font-semibold">tanggal check-in</span>. Kartu ringkasan hanya menghitung reservasi
        berstatus Checked-In &amp; Checked-Out; tabel rincian mengikuti filter status.
    </p>
</div>

<!-- KPI -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="Total Pendapatan Terkonfirmasi" value="Rp {{ number_format($kpi['revenue'], 0, ',', '.') }}" subtitle="Checked-In & Checked-Out · {{ $period }}">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Total Malam Terjual" value="{{ number_format($kpi['nights'], 0, ',', '.') }} Malam" subtitle="Akumulasi malam kamar terjual">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>

    <x-stat-card title="Jumlah Transaksi" value="{{ number_format($kpi['transactions'], 0, ',', '.') }} Reservasi" subtitle="Reservasi Checked-In & Checked-Out">
        <x-slot:icon>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="15" y2="17"/>
            </svg>
        </x-slot:icon>
    </x-stat-card>
</div>

<!-- Tabel Rincian -->
<div class="bg-white border border-stone-200 rounded-xl overflow-hidden">
    <div class="p-5 border-b border-stone-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold text-stone-900">Rincian Transaksi</h2>
            <p class="text-xs text-stone-500 mt-0.5 tabular-nums">
                {{ number_format($printMode ? $transactions->count() : $transactions->total(), 0, ',', '.') }} reservasi
                @if($status) berstatus {{ $statusLabels[$status] }} @endif
                pada {{ $period }}
            </p>
        </div>

        <div class="print:hidden flex items-center gap-2 shrink-0">
            <a href="{{ $exportUrl }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-800 hover:bg-stone-100 transition">
                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ $printUrl }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-[#181512] text-amber-50 text-xs font-semibold hover:bg-stone-900 transition">
                <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                </svg>
                Cetak Laporan
            </a>
        </div>
    </div>

    @if($transactions->isEmpty())
        <div class="p-12 text-center">
            <p class="text-sm font-semibold text-stone-800">Tidak ada transaksi pada rentang ini.</p>
            <p class="text-xs text-stone-500 mt-1">Ubah rentang tanggal atau filter status.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-stone-50/70 border-b border-stone-200 text-left text-[11px] font-semibold uppercase tracking-wider text-stone-500">
                        <th class="px-5 py-3">Kode Booking</th>
                        <th class="px-5 py-3">Tamu</th>
                        <th class="px-5 py-3">Kamar</th>
                        <th class="px-5 py-3">Check-In</th>
                        <th class="px-5 py-3">Check-Out</th>
                        <th class="px-5 py-3 text-right">Malam</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($transactions as $r)
                        <tr class="hover:bg-stone-50/50 transition">
                            <td class="px-5 py-3 font-medium text-stone-900 tabular-nums whitespace-nowrap">{{ $r->booking_code }}</td>
                            <td class="px-5 py-3 text-stone-700">{{ $r->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-stone-700">
                                <span class="tabular-nums font-medium">{{ $r->room?->room_number ?? '—' }}</span>
                                <span class="text-xs text-stone-500">· {{ $r->room?->roomType?->name }}</span>
                            </td>
                            <td class="px-5 py-3 text-stone-600 tabular-nums whitespace-nowrap">{{ $r->check_in_date->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-stone-600 tabular-nums whitespace-nowrap">{{ $r->check_out_date->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right text-stone-700 tabular-nums">{{ $r->total_nights }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-stone-900 tabular-nums whitespace-nowrap">Rp {{ number_format($r->total_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-3"><x-status-badge :status="$r->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @unless($printMode)
            @if($transactions->hasPages())
                <div class="print:hidden px-5 py-4 border-t border-stone-200">{{ $transactions->links() }}</div>
            @endif
        @endunless
    @endif
</div>

@if($printMode)
    @push('scripts')
        <script>
            window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 400); });
        </script>
    @endpush
@endif
@endsection
