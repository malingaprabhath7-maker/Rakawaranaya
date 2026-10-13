<x-guest-layout :title="__('Forgot password')">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ __('Reset your password') }}</h1>
    <p class="mt-2 text-muted">{{ __('Enter your e-mail and we will send you a link to choose a new password.') }}</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-4">
        @csrf
        <x-field name="email" type="email" :label="__('E-mail')" autocomplete="email" required autofocus/>
        <button class="btn-primary w-full py-3">{{ __('Send reset link') }}</button>
    </form>
    <p class="mt-8 text-center text-sm"><a href="{{ route('login') }}" class="font-bold text-brand-700 hover:underline dark:text-brand-300">{{ __('Back to log in') }}</a></p>
</x-guest-layout>
