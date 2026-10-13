@props(['title', 'subtitle' => null])
<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
    <div class="min-w-0">
        <h1 class="font-display text-2xl font-extrabold tracking-tight sm:text-[1.7rem]">{{ $title }}</h1>
        @if ($subtitle)<p class="mt-1 text-sm text-muted">{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
</div>
