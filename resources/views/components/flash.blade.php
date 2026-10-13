@php
    $fortify = [
        'two-factor-authentication-enabled' => __('Scan the QR code with your authenticator app, then enter the 6-digit code to finish.'),
        'two-factor-authentication-confirmed' => __('Two-step login is now on.'),
        'two-factor-authentication-disabled' => __('Two-step login was turned off.'),
        'recovery-codes-generated' => __('New recovery codes created. Save them somewhere safe.'),
        'password-updated' => __('Password changed.'),
        'profile-information-updated' => __('Profile saved.'),
        'verification-link-sent' => __('A new verification link was sent to your e-mail.'),
    ];
    $status = session('status');
    $success = session('success') ?? ($fortify[$status] ?? ($status ? __($status) : null));
    $error = session('error');
@endphp
@if ($success || $error)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition.opacity
         role="status"
         class="mb-5 flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-medium {{ $error ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200' : 'border-brand-200 bg-brand-50 text-brand-800 dark:border-brand-700 dark:bg-brand-900/40 dark:text-brand-100' }}">
        <x-icon :name="$error ? 'alert' : 'check'" class="mt-0.5 size-4 shrink-0"/>
        <span class="flex-1">{{ $error ?? $success }}</span>
        <button type="button" @click="show = false" class="opacity-60 hover:opacity-100" aria-label="{{ __('Close') }}"><x-icon name="x" class="size-4"/></button>
    </div>
@endif
