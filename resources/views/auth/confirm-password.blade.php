<x-guest-layout :title="__('Confirm password')">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ __('Confirm your password') }}</h1>
    <p class="mt-2 text-muted">{{ __('For your security, please enter your password to continue.') }}</p>
    <form method="POST" action="{{ route('password.confirm.store') }}" class="mt-8 space-y-4">
        @csrf
        <x-field name="password" type="password" :label="__('Password')" autocomplete="current-password" required autofocus/>
        <button class="btn-primary w-full py-3">{{ __('Confirm') }}</button>
    </form>
</x-guest-layout>
