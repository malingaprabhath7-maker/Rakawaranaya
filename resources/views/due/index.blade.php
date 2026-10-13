@php use App\Support\Money; use App\Support\WhatsApp; $user = auth()->user(); @endphp
<x-app-layout :title="__('Today')">
    <x-page-header :title="__('Today\'s work')" :subtitle="__('Premiums to collect, renewals, birthdays and follow-ups — most urgent first.')"/>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
        <x-stat :label="__('Overdue')" :value="$overdue->count()" icon="alert" tone="red"/>
        <x-stat :label="__('Due in 30 days')" :value="$upcoming->count()" icon="clock" tone="lamp"/>
        <x-stat :label="__('Renewals (60 days)')" :value="$renewals->count()" icon="refresh" tone="blue"/>
        <x-stat :label="__('Birthdays (14 days)')" :value="$birthdays->count()" icon="cake"/>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        @foreach ([['overdue', __('Overdue premiums'), $overdue, 'red'], ['upcoming', __('Coming up'), $upcoming, 'lamp']] as [$key, $heading, $list, $tone])
            <section class="card {{ $key === 'overdue' && $list->isNotEmpty() ? 'border-red-200 dark:border-red-500/30' : '' }}">
                <h2 class="flex items-center justify-between border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ $heading }} <span class="badge-{{ $tone }}">{{ $list->count() }}</span></h2>
                @forelse ($list as $p)
                    <div class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-3 last:border-0">
                        <a href="{{ route('policies.show', $p) }}" class="min-w-0 flex-1">
                            <span class="block truncate font-bold">{{ $p->client?->full_name }}</span>
                            <span class="block truncate text-xs text-muted">{{ $p->label }}</span>
                        </a>
                        <span class="num text-right text-sm font-bold">{{ Money::format($p->premium) }}</span>
                        <span class="w-28 text-right text-sm"><x-due-date :date="$p->next_due_date"/></span>
                        <x-wa-button :href="WhatsApp::link($user, $p->client, 'premium_due', $p)" :client="$p->client" label=""/>
                    </div>
                @empty
                    <x-empty icon="check" :title="$key === 'overdue' ? __('Nothing overdue — well done!') : __('Nothing due in the next 30 days')"/>
                @endforelse
            </section>
        @endforeach

        <section class="card">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Renewals & maturities') }}</h2>
            @forelse ($renewals as $p)
                <div class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-3 last:border-0">
                    <span class="grid size-9 place-items-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300"><x-icon :name="$p->type === 'motor' ? 'car' : 'refresh'" class="size-4"/></span>
                    <a href="{{ route('policies.show', $p) }}" class="min-w-0 flex-1"><span class="block truncate font-bold">{{ $p->client?->full_name }}</span><span class="block truncate text-xs text-muted">{{ $p->label }} @if($p->vehicle_number)· {{ $p->vehicle_number }}@endif</span></a>
                    <span class="w-28 text-right text-sm"><x-due-date :date="$p->end_date"/></span>
                    <x-wa-button :href="WhatsApp::link($user, $p->client, 'renewal', $p, ['{date}' => $p->end_date->format('Y-m-d')])" :client="$p->client" label=""/>
                </div>
            @empty
                <x-empty icon="refresh" :title="__('No renewals in the next 60 days')"/>
            @endforelse
        </section>

        <div class="space-y-6">
            <section class="card">
                <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Birthdays') }}</h2>
                @forelse ($birthdays as $c)
                    <div class="flex items-center gap-3 border-b border-line px-5 py-3 last:border-0">
                        <span class="grid size-9 place-items-center rounded-xl bg-lamp-100 text-lamp-600 dark:bg-lamp-600/15 dark:text-lamp-300"><x-icon name="cake" class="size-4"/></span>
                        <a href="{{ route('clients.show', $c) }}" class="min-w-0 flex-1"><span class="block truncate font-bold">{{ $c->full_name }}</span>
                            <span class="block text-xs text-muted">{{ $c->date_of_birth->format('M j') }} · {{ $c->days_to_birthday === 0 ? __('Today!') : trans_choice('in :count day|in :count days', $c->days_to_birthday) }}</span></a>
                        <x-wa-button :href="WhatsApp::link($user, $c, 'birthday')" :client="$c" label=""/>
                    </div>
                @empty
                    <x-empty icon="cake" :title="__('No birthdays in the next two weeks')"/>
                @endforelse
            </section>
            <section class="card">
                <h2 class="flex items-center justify-between border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Tasks this week') }} <a href="{{ route('tasks.index') }}" class="text-sm font-bold text-brand-700 hover:underline dark:text-brand-300">{{ __('All tasks') }}</a></h2>
                @forelse ($tasks as $task)@include('tasks._row', ['task' => $task])@empty<x-empty icon="tasks" :title="__('No open tasks this week')"/>@endforelse
            </section>
        </div>
    </div>
</x-app-layout>
