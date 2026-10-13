@php use App\Support\Money; @endphp
<x-app-layout :title="__('Team')">
    <x-page-header :title="__('Team')" :subtitle="__('Branch managers see their advisors\' numbers. Client lists always stay private.')"/>

    @if (!$user->team)
        <div class="grid gap-6 md:grid-cols-2">
            <form method="POST" action="{{ route('team.store') }}" class="card card-pad space-y-4">
                @csrf
                <h2 class="font-display text-lg font-extrabold">{{ __('I am a manager') }}</h2>
                <p class="text-sm text-muted">{{ __('Create a team and share its code with your advisors.') }}</p>
                <x-field name="name" :label="__('Team / branch name')" :placeholder="__('e.g. Matara Branch')" required/>
                <button class="btn-primary w-full">{{ __('Create team') }}</button>
            </form>
            <form method="POST" action="{{ route('team.join') }}" class="card card-pad space-y-4">
                @csrf
                <h2 class="font-display text-lg font-extrabold">{{ __('I am an advisor') }}</h2>
                <p class="text-sm text-muted">{{ __('Enter the code your manager gave you. They will see your totals, not your clients.') }}</p>
                <x-field name="code" :label="__('Team code')" class="uppercase" autocomplete="off" required/>
                <button class="btn-secondary w-full">{{ __('Join team') }}</button>
            </form>
        </div>
    @elseif (!$user->isManager())
        <div class="card card-pad max-w-xl">
            <p class="text-sm text-muted">{{ __('You are a member of') }}</p>
            <p class="font-display text-2xl font-extrabold">{{ $user->team->name }}</p>
            <p class="mt-1 text-sm text-muted">{{ __('Manager') }}: {{ $user->team->owner?->name }}</p>
            <form method="POST" action="{{ route('team.leave') }}" class="mt-5" x-data @submit="confirm(@js(__('Leave this team?'))) || $event.preventDefault()">@csrf<button class="btn-secondary">{{ __('Leave team') }}</button></form>
        </div>
    @else
        <div class="card card-pad mb-6 flex flex-wrap items-center gap-4">
            <div class="flex-1"><p class="text-sm text-muted">{{ __('Your team') }}</p><p class="font-display text-2xl font-extrabold">{{ $user->team->name }}</p></div>
            <div><p class="text-xs font-semibold text-muted">{{ __('Invite code') }}</p><p class="num font-display text-2xl font-extrabold tracking-[0.2em] text-brand-700 dark:text-brand-300">{{ $user->team->invite_code }}</p></div>
            <form method="POST" action="{{ route('team.code') }}" x-data @submit="confirm(@js(__('Create a new code? The old one stops working.'))) || $event.preventDefault()">@csrf<button class="btn-secondary btn-sm"><x-icon name="refresh" class="size-4"/> {{ __('New code') }}</button></form>
        </div>
        <section class="card overflow-hidden">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('This month') }}</h2>
            <div class="table-wrap"><table class="table">
                <thead><tr><th>{{ __('Advisor') }}</th><th class="text-right">{{ __('New business') }}</th><th class="text-right">{{ __('Target') }}</th><th class="text-right">{{ __('Collected') }}</th><th class="text-right">{{ __('Active policies') }}</th><th class="text-right">{{ __('Overdue') }}</th><th></th></tr></thead>
                <tbody>
                @foreach ($members as $m)
                    @php $t = (float) $m['user']->monthly_target; $pct = $t > 0 ? round($m['new']['annual_premium'] / $t * 100) : null; @endphp
                    <tr>
                        <td><span class="font-bold">{{ $m['user']->name }}</span>@if($m['user']->id === $user->id) <span class="badge-brand">{{ __('You') }}</span>@endif<span class="block text-xs text-muted">{{ $m['user']->agent_code }}</span></td>
                        <td class="num text-right font-semibold">{{ Money::format($m['new']['annual_premium']) }}<span class="block text-xs font-normal text-muted">{{ trans_choice(':count policy|:count policies', $m['new']['count']) }}</span></td>
                        <td class="text-right">@if($pct !== null)<span class="{{ $pct >= 100 ? 'badge-green' : ($pct >= 60 ? 'badge-lamp' : 'badge-red') }}">{{ $pct }}%</span>@else<span class="text-muted">—</span>@endif</td>
                        <td class="num text-right">{{ Money::format($m['collections']['collected']) }}</td>
                        <td class="num text-right">{{ $m['active'] }}</td>
                        <td class="num text-right {{ $m['overdue'] ? 'font-bold text-red-600 dark:text-red-400' : '' }}">{{ $m['overdue'] }}</td>
                        <td class="text-right">@if($m['user']->id !== $user->id)
                            <form method="POST" action="{{ route('team.remove', $m['user']) }}" x-data @submit="confirm(@js(__('Remove this advisor from the team?'))) || $event.preventDefault()">@csrf @method('DELETE')<button class="btn-ghost btn-sm" aria-label="{{ __('Remove') }}"><x-icon name="x" class="size-4"/></button></form>
                        @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        </section>
    @endif
</x-app-layout>
