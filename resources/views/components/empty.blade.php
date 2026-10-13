@props(['icon' => 'sparkle', 'title', 'text' => null])
<div class="flex flex-col items-center gap-2 px-6 py-10 text-center">
    <span class="grid size-12 place-items-center rounded-2xl bg-surface-2 text-muted"><x-icon :name="$icon" class="size-6"/></span>
    <p class="font-semibold">{{ $title }}</p>
    @if ($text)<p class="max-w-sm text-sm text-muted">{{ $text }}</p>@endif
    {{ $slot }}
</div>
