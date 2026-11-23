@props(['status'])

@php
    $styles = [
        'tersedia'    => ['class' => 'bg-emerald-950/[0.04] text-emerald-900 border-emerald-800/25', 'label' => 'Tersedia'],
        'confirmed'   => ['class' => 'bg-emerald-950/[0.04] text-emerald-900 border-emerald-800/25', 'label' => 'Confirmed'],
        'terisi'      => ['class' => 'bg-amber-900/[0.07] text-amber-950 border-amber-800/30', 'label' => 'Terisi'],
        'checked_in'  => ['class' => 'bg-amber-900/[0.07] text-amber-950 border-amber-800/30', 'label' => 'Checked-In'],
        'pending'     => ['class' => 'bg-amber-50 text-amber-800 border-amber-400/50', 'label' => 'Pending'],
        'checked_out' => ['class' => 'bg-stone-100/90 text-stone-600 border-stone-300/80', 'label' => 'Checked-Out'],
        'maintenance' => ['class' => 'bg-rose-950/[0.04] text-rose-900 border-rose-800/25', 'label' => 'Maintenance'],
        'cancelled'   => ['class' => 'bg-rose-950/[0.04] text-rose-900 border-rose-800/25', 'label' => 'Dibatalkan'],
    ];
    $item = $styles[$status] ?? ['class' => 'bg-stone-100 text-stone-700 border-stone-300', 'label' => ucfirst($status)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center justify-center w-28 py-1 rounded-md text-[11px] font-semibold tracking-wide border whitespace-nowrap {$item['class']}"]) }}>
    {{ $item['label'] }}
</span>