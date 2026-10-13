@props(['title' => null])
@php
    $user = auth()->user();
    $nav = [
        ['dashboard', 'home', __('Dashboard')],
        ['due', 'today', __('Today')],
        ['clients.index', 'users', __('Clients'), 'clients.*'],
        ['pipeline', 'pipeline', __('Pipeline')],
        ['policies.index', 'shield', __('Policies'), 'policies.*'],
        ['tasks.index', 'tasks', __('Tasks')],
        ['reports.index', 'chart', __('Reports'), 'reports.*'],
        ['tools', 'calc', __('Calculators')],
        ['team', 'team', __('Team')],
    ];
    $mobile = [['dashboard', 'home', __('Home')], ['due', 'today', __('Today')], ['clients.index', 'users', __('Clients'), 'clients.*'], ['policies.index', 'shield', __('Policies'), 'policies.*']];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>@include('partials.head', ['title' => $title])</head>
<body class="min-h-dvh" x-data="{ menu: false }">
<div class="lg:grid lg:min-h-dvh lg:grid-cols-[17rem_1fr]">
    {{-- sidebar (desktop) / drawer (mobile) --}}
    <div x-show="menu" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="menu = false"></div>
    <aside :class="menu ? 'translate-x-0' : '-translate-x-full'" class="no-print fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col border-r border-line bg-surface transition lg:sticky lg:top-0 lg:h-dvh lg:w-auto lg:translate-x-0">
        <div class="flex items-center justify-between px-5 pb-3 pt-5">
            <a href="{{ route('dashboard') }}">@include('partials.logo')</a>
            <button class="btn-ghost btn-sm lg:hidden" @click="menu = false" aria-label="{{ __('Close menu') }}"><x-icon name="x"/></button>
        </div>
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 py-2" aria-label="{{ __('Main') }}">
            @foreach ($nav as $item)
                <a href="{{ route($item[0]) }}" class="nav-link {{ request()->routeIs($item[3] ?? $item[0]) ? 'active' : '' }}" @if(request()->routeIs($item[3] ?? $item[0])) aria-current="page" @endif>
                    <x-icon :name="$item[1]"/> {{ $item[2] }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-line p-3">
            @if ($user->is_demo)
                <div class="mb-3 rounded-xl bg-lamp-100 p-3 text-xs font-medium text-lamp-600 dark:bg-lamp-600/15 dark:text-lamp-300">
                    {{ __('You are in the demo. Sample data resets every night.') }}
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">@csrf<button class="font-bold underline">{{ __('Create your own account') }}</button></form>
                </div>
            @endif
            <a href="{{ route('settings.profile') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><x-icon name="settings"/> {{ __('Settings') }}</a>
            <div class="mt-2 flex items-center gap-3 rounded-xl px-3 py-2">
                <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-100 text-sm font-extrabold text-brand-700 dark:bg-brand-900 dark:text-brand-200">{{ $user->initials() }}</span>
                <span class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-sm font-bold">{{ $user->name }}</span>
                    <span class="block truncate text-xs text-muted">{{ $user->company ?: $user->email }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn-ghost btn-sm" title="{{ __('Log out') }}" aria-label="{{ __('Log out') }}"><x-icon name="logout" class="size-4"/></button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-col">
        <header class="no-print sticky top-0 z-30 flex items-center gap-3 border-b border-line bg-page/85 px-4 py-3 backdrop-blur sm:px-6">
            <button class="btn-ghost btn-sm lg:hidden" @click="menu = true" aria-label="{{ __('Open menu') }}"><x-icon name="menu"/></button>
            <form action="{{ route('clients.index') }}" method="GET" class="relative hidden max-w-sm flex-1 sm:block" role="search">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted"/>
                <input name="q" value="{{ request()->routeIs('clients.index') ? request('q') : '' }}" class="input py-2 pl-9" placeholder="{{ __('Search clients…') }}" aria-label="{{ __('Search clients') }}">
            </form>
            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('clients.create') }}" class="btn-primary btn-sm hidden sm:inline-flex"><x-icon name="plus" class="size-4"/> {{ __('New client') }}</a>
                <div x-data="{ open: false }" class="relative">
                    <button class="btn-ghost btn-sm" @click="open = !open" aria-label="{{ __('Theme') }}"><x-icon name="sun" class="size-4 dark:hidden"/><x-icon name="moon" class="hidden size-4 dark:block"/></button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="card absolute right-0 mt-2 w-36 p-1 text-sm shadow-lg">
                        <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-surface-2" @click="setTheme('light'); open=false">{{ __('Light') }}</button>
                        <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-surface-2" @click="setTheme('dark'); open=false">{{ __('Dark') }}</button>
                        <button class="w-full rounded-lg px-3 py-2 text-left hover:bg-surface-2" @click="setTheme('system'); open=false">{{ __('System') }}</button>
                    </div>
                </div>
                @include('partials.lang-switch')
            </div>
        </header>

        <main class="mx-auto w-full max-w-7xl flex-1 px-4 pb-28 pt-6 sm:px-6 lg:pb-10">
            <x-flash/>
            {{ $slot }}
        </main>
    </div>
</div>

{{-- bottom navigation on phones --}}
<nav class="no-print fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 border-t border-line bg-surface/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden" aria-label="{{ __('Quick') }}">
    @foreach ($mobile as $i => $item)
        @if ($i === 2)
            <a href="{{ route('clients.create') }}" class="flex flex-col items-center justify-center" aria-label="{{ __('New client') }}">
                <span class="-mt-5 grid size-12 place-items-center rounded-2xl bg-brand-600 text-white shadow-lg shadow-brand-600/30 dark:bg-brand-500"><x-icon name="plus" class="size-6"/></span>
            </a>
        @endif
        <a href="{{ route($item[0]) }}" class="flex flex-col items-center gap-0.5 py-2 text-[0.68rem] font-bold {{ request()->routeIs($item[3] ?? $item[0]) ? 'text-brand-600 dark:text-brand-300' : 'text-muted' }}">
            <x-icon :name="$item[1]" class="size-5"/>{{ $item[2] }}
        </a>
    @endforeach
</nav>
</body>
</html>
