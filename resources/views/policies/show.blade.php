@php
    use App\Support\Money; use App\Support\Options; use App\Support\WhatsApp;
    $user = auth()->user();
    $client = $policy->client;
    $paidTotal = $policy->payments->sum('amount');
    $commTotal = $policy->payments->sum('commission');
@endphp
<x-app-layout :title="$policy->label">
    <div class="mb-6 flex flex-wrap items-start gap-4">
        <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200"><x-icon :name="$policy->type === 'motor' ? 'car' : ($policy->type === 'health' ? 'heart' : 'shield')" class="size-7"/></span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-muted"><a href="{{ route('clients.show', $client) }}" class="hover:underline">{{ $client->full_name }}</a></p>
            <h1 class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $policy->label }}</h1>
            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-sm text-muted">
                <x-status-badge :policy="$policy"/>
                <span>{{ __(Options::POLICY_TYPES[$policy->type] ?? $policy->type) }}</span>
                @if ($policy->policy_number)<span class="num">· {{ $policy->policy_number }}</span>@endif
                @if ($policy->vehicle_number)<span>· {{ $policy->vehicle_number }}</span>@endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-wa-button :href="WhatsApp::link($user, $client, $policy->end_date && $policy->end_date->lte(today()->addDays(60)) ? 'renewal' : 'premium_due', $policy)" :client="$client" :small="false" :label="__('Send reminder')"/>
            <a href="{{ route('policies.edit', $policy) }}" class="btn-secondary"><x-icon name="edit" class="size-4"/> {{ __('Edit') }}</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <x-stat :label="__('Premium')" :value="Money::format($policy->premium)" :sub="__(Options::FREQUENCIES[$policy->frequency] ?? '')"/>
                <x-stat :label="__('Annual premium')" :value="Money::format($policy->annualPremium())"/>
                <x-stat :label="__('Cover')" :value="$policy->sum_assured ? Money::short($policy->sum_assured) : '—'"/>
                <x-stat :label="__('Commission earned')" :value="Money::format($commTotal)" :sub="$policy->commission_rate.'%'"/>
            </div>

            <section class="card">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-5 py-4">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Payments') }}</h2>
                    <span class="num text-sm text-muted">{{ __('Total') }}: <b class="text-ink">{{ Money::format($paidTotal) }}</b></span>
                </div>
                @if ($policy->payments->isEmpty())
                    <x-empty icon="money" :title="__('No payments recorded yet')"/>
                @else
                    <div class="table-wrap"><table class="table">
                        <thead><tr><th>{{ __('Paid on') }}</th><th>{{ __('For due date') }}</th><th class="text-right">{{ __('Amount') }}</th><th class="text-right">{{ __('Commission') }}</th><th>{{ __('Method') }}</th><th></th></tr></thead>
                        <tbody>@foreach ($policy->payments as $pay)
                            <tr>
                                <td class="num whitespace-nowrap">{{ $pay->paid_on->format('Y-m-d') }}</td>
                                <td class="num whitespace-nowrap text-muted">{{ $pay->covers_due_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="num text-right font-semibold">{{ Money::format($pay->amount) }}</td>
                                <td class="num text-right text-muted">{{ Money::format($pay->commission) }}</td>
                                <td class="text-sm text-muted">{{ __(Options::PAYMENT_METHODS[$pay->method] ?? $pay->method) }}@if($pay->reference)<span class="block text-xs">{{ $pay->reference }}</span>@endif</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('payments.destroy', $pay) }}" x-data @submit="confirm(@js(__('Remove this payment? The due date moves back if it was the latest one.'))) || $event.preventDefault()">@csrf @method('DELETE')
                                        <button class="btn-ghost btn-sm" aria-label="{{ __('Remove payment') }}"><x-icon name="trash" class="size-4"/></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach</tbody>
                    </table></div>
                @endif
            </section>

            @if ($policy->notes)<section class="card card-pad"><h2 class="mb-2 font-display text-lg font-extrabold">{{ __('Notes') }}</h2><p class="whitespace-pre-line text-sm">{{ $policy->notes }}</p></section>@endif
        </div>

        <aside class="space-y-6">
            @if ($policy->frequency !== 'single' && in_array($policy->status, ['active', 'lapsed']))
                <section class="card card-pad">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Record a payment') }}</h2>
                    @if ($policy->next_due_date)<p class="mt-1 text-sm text-muted">{{ __('Next due') }}: <x-due-date :date="$policy->next_due_date"/></p>@endif
                    <form method="POST" action="{{ route('payments.store', $policy) }}" class="mt-4 space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <x-field name="amount" type="number" step="0.01" min="1" :label="__('Amount (Rs.)')" :value="$policy->premium" required/>
                            <x-field name="paid_on" type="date" :label="__('Paid on')" :value="today()->format('Y-m-d')" required/>
                        </div>
                        <x-select name="method" :label="__('Method')" :options="Options::t(Options::PAYMENT_METHODS)" value="cash" required/>
                        <x-field name="reference" :label="__('Receipt / reference')" :hint="__('Optional')"/>
                        <label class="flex items-center gap-2 text-sm"><input type="hidden" name="advance" value="0"><input type="checkbox" name="advance" value="1" checked class="size-4 accent-brand-600"> {{ __('Move the next due date forward') }}</label>
                        <button class="btn-primary w-full"><x-icon name="check" class="size-4"/> {{ __('Save payment') }}</button>
                    </form>
                    @if (session('thank_you_for') === $policy->id)
                        <div class="mt-4 rounded-xl bg-brand-50 p-3 text-sm dark:bg-brand-900/40">
                            {{ __('Send a thank-you message?') }}
                            <x-wa-button class="mt-2" :href="WhatsApp::link($user, $client, 'thank_you', $policy, ['{amount}' => Money::format($policy->payments->first()?->amount)])" :client="$client" :label="__('Send thank-you')"/>
                        </div>
                    @endif
                </section>
            @endif

            <section class="card card-pad">
                <h2 class="mb-3 font-display text-lg font-extrabold">{{ __('Dates') }}</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('Start date') }}</dt><dd class="num font-semibold">{{ $policy->start_date->format('Y-m-d') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('Next premium due') }}</dt><dd class="text-right"><x-due-date :date="$policy->next_due_date"/></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('Maturity / renewal') }}</dt><dd class="text-right"><x-due-date :date="$policy->end_date"/></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-muted">{{ __('Insurer') }}</dt><dd class="text-right font-semibold">{{ $policy->insurer }}</dd></div>
                </dl>
            </section>

            @if ($policy->documents->isNotEmpty())
                <section class="card">
                    <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Documents') }}</h2>
                    @foreach ($policy->documents as $doc)
                        <a href="{{ route('documents.show', $doc) }}" class="flex items-center gap-3 border-b border-line px-5 py-3 text-sm font-semibold last:border-0 hover:bg-surface-2/60"><x-icon name="doc" class="size-5 text-muted"/>{{ $doc->name }}</a>
                    @endforeach
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
