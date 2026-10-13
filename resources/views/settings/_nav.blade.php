<nav class="mb-6 flex gap-2 overflow-x-auto" aria-label="{{ __('Settings') }}">
    @foreach ([['settings.profile', __('Profile')], ['settings.security', __('Security')], ['settings.messages', __('WhatsApp messages')]] as [$r, $l])
        <a href="{{ route($r) }}" class="{{ request()->routeIs($r) ? 'btn-primary' : 'btn-secondary' }} btn-sm whitespace-nowrap">{{ $l }}</a>
    @endforeach
</nav>
