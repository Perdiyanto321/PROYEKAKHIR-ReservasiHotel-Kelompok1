@props(['title', 'value', 'subtitle' => null])

<div class="bg-white border border-stone-200 rounded-xl p-5">
    <p class="text-xs font-medium uppercase tracking-wider text-stone-500">{{ $title }}</p>
    <p class="mt-2 text-2xl font-semibold text-stone-900 tabular-nums">{{ $value }}</p>
    @if($subtitle)
        <p class="mt-1 text-xs text-stone-500">{{ $subtitle }}</p>
    @endif
</div>