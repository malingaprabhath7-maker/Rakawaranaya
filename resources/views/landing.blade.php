<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head', ['title' => __('CRM for insurance advisors')])</head>
<body class="bg-page">
<header class="sticky top-0 z-30 border-b border-line/70 bg-page/85 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-5 py-3">
        <a href="{{ route('home') }}">@include('partials.logo')</a>
        <nav class="ml-auto hidden items-center gap-6 text-sm font-semibold text-muted md:flex">
            <a href="#features" class="hover:text-ink">{{ __('Features') }}</a>
            <a href="#security" class="hover:text-ink">{{ __('Security') }}</a>
            <a href="#how" class="hover:text-ink">{{ __('How it works') }}</a>
        </nav>
        <div class="ml-auto flex items-center gap-2 md:ml-6">
            @include('partials.lang-switch')
            <a href="{{ route('login') }}" class="btn-ghost btn-sm hidden sm:inline-flex">{{ __('Log in') }}</a>
            <a href="{{ route('register') }}" class="btn-primary btn-sm">{{ __('Start free') }}</a>
        </div>
    </div>
</header>

<main>
    {{-- hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-40 -top-40 size-[34rem] rounded-full bg-brand-200/50 blur-3xl dark:bg-brand-800/30"></div>
        <div class="pointer-events-none absolute -left-40 top-60 size-[28rem] rounded-full bg-lamp-300/30 blur-3xl dark:bg-lamp-600/10"></div>
        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-5 py-16 lg:grid-cols-[1.05fr_1fr] lg:py-24">
            <div>
                <span class="badge-lamp">{{ __('Made in Sri Lanka · සිංහල / English') }}</span>
                <h1 class="mt-5 font-display text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.4rem]">
                    {{ __('The insurance advisor\'s') }} <span class="text-brand-600 dark:text-brand-300">{{ __('daily companion') }}</span>
                </h1>
                <p class="mt-5 max-w-xl text-lg text-muted">{{ __('Clients, policies, premium due dates, renewals and birthdays — organised in one place, with one-tap WhatsApp reminders. Spend less time on paperwork and more time with clients.') }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @if (config('rakawarana.demo_enabled'))
                        <form method="POST" action="{{ route('demo') }}">@csrf
                            <button class="btn-primary px-6 py-3.5 text-base"><x-icon name="sparkle" class="size-5"/> {{ __('See the live demo') }}</button>
                        </form>
                    @endif
                    <a href="{{ route('register') }}" class="btn-secondary px-6 py-3.5 text-base">{{ __('Create a free account') }} <x-icon name="arrow-right" class="size-4"/></a>
                </div>
                <p class="mt-4 text-sm text-muted">{{ __('No sign-up needed for the demo. Works on phone and laptop.') }}</p>
            </div>

            {{-- product preview built in HTML (no screenshot needed) --}}
            <div class="relative" aria-hidden="true">
                <div class="card rotate-1 p-5 shadow-2xl shadow-brand-900/10">
                    <div class="flex items-center justify-between">
                        <p class="font-display font-extrabold">{{ __('Today') }}</p>
                        <span class="badge-red">3 {{ __('overdue') }}</span>
                    </div>
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach ([[__('Collected'), 'Rs. 486K', 'brand'], [__('Commission'), 'Rs. 92K', 'lamp'], [__('Target'), '78%', 'brand']] as [$l, $v, $t])
                            <div class="rounded-xl bg-surface-2 p-3"><p class="text-[0.65rem] font-bold uppercase tracking-wide text-muted">{{ $l }}</p><p class="num mt-1 font-display text-lg font-extrabold">{{ $v }}</p></div>
                        @endforeach
                    </div>
                    <ul class="mt-4 divide-y divide-line text-sm">
                        @foreach ([['Kamala Fernando', 'Life · Rs. 6,500', __('Today'), 'lamp'], ['Ruwan Silva', 'Motor · Rs. 48,000', __('Renewal in 5 days'), 'blue'], ['Nadeesha Perera', '🎂 '.__('Birthday tomorrow'), '', 'green']] as [$n, $d, $w, $b])
                            <li class="flex items-center gap-3 py-3">
                                <span class="grid size-9 place-items-center rounded-full bg-brand-100 text-xs font-extrabold text-brand-700 dark:bg-brand-900 dark:text-brand-200">{{ mb_substr($n, 0, 1) }}</span>
                                <span class="flex-1"><span class="block font-bold">{{ $n }}</span><span class="block text-xs text-muted">{{ $d }}</span></span>
                                @if ($w)<span class="badge-{{ $b }}">{{ $w }}</span>@endif
                                <span class="grid size-8 place-items-center rounded-lg bg-[#1faa53] text-white"><x-icon name="whatsapp" class="size-4"/></span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card absolute -bottom-6 -left-6 hidden w-56 -rotate-2 p-4 shadow-xl sm:block">
                    <p class="text-xs font-bold text-muted">{{ __('Monthly target') }}</p>
                    <div class="mt-2 h-2 rounded-full bg-surface-2"><div class="h-2 w-[78%] rounded-full bg-brand-500"></div></div>
                    <p class="num mt-2 text-sm font-extrabold">Rs. 195,000 / 250,000</p>
                </div>
            </div>
        </div>
    </section>

    {{-- features --}}
    <section id="features" class="mx-auto max-w-6xl px-5 py-16">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-300">{{ __('Features') }}</p>
        <h2 class="mt-2 max-w-2xl font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Everything an advisor keeps in a notebook — but smarter') }}</h2>
        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['today', __('Today list'), __('Overdue premiums, upcoming dues, renewals, birthdays and follow-ups on one screen every morning.')],
                ['whatsapp', __('WhatsApp in one tap'), __('Ready-made Sinhala and English messages for reminders, renewals, thank-yous and birthday wishes.')],
                ['shield', __('Policies & premiums'), __('Life, motor, health and more. Record a payment and the next due date moves forward by itself.')],
                ['pipeline', __('Sales pipeline'), __('Drag leads from first contact to proposal to client, with follow-up tasks so nobody is forgotten.')],
                ['chart', __('Targets & reports'), __('Monthly collections, commission and new business against your target. Print or export to Excel.')],
                ['team', __('Team view'), __('Branch managers see each advisor\'s numbers — without seeing their private client lists.')],
                ['calc', __('Advisor calculators'), __('Premium, commission, life-cover needs and savings-goal calculators for client meetings.')],
                ['doc', __('Documents'), __('Keep proposal forms and NIC copies with the client, stored privately.')],
                ['globe', __('Sinhala & English'), __('Switch language any time. Works on phones, tablets and laptops, in light or dark mode.')],
            ] as [$icon, $t, $d])
                <div class="card card-pad">
                    <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200"><x-icon :name="$icon" class="size-5"/></span>
                    <h3 class="mt-4 font-display text-lg font-extrabold">{{ $t }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $d }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- security --}}
    <section id="security" class="bg-brand-900 text-brand-50">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 py-16 lg:grid-cols-[1fr_1.2fr]">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-lamp-300">{{ __('Security') }}</p>
                <h2 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-white sm:text-4xl">{{ __('Your clients trust you. You can trust where their data lives.') }}</h2>
            </div>
            <ul class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    [__('Two-step login'), __('Protect your account with Google Authenticator codes and recovery codes.')],
                    [__('Encrypted NIC numbers'), __('NIC numbers are encrypted in the database and hidden on screen until you ask.')],
                    [__('Private by design'), __('Each advisor sees only their own clients. Every record is checked on every request.')],
                    [__('Activity log'), __('See logins, exports and NIC views, and sign out other devices in one click.')],
                ] as [$t, $d])
                    <li class="rounded-2xl border border-brand-700 bg-brand-800/50 p-5">
                        <x-icon name="lock" class="size-5 text-lamp-300"/>
                        <p class="mt-3 font-bold text-white">{{ $t }}</p>
                        <p class="mt-1 text-sm text-brand-100">{{ $d }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- how --}}
    <section id="how" class="mx-auto max-w-6xl px-5 py-16">
        <h2 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Up and running in 10 minutes') }}</h2>
        <ol class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ([[__('Create your account'), __('Free. Turn on two-step login in Settings.')], [__('Add or import clients'), __('Type them in or bring your Excel list as a CSV file.')], [__('Add policies'), __('Due dates, renewals and reminders are worked out for you.')]] as $i => [$t, $d])
                <li class="card card-pad">
                    <span class="num font-display text-4xl font-extrabold text-lamp-500">{{ $i + 1 }}</span>
                    <p class="mt-2 font-display text-lg font-extrabold">{{ $t }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
        <div class="card mt-10 flex flex-col items-start gap-4 bg-brand-50 p-8 sm:flex-row sm:items-center dark:bg-brand-900/40">
            <div class="flex-1">
                <p class="font-display text-2xl font-extrabold">{{ __('Try it with sample data first') }}</p>
                <p class="mt-1 text-muted">{{ __('42 sample clients, 60+ policies and a full month of activity. Nothing to install.') }}</p>
            </div>
            @if (config('rakawarana.demo_enabled'))
                <form method="POST" action="{{ route('demo') }}">@csrf<button class="btn-primary px-6 py-3.5">{{ __('Open the demo') }}</button></form>
            @endif
        </div>
    </section>
</main>

<footer class="border-t border-line">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-4 px-5 py-8 text-sm text-muted">
        @include('partials.logo')
        <p class="ml-auto">© {{ date('Y') }} {{ __('Rakawarana') }} · {{ __('Built with Laravel') }}</p>
    </div>
</footer>
</body>
</html>
