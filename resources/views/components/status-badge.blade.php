@props(['status'])

@php
    $styles = [
        'tersedia'    => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-600', 'label' => 'Tersedia'],
        'confirmed'   => ['bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200', 'dot' => 'bg-emerald-600', 'label' => 'Confirmed'],
        'terisi'      => ['bg' => 'bg-sky-50 text-sky-800 border-sky-200', 'dot' => 'bg-sky-600', 'label' => 'Terisi'],
        'checked_in'  => ['bg' => 'bg-sky-50 text-sky-800 border-sky-200', 'dot' => 'bg-sky-600', 'label' => 'Checked-In'],
        'pending'     => ['bg' => 'bg-amber-50 text-amber-800 border-amber-200', 'dot' => 'bg-amber-600', 'label' => 'Pending'],
        'checked_out' => ['bg' => 'bg-stone-100 text-stone-700 border-stone-200', 'dot' => 'bg-stone-500', 'label' => 'Checked-Out'],
        'maintenance' => ['bg' => 'bg-rose-50 text-rose-800 border-rose-200', 'dot' => 'bg-rose-600', 'label' => 'Maintenance'],
        'cancelled'   => ['bg' => 'bg-rose-50 text-rose-800 border-rose-200', 'dot' => 'bg-rose-600', 'label' => 'Dibatalkan'],
    ];
    $item =$styles[$status] ?? ['bg' => 'bg-stone-100 text-stone-700 border-stone-200', 'dot' => 'bg-stone-500', 'label' => ucfirst($status)];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium border {$item['bg']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $item['dot'] }}"></span>
    {{ $item['label'] }}
</span>