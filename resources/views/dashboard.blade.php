@php
    use App\Support\Money;
    use App\Support\WhatsApp;
    $user = auth()->user();
    $s = $summary;
    $pct = $s['target'] > 0 ? min(100, round($s['new_business']['annual_premium'] / $s['target'] * 100)) : null;
    $hour = now()->hour;
    $greet = $hour < 12 ? __('Good morning') : ($hour < 17 ? __('Good afternoon') : __('Good evening'));
@endphp
<x-app-layout :title="__('Dashboard')">
    <x-page-header :title="$greet.', '.explode(' ', $user->name)[0]" :subtitle="now()->translatedFormat('l, j F Y')">
        <x-slot:actions>
            <a href="{{ route('due') }}" class="btn-secondary"><x-icon name="today" class="size-4"/> {{ __('Today\'s list') }}</a>
            <a href="{{ route('policies.create') }}" class="btn-primary"><x-icon name="plus" class="size-4"/> {{ __('New policy') }}</a>
        </x-slot:actions>
    </x-page-header>

    {{-- target + headline numbers --}}
    <div class="grid gap-4 lg:grid-cols-[1.25fr_2fr]">
        <div class="card card-pad relative overflow-hidden bg-brand-700 text-white dark:bg-brand-800">
            <div class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-lamp-400/25 blur-2xl"></div>
            <p class="text-sm font-semibold text-brand-100">{{ __('New business this month') }}</p>
            <p class="num mt-1 font-display text-4xl font-extrabold tracking-tight">{{ Money::format($s['new_business']['annual_premium']) }}</p>
            <p class="mt-1 text-sm text-brand-100">{{ trans_choice(':count new policy (annual premium)|:count new policies (annual premium)', $s['new_business']['count']) }}</p>
            @if ($pct !== null)
                <div class="mt-5">
                    <div class="flex justify-between text-xs font-semibold text-brand-100"><span>{{ __('Monthly target') }} · {{ Money::format($s['target']) }}</span><span class="num">{{ $pct }}%</span></div>
                    <div class="mt-1.5 h-2.5 rounded-full bg-white/15"><div class="h-2.5 rounded-full bg-lamp-300" style="width: {{ $pct }}%"></div></div>
                </div>
            @else
                <a href="{{ route('settings.profile') }}" class="mt-5 inline-block text-sm font-bold text-lamp-300 underline">{{ __('Set a monthly target') }}</a>
            @endif
        </div>
        <div class="grid grid-cols-2 gap-4 xl:grid-cols-3">
            <x-stat :label="__('Collected this month')" :value="Money::format($s['month']['collected'])" icon="money" :sub="trans_choice(':count payment|:count payments', $s['month']['count'])"/>
            <x-stat :label="__('Commission this month')" :value="Money::format($s['month']['commission'])" icon="sparkle" tone="lamp"/>
            <x-stat :label="__('Overdue premiums')" :value="$s['overdue']" icon="alert" tone="red" :href="route('due')" :sub="trans_choice(':count more due this week|:count more due this week', $s['due_week'])"/>
            <x-stat :label="__('Active policies')" :value="$s['active_policies']" icon="shield" :href="route('policies.index', ['status' => 'active'])"/>
            <x-stat :label="__('Clients')" :value="$s['clients']" icon="users" tone="blue" :href="route('clients.index', ['stage' => 'client'])"/>
            <x-stat :label="__('Open leads')" :value="$s['leads']" icon="pipeline" tone="lamp" :href="route('pipeline')"/>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        {{-- work list --}}
        <div class="space-y-6">
            <section class="card">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Premiums due') }}</h2>
                    <a href="{{ route('due') }}" class="text-sm font-bold text-brand-700 hover:underline dark:text-brand-300">{{ __('See all') }}</a>
                </div>
                @forelse ($due as $p)
                    <div class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-3 last:border-0">
                        <a href="{{ route('policies.show', $p) }}" class="min-w-0 flex-1">
                            <span class="block truncate font-bold">{{ $p->client?->full_name }}</span>
                            <span class="block truncate text-xs text-muted">{{ $p->label }} · {{ Money::format($p->premium) }}</span>
                        </a>
                        <span class="text-right text-sm"><x-due-date :date="$p->next_due_date"/></span>
                        <x-wa-button :href="WhatsApp::link($user, $p->client, 'premium_due', $p)" :client="$p->client" label=""/>
                    </div>
                @empty
                    <x-empty icon="check" :title="__('Nothing due in the next two weeks')" :text="__('Premiums you need to collect will appear here.')"/>
                @endforelse
            </section>

            <section class="card card-pad">
                <div class="flex items-center justify-between">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Collections & commission') }}</h2>
                    <span class="text-xs font-semibold text-muted">{{ __('Last 6 months') }}</span>
                </div>
                <div class="mt-4 h-64"><canvas data-chart="chart-monthly" role="img" aria-label="{{ __('Collections and commission for the last 6 months') }}"></canvas></div>
                <script type="application/json" id="chart-monthly">@json(['kind' => 'bars', 'labels' => $monthly->pluck('label'), 'series' => [['label' => __('Collected'), 'values' => $monthly->pluck('collected')], ['label' => __('Commission'), 'values' => $monthly->pluck('commission')]]])</script>
            </section>
        </div>

        <div class="space-y-6">
            <section class="card">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Tasks') }}</h2>
                    <a href="{{ route('tasks.index') }}" class="text-sm font-bold text-brand-700 hover:underline dark:text-brand-300">{{ __('All tasks') }}</a>
                </div>
                @forelse ($tasks as $task)
                    @include('tasks._row', ['task' => $task, 'compact' => true])
                @empty
                    <x-empty icon="tasks" :title="__('No tasks for the next few days')"/>
                @endforelse
            </section>

            <section class="card">
                <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Renewals & birthdays') }}</h2>
                @foreach ($renewals as $p)
                    <div class="flex items-center gap-3 border-b border-line px-5 py-3">
                        <span class="grid size-9 place-items-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300"><x-icon :name="$p->type === 'motor' ? 'car' : 'refresh'" class="size-4"/></span>
                        <a href="{{ route('policies.show', $p) }}" class="min-w-0 flex-1"><span class="block truncate font-bold">{{ $p->client?->full_name }}</span><span class="block truncate text-xs text-muted">{{ $p->label }}</span></a>
                        <span class="text-right text-sm"><x-due-date :date="$p->end_date"/></span>
                    </div>
                @endforeach
                @foreach ($birthdays as $c)
                    <div class="flex items-center gap-3 border-b border-line px-5 py-3 last:border-0">
                        <span class="grid size-9 place-items-center rounded-xl bg-lamp-100 text-lamp-600 dark:bg-lamp-600/15 dark:text-lamp-300"><x-icon name="cake" class="size-4"/></span>
                        <a href="{{ route('clients.show', $c) }}" class="min-w-0 flex-1"><span class="block truncate font-bold">{{ $c->full_name }}</span>
                            <span class="block text-xs text-muted">{{ $c->days_to_birthday === 0 ? __('Birthday today!') : trans_choice('Birthday in :count day|Birthday in :count days', $c->days_to_birthday) }} · {{ trans_choice(':count year|:count years', ($c->age ?? 0) + ($c->days_to_birthday === 0 ? 0 : 1)) }}</span></a>
                        <x-wa-button :href="WhatsApp::link($user, $c, 'birthday')" :client="$c" label=""/>
                    </div>
                @endforeach
                @if ($renewals->isEmpty() && $birthdays->isEmpty())
                    <x-empty icon="cake" :title="__('No renewals or birthdays coming up')"/>
                @endif
            </section>

            <section class="card card-pad">
                <h2 class="font-display text-lg font-extrabold">{{ __('Active policies by type') }}</h2>
                @if ($byType->isNotEmpty())
                    <div class="mt-4 h-52"><canvas data-chart="chart-types" role="img" aria-label="{{ __('Active policies by type') }}"></canvas></div>
                    <script type="application/json" id="chart-types">@json(['kind' => 'doughnut', 'labels' => $byType->keys(), 'values' => $byType->values()])</script>
                @else
                    <x-empty icon="shield" :title="__('No active policies yet')"><a href="{{ route('policies.create') }}" class="btn-primary btn-sm mt-2">{{ __('Add a policy') }}</a></x-empty>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
