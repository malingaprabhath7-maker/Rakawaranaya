<x-app-layout :title="__('Security')">
    <x-page-header :title="__('Settings')"/>
    @include('settings._nav')
    <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
        {{-- two-step login --}}
        <section class="card card-pad space-y-4 lg:col-span-2">
            <div class="flex flex-wrap items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl {{ $user->hasTwoFactor() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-lamp-100 text-lamp-600 dark:bg-lamp-600/15 dark:text-lamp-300' }}"><x-icon name="lock" class="size-5"/></span>
                <div class="flex-1">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Two-step login') }}</h2>
                    <p class="text-sm text-muted">{{ $user->hasTwoFactor() ? __('On. You need a code from your authenticator app to log in.') : __('Strongly recommended. Even if someone learns your password, they cannot log in without your phone.') }}</p>
                </div>
                @if ($user->hasTwoFactor())<span class="badge-green">{{ __('On') }}</span>@else<span class="badge-lamp">{{ __('Off') }}</span>@endif
            </div>

            @if (! $user->two_factor_secret)
                <form method="POST" action="{{ route('two-factor.enable') }}">@csrf<button class="btn-primary"><x-icon name="shield" class="size-4"/> {{ __('Turn on two-step login') }}</button></form>
            @elseif (! $user->two_factor_confirmed_at)
                <div class="grid gap-6 md:grid-cols-[auto_1fr]">
                    <div class="rounded-2xl bg-white p-3 ring-1 ring-line">{!! $user->twoFactorQrCodeSvg() !!}</div>
                    <div class="space-y-3 text-sm">
                        <p>{{ __('1. Install Google Authenticator or Microsoft Authenticator on your phone.') }}</p>
                        <p>{{ __('2. Scan this QR code with the app.') }}</p>
                        <p>{{ __('3. Enter the 6-digit code it shows.') }}</p>
                        <form method="POST" action="{{ route('two-factor.confirm') }}" class="flex flex-wrap gap-2">
                            @csrf
                            <input name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" class="input w-40 text-center tracking-[0.3em]" placeholder="123456" required aria-label="{{ __('Code') }}">
                            <button class="btn-primary">{{ __('Confirm') }}</button>
                        </form>
                        @error('code', 'confirmTwoFactorAuthentication')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
            @else
                <div x-data="{ show: false }">
                    <button type="button" class="btn-secondary btn-sm" @click="show = !show">{{ __('Show recovery codes') }}</button>
                    <div x-show="show" x-cloak class="mt-3 rounded-xl bg-surface-2 p-4">
                        <p class="mb-2 text-sm text-muted">{{ __('Keep these somewhere safe. Each one lets you log in once if you lose your phone.') }}</p>
                        <div class="num grid grid-cols-2 gap-1 font-mono text-sm">@foreach ($user->recoveryCodes() as $code)<span>{{ $code }}</span>@endforeach</div>
                        <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="mt-3">@csrf<button class="btn-secondary btn-sm">{{ __('Create new codes') }}</button></form>
                    </div>
                </div>
            @endif
            @if ($user->two_factor_secret)
                <form method="POST" action="{{ route('two-factor.disable') }}" x-data @submit="confirm(@js(__('Turn off two-step login?'))) || $event.preventDefault()">@csrf @method('DELETE')<button class="btn-ghost btn-sm text-red-600">{{ __('Turn off') }}</button></form>
            @endif
        </section>

        {{-- password --}}
        <form method="POST" action="{{ route('user-password.update') }}" class="card card-pad space-y-4">
            @csrf @method('PUT')
            <h2 class="font-display text-lg font-extrabold">{{ __('Change password') }}</h2>
            <x-field name="current_password" type="password" :label="__('Current password')" autocomplete="current-password" bag="updatePassword" required/>
            <x-field name="password" type="password" :label="__('New password')" autocomplete="new-password" bag="updatePassword" required/>
            <x-field name="password_confirmation" type="password" :label="__('Confirm password')" autocomplete="new-password" bag="updatePassword" required/>
            <button class="btn-primary">{{ __('Change password') }}</button>
        </form>

        {{-- sessions --}}
        <section class="card card-pad space-y-4">
            <h2 class="font-display text-lg font-extrabold">{{ __('Signed-in devices') }}</h2>
            <ul class="space-y-2 text-sm">
                @forelse ($sessions as $s)
                    <li class="flex items-center gap-3"><x-icon name="globe" class="size-4 text-muted"/><span class="flex-1">{{ $s->agent }} · <span class="num">{{ $s->ip }}</span></span>
                        @if ($s->current)<span class="badge-green">{{ __('This device') }}</span>@else<span class="text-xs text-muted">{{ $s->last_active }}</span>@endif</li>
                @empty
                    <li class="text-muted">{{ __('Only this device.') }}</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('settings.sessions.logout') }}" class="flex flex-wrap gap-2">
                @csrf
                <input type="password" name="password" class="input flex-1" placeholder="{{ __('Your password') }}" required autocomplete="current-password" aria-label="{{ __('Password') }}">
                <button class="btn-secondary">{{ __('Sign out other devices') }}</button>
            </form>
            @error('password')<p class="error">{{ $message }}</p>@enderror
        </section>

        {{-- activity --}}
        <section class="card lg:col-span-2">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Recent activity') }}</h2>
            <div class="table-wrap"><table class="table">
                <thead><tr><th>{{ __('When') }}</th><th>{{ __('What') }}</th><th>{{ __('IP address') }}</th></tr></thead>
                <tbody>@forelse ($activity as $a)<tr>
                    <td class="whitespace-nowrap text-xs text-muted">{{ $a->created_at->diffForHumans() }}</td>
                    <td class="text-sm"><span class="{{ str_contains($a->action, 'failed') ? 'badge-red' : 'badge-gray' }}">{{ __('activity.'.$a->action) === 'activity.'.$a->action ? $a->action : __('activity.'.$a->action) }}</span> @if($a->description)<span class="text-muted">{{ $a->description }}</span>@endif</td>
                    <td class="num text-xs text-muted">{{ $a->ip }}</td></tr>
                @empty<tr><td colspan="3" class="text-muted">{{ __('No activity yet.') }}</td></tr>@endforelse</tbody>
            </table></div>
        </section>

        {{-- delete account --}}
        <form method="POST" action="{{ route('settings.account.destroy') }}" class="card card-pad space-y-3 border-red-200 lg:col-span-2 dark:border-red-500/30" x-data @submit="confirm(@js(__('Delete your account and ALL client data permanently? This cannot be undone.'))) || $event.preventDefault()">
            @csrf @method('DELETE')
            <h2 class="font-display text-lg font-extrabold text-red-600">{{ __('Delete account') }}</h2>
            <p class="text-sm text-muted">{{ __('Download your data first. This removes every client, policy, payment and document.') }}</p>
            <div class="flex flex-wrap gap-2"><input type="password" name="password" class="input max-w-xs" placeholder="{{ __('Your password') }}" required autocomplete="current-password" aria-label="{{ __('Password') }}"><button class="btn-danger">{{ __('Delete my account') }}</button></div>
        </form>
    </div>
</x-app-layout>
