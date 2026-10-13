@props(['date'])
@php
    $d = $date ? \Illuminate\Support\Carbon::parse($date)->startOfDay() : null;
    $days = $d ? (int) today()->diffInDays($d, false) : null;
@endphp
@if ($d)
    <span class="num font-semibold {{ $days < 0 ? 'text-red-600 dark:text-red-400' : ($days <= 3 ? 'text-lamp-600 dark:text-lamp-300' : '') }}">{{ $d->format('Y-m-d') }}</span>
    <span class="block text-xs text-muted">
        @if ($days < 0) {{ trans_choice(':count day overdue|:count days overdue', abs($days)) }}
        @elseif ($days === 0) {{ __('Today') }}
        @elseif ($days === 1) {{ __('Tomorrow') }}
        @else {{ trans_choice('in :count day|in :count days', $days) }}
        @endif
    </span>
@else
    <span class="text-muted">—</span>
@endif
