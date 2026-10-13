<x-guest-layout :title="__('Log in')">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ __('Welcome back') }}</h1>
    <p class="mt-2 text-muted">{{ __('Log in to see today\'s premiums, renewals and follow-ups.') }}</p>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-4">
        @csrf
        <x-field name="email" type="email" :label="__('E-mail')" autocomplete="email" required autofocus/>
        <div>
            <x-field name="password" type="password" :label="__('Password')" autocomplete="current-password" required/>
            <div class="mt-2 flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2 text-muted"><input type="checkbox" name="remember" class="size-4 rounded accent-brand-600"> {{ __('Remember me') }}</label>
                <a href="{{ route('password.request') }}" class="font-semibold text-brand-700 hover:underline dark:text-brand-300">{{ __('Forgot password?') }}</a>
            </div>
        </div>
        <button class="btn-primary w-full py-3">{{ __('Log in') }}</button>
    </form>

    @if (config('rakawarana.demo_enabled'))
        <div class="my-6 flex items-center gap-3 text-xs font-semibold uppercase tracking-widest text-muted"><span class="h-px flex-1 bg-line"></span>{{ __('or') }}<span class="h-px flex-1 bg-line"></span></div>
        <form method="POST" action="{{ route('demo') }}">@csrf
            <button class="btn-secondary w-full py-3"><x-icon name="sparkle" class="size-4 text-lamp-500"/> {{ __('Try the demo — no sign-up') }}</button>
        </form>
    @endif

    <p class="mt-8 text-center text-sm text-muted">{{ __('New here?') }} <a href="{{ route('register') }}" class="font-bold text-brand-700 hover:underline dark:text-brand-300">{{ __('Create a free account') }}</a></p>
</x-guest-layout>
