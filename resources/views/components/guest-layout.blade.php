@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head', ['title' => $title])</head>
<body class="min-h-dvh">
<div class="grid min-h-dvh lg:grid-cols-[1.05fr_1fr]">
    <aside class="relative hidden overflow-hidden bg-brand-900 p-12 text-brand-50 lg:flex lg:flex-col">
        <div class="pointer-events-none absolute -right-24 -top-24 size-96 rounded-full bg-brand-700/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-16 size-96 rounded-full bg-lamp-500/20 blur-3xl"></div>
        <a href="{{ route('home') }}" class="relative text-white">@include('partials.logo')</a>
        <div class="relative mt-auto max-w-md">
            <p class="font-display text-4xl font-extrabold leading-tight tracking-tight text-white">{{ __('Every client, policy and premium — remembered for you.') }}</p>
            <ul class="mt-8 space-y-3 text-brand-100">
                @foreach ([__('Premium due dates and renewals worked out automatically'), __('One-tap WhatsApp reminders in Sinhala or English'), __('Your data protected with two-step login and encryption')] as $point)
                    <li class="flex gap-3"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-lamp-300"/>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
    </aside>
    <main class="flex flex-col px-5 py-6 sm:px-10">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="lg:invisible">@include('partials.logo')</a>
            @include('partials.lang-switch')
        </div>
        <div class="mx-auto my-auto w-full max-w-md py-10">
            <x-flash/>
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
