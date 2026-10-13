<x-guest-layout :title="__('Create account')">
    <h1 class="font-display text-3xl font-extrabold tracking-tight">{{ __('Create your account') }}</h1>
    <p class="mt-2 text-muted">{{ __('Free while in beta. Your clients stay private to you.') }}</p>

    <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-4" x-data="{ pw: '' }">
        @csrf
        <x-field name="name" :label="__('Your name')" autocomplete="name" required autofocus/>
        <x-field name="email" type="email" :label="__('E-mail')" autocomplete="email" required/>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="company" :label="__('Insurance company')" list="insurers" :hint="__('Optional')"/>
            <x-field name="phone" type="tel" :label="__('Mobile')" :hint="__('Optional')" autocomplete="tel"/>
        </div>
        <datalist id="insurers">@foreach (\App\Support\Options::INSURERS as $i)<option value="{{ $i }}">@endforeach</datalist>
        <div>
            <x-field name="password" type="password" :label="__('Password')" autocomplete="new-password" required x-model="pw"/>
            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-surface-2" aria-hidden="true">
                <div class="h-full rounded-full transition-all"
                     :class="pw.length < 8 ? 'bg-red-500' : (/[A-Z]/.test(pw) && /\d/.test(pw) && /[^A-Za-z0-9]/.test(pw) ? 'bg-brand-500' : 'bg-lamp-400')"
                     :style="`width: ${Math.min(100, pw.length * 8)}%`"></div>
            </div>
            <p class="hint">{{ __('At least 8 characters with letters and numbers. Mixing in capitals and symbols makes it stronger.') }}</p>
        </div>
        <x-field name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" required/>
        <label class="flex items-start gap-2 text-sm text-muted">
            <input type="checkbox" name="terms" value="1" class="mt-0.5 size-4 rounded accent-brand-600" required>
            <span>{{ __('I will only store client information I am allowed to, and keep my login private.') }}</span>
        </label>
        @error('terms')<p class="error">{{ $message }}</p>@enderror
        <button class="btn-primary w-full py-3">{{ __('Create account') }}</button>
    </form>
    <p class="mt-8 text-center text-sm text-muted">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="font-bold text-brand-700 hover:underline dark:text-brand-300">{{ __('Log in') }}</a></p>
</x-guest-layout>
