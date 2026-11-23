@props(['title', 'value', 'subtitle' => null])

<div class="bg-amber-50/25 border border-amber-300/80 shadow-[0_2px_10px_-3px_rgba(146,64,14,0.08)] rounded-xl p-5 flex flex-col justify-between transition hover:border-amber-400">
    <div class="flex items-start justify-between gap-3">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-stone-500">{{ $title }}</p>
        @if(isset($icon))
            <div class="w-8 h-8 rounded-lg bg-amber-100/90 text-amber-800 flex items-center justify-center shrink-0">
                {{ $icon }}
            </div>
        @endif
    </div>

    <div class="mt-3">
        <p class="text-2xl font-bold text-stone-900 tracking-tight tabular-nums">{{ $value }}</p>
        @if($subtitle)
            <p class="mt-1 text-xs text-stone-500">{{ $subtitle }}</p>
        @endif
    </div>
</div>