<x-app-layout :title="__('Settings')">
    <x-page-header :title="__('Settings')"/>
    @include('settings._nav')
    <form method="POST" action="{{ route('user-profile-information.update') }}" class="card card-pad max-w-3xl space-y-4">
        @csrf @method('PUT')
        <h2 class="font-display text-lg font-extrabold">{{ __('Profile') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="name" :label="__('Name')" :value="$user->name" bag="updateProfileInformation" required/>
            <x-field name="email" type="email" :label="__('E-mail')" :value="$user->email" bag="updateProfileInformation" required :readonly="$user->is_demo"/>
            <x-field name="phone" type="tel" :label="__('Mobile')" :value="$user->phone" bag="updateProfileInformation"/>
            <x-field name="company" :label="__('Insurance company')" :value="$user->company" list="insurers" bag="updateProfileInformation"/>
            <datalist id="insurers">@foreach (\App\Support\Options::INSURERS as $i)<option value="{{ $i }}">@endforeach</datalist>
            <x-field name="agent_code" :label="__('Agent code')" :value="$user->agent_code" bag="updateProfileInformation"/>
            <x-field name="monthly_target" type="number" min="0" step="1000" :label="__('Monthly target (annual premium, Rs.)')" :value="(int) $user->monthly_target" bag="updateProfileInformation" :hint="__('Shown on the dashboard and team page')"/>
            <x-select name="locale" :label="__('Language')" :options="['si' => 'සිංහල', 'en' => 'English']" :value="$user->locale" required/>
        </div>
        <label class="flex items-start gap-3 rounded-xl border border-line p-3">
            <input type="checkbox" name="digest" value="1" class="mt-1 size-4 accent-brand-600" @checked(data_get($user->settings, 'digest', true) !== false)>
            <span><span class="font-semibold">{{ __('Morning e-mail') }}</span><span class="hint block">{{ __('Every day at 7 am: premiums due, renewals and birthdays. Only sent when there is something to do.') }}</span></span>
        </label>
        <button class="btn-primary">{{ __('Save') }}</button>
    </form>

    <section class="card card-pad mt-6 max-w-3xl">
        <h2 class="font-display text-lg font-extrabold">{{ __('Your data') }}</h2>
        <p class="mt-1 text-sm text-muted">{{ __('Download everything as CSV files you can open in Excel.') }}</p>
        <div class="mt-4 flex flex-wrap gap-2">
            <a href="{{ route('export', 'clients') }}" class="btn-secondary btn-sm"><x-icon name="download" class="size-4"/> {{ __('Clients') }}</a>
            <a href="{{ route('export', 'policies') }}" class="btn-secondary btn-sm"><x-icon name="download" class="size-4"/> {{ __('Policies') }}</a>
            <a href="{{ route('export', 'payments') }}" class="btn-secondary btn-sm"><x-icon name="download" class="size-4"/> {{ __('Payments') }}</a>
        </div>
    </section>
</x-app-layout>
