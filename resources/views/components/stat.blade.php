@props(['label', 'value', 'icon' => null, 'tone' => 'brand', 'sub' => null, 'href' => null])
@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200',
        'lamp' => 'bg-lamp-100 text-lamp-600 dark:bg-lamp-600/15 dark:text-lamp-300',
        'red' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300',
        'blue' => 'bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300',
    ];
    $tag = $href ? 'a' : 'div';
@endphp
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->merge(['class' => 'card card-pad flex items-start gap-3'.($href ? ' transition hover:border-brand-300 hover:shadow-sm' : '')]) }}>
    @if ($icon)
        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}"><x-icon :name="$icon" class="size-5"/></span>
    @endif
    <span class="min-w-0">
        <span class="block text-xs font-semibold text-muted">{{ $label }}</span>
        <span class="num mt-0.5 block font-display text-lg leading-tight font-extrabold tracking-tight [overflow-wrap:anywhere] 2xl:text-xl">{{ $value }}</span>
        @if ($sub)<span class="mt-0.5 block text-xs text-muted">{{ $sub }}</span>@endif
    </span>
</{{ $tag }}>
