@php use App\Support\Money; use App\Support\Options; $pct = $target > 0 ? round($newBusiness['annual_premium'] / $target * 100) : null; @endphp
<x-app-layout :title="__('Reports')">
    <x-page-header :title="__('Monthly report')" :subtitle="$from->translatedFormat('F Y')">
        <x-slot:actions>
            <form method="GET" class="flex gap-2"><input type="month" name="month" value="{{ $month }}" class="input w-auto" aria-label="{{ __('Month') }}"><button class="btn-secondary">{{ __('Show') }}</button></form>
            <a href="{{ route('reports.print', ['month' => $month]) }}" target="_blank" class="btn-secondary"><x-icon name="print" class="size-4"/> {{ __('Print / PDF') }}</a>
            <a href="{{ route('export', 'payments') }}" class="btn-secondary"><x-icon name="download" class="size-4"/> {{ __('Payments CSV') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat :label="__('Collected')" :value="Money::format($collections['collected'])" icon="money" :sub="trans_choice(':count payment|:count payments', $collections['count'])"/>
        <x-stat :label="__('Commission')" :value="Money::format($collections['commission'])" icon="sparkle" tone="lamp"/>
        <x-stat :label="__('New business (annual)')" :value="Money::format($newBusiness['annual_premium'])" icon="shield" :sub="trans_choice(':count policy|:count policies', $newBusiness['count'])"/>
        <x-stat :label="__('Target reached')" :value="$pct !== null ? $pct.'%' : '—'" icon="chart" tone="blue" :sub="$target ? Money::format($target) : __('No target set')"/>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('By insurer') }}</h2>
            @if ($byInsurer->isEmpty())<x-empty icon="chart" :title="__('No payments this month')"/>@else
            <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('Insurer') }}</th><th class="text-right">{{ __('Payments') }}</th><th class="text-right">{{ __('Collected') }}</th><th class="text-right">{{ __('Commission') }}</th></tr></thead>
                <tbody>@foreach ($byInsurer as $name => $row)<tr><td class="font-semibold">{{ $name }}</td><td class="num text-right">{{ $row['count'] }}</td><td class="num text-right font-semibold">{{ Money::format($row['amount']) }}</td><td class="num text-right text-muted">{{ Money::format($row['commission']) }}</td></tr>@endforeach</tbody></table></div>
            @endif
        </section>
        <section class="card">
            <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('New policies by type') }}</h2>
            @if ($byType->isEmpty())<x-empty icon="shield" :title="__('No new policies this month')"/>@else
            <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('Type') }}</th><th class="text-right">{{ __('Policies') }}</th><th class="text-right">{{ __('Annual premium') }}</th></tr></thead>
                <tbody>@foreach ($byType as $type => $row)<tr><td class="font-semibold">{{ __(Options::POLICY_TYPES[$type] ?? $type) }}</td><td class="num text-right">{{ $row['count'] }}</td><td class="num text-right font-semibold">{{ Money::format($row['annual']) }}</td></tr>@endforeach</tbody></table></div>
            @endif
        </section>
    </div>

    <section class="card mt-6">
        <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Payments') }}</h2>
        @if ($payments->isEmpty())<x-empty icon="money" :title="__('No payments this month')"/>@else
        <div class="table-wrap"><table class="table"><thead><tr><th>{{ __('Date') }}</th><th>{{ __('Client') }}</th><th>{{ __('Policy') }}</th><th class="text-right">{{ __('Amount') }}</th><th class="text-right">{{ __('Commission') }}</th></tr></thead>
            <tbody>@foreach ($payments as $p)<tr>
                <td class="num whitespace-nowrap">{{ $p->paid_on->format('Y-m-d') }}</td>
                <td class="font-semibold">{{ $p->policy?->client?->full_name }}</td>
                <td class="text-sm text-muted">{{ $p->policy?->label }}</td>
                <td class="num text-right font-semibold">{{ Money::format($p->amount) }}</td>
                <td class="num text-right text-muted">{{ Money::format($p->commission) }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
</x-app-layout>
