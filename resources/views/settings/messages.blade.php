@php
    $labels = ['premium_due' => __('Premium reminder'), 'renewal' => __('Renewal reminder'), 'birthday' => __('Birthday wish'), 'thank_you' => __('Payment thank-you'), 'follow_up' => __('Lead follow-up')];
@endphp
<x-app-layout :title="__('WhatsApp messages')">
    <x-page-header :title="__('Settings')"/>
    @include('settings._nav')
    <form method="POST" action="{{ route('settings.messages.update') }}" class="max-w-4xl space-y-6">
        @csrf @method('PUT')
        <p class="text-sm text-muted">{{ __('Placeholders you can use:') }} <code class="rounded bg-surface-2 px-1">{name} {advisor} {insurer} {policy} {type} {amount} {date}</code>. {{ __('Leave a box empty to use the default.') }}</p>
        @foreach ($defaults as $key => $langs)
            <section class="card card-pad">
                <h2 class="mb-3 font-display text-lg font-extrabold">{{ $labels[$key] ?? $key }}</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach (['si' => 'සිංහල', 'en' => 'English'] as $lang => $ln)
                        <div>
                            <label class="label" for="t-{{ $key }}-{{ $lang }}">{{ $ln }}</label>
                            <textarea id="t-{{ $key }}-{{ $lang }}" name="templates[{{ $key }}][{{ $lang }}]" rows="4" class="input" placeholder="{{ $langs[$lang] }}">{{ old("templates.$key.$lang", data_get($user->settings, "templates.$key.$lang")) }}</textarea>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
        <button class="btn-primary">{{ __('Save messages') }}</button>
    </form>
</x-app-layout>
