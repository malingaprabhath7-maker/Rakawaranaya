<x-guest-layout :title="__('Two-step login')">
    <div x-data="{ recovery: false }">
        <span class="grid size-12 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200"><x-icon name="lock" class="size-6"/></span>
        <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight">{{ __('Two-step login') }}</h1>
        <p class="mt-2 text-muted" x-show="!recovery">{{ __('Open your authenticator app and enter the 6-digit code.') }}</p>
        <p class="mt-2 text-muted" x-show="recovery" x-cloak>{{ __('Enter one of your saved recovery codes.') }}</p>

        <form method="POST" action="{{ route('two-factor.login.store') }}" class="mt-8 space-y-4">
            @csrf
            <div x-show="!recovery">
                <x-field name="code" :label="__('Code')" inputmode="numeric" autocomplete="one-time-code" maxlength="6" autofocus class="text-center" x-bind:disabled="recovery"/>
            </div>
            <div x-show="recovery" x-cloak>
                <x-field name="recovery_code" :label="__('Recovery code')" autocomplete="off" x-bind:disabled="!recovery"/>
            </div>
            <button class="btn-primary w-full py-3">{{ __('Log in') }}</button>
        </form>
        <button type="button" class="mt-4 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-300" @click="recovery = !recovery"
                x-text="recovery ? @js(__('Use the authenticator code')) : @js(__('Use a recovery code'))"></button>
    </div>
</x-guest-layout>
