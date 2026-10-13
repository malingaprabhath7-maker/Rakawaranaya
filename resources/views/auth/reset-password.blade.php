<x-guest-layout :title="__('Choose a new password')">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ __('Choose a new password') }}</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-field name="email" type="email" :label="__('E-mail')" :value="$request->email" autocomplete="email" required/>
        <x-field name="password" type="password" :label="__('New password')" autocomplete="new-password" required autofocus/>
        <x-field name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" required/>
        <button class="btn-primary w-full py-3">{{ __('Save new password') }}</button>
    </form>
</x-guest-layout>
