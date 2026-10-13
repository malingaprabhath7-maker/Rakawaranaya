@props(['href', 'client' => null, 'label' => null, 'small' => true])
@if ($href)
    <a href="{{ $href }}" target="_blank" rel="noopener" @if($client) x-data="touch('{{ route('clients.touch', $client) }}')" @click="ping()" @endif
       {{ $attributes->merge(['class' => 'btn-wa'.($small ? ' btn-sm' : '')]) }} title="{{ __('Open WhatsApp with a ready message') }}">
        <x-icon name="whatsapp" class="size-4"/>{{ $label ?? 'WhatsApp' }}
    </a>
@endif
