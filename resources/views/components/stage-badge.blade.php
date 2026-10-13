@props(['stage'])
@php
    $map = ['lead' => 'badge-gray', 'contacted' => 'badge-blue', 'proposal' => 'badge-lamp', 'client' => 'badge-green', 'inactive' => 'badge-gray'];
@endphp
<span class="{{ $map[$stage] ?? 'badge-gray' }}">{{ __(\App\Support\Options::STAGES[$stage] ?? $stage) }}</span>
