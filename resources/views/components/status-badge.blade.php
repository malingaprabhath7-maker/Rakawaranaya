@props(['policy'])
@php
    $map = ['active' => 'badge-green', 'proposal' => 'badge-lamp', 'lapsed' => 'badge-red', 'matured' => 'badge-blue', 'cancelled' => 'badge-gray'];
@endphp
<span class="{{ $map[$policy->status] ?? 'badge-gray' }}">{{ __(\App\Support\Options::POLICY_STATUSES[$policy->status] ?? $policy->status) }}</span>
@if ($policy->isOverdue())<span class="badge-red">{{ __('Overdue') }}</span>@endif
