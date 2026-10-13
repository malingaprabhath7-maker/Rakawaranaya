<x-app-layout :title="__('Calculators')">
    <x-page-header :title="__('Calculators')" :subtitle="__('Quick numbers for client meetings. Nothing here is saved.')"/>
    <div x-data="calculators()" class="grid gap-6 lg:grid-cols-3">
        <section class="card card-pad space-y-4">
            <h2 class="font-display text-lg font-extrabold">{{ __('Premium & commission') }}</h2>
            <div><label class="label" for="c-prem">{{ __('Premium (Rs.)') }}</label><input id="c-prem" type="number" min="0" class="input" x-model.number="premium"></div>
            <div><label class="label" for="c-freq">{{ __('Paid') }}</label>
                <select id="c-freq" class="input" x-model.number="freq"><option value="12">{{ __('Monthly') }}</option><option value="4">{{ __('Quarterly') }}</option><option value="2">{{ __('Half-yearly') }}</option><option value="1">{{ __('Yearly') }}</option></select></div>
            <div><label class="label" for="c-rate">{{ __('Commission (%)') }}</label><input id="c-rate" type="number" min="0" max="100" step="0.5" class="input" x-model.number="rate"></div>
            <dl class="space-y-2 rounded-xl bg-surface-2 p-4">
                <div class="flex justify-between"><dt class="text-muted">{{ __('Annual premium') }}</dt><dd class="num font-extrabold" x-text="fmt(annual)"></dd></div>
                <div class="flex justify-between"><dt class="text-muted">{{ __('Your commission / year') }}</dt><dd class="num font-extrabold text-brand-700 dark:text-brand-300" x-text="fmt(commission)"></dd></div>
            </dl>
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="font-display text-lg font-extrabold">{{ __('How much life cover?') }}</h2>
            <p class="text-sm text-muted">{{ __('Income replacement for the family, plus loans and children\'s education, minus cover they already have.') }}</p>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label" for="n-inc">{{ __('Monthly income') }}</label><input id="n-inc" type="number" min="0" class="input" x-model.number="income"></div>
                <div><label class="label" for="n-yrs">{{ __('Years to support') }}</label><input id="n-yrs" type="number" min="1" max="40" class="input" x-model.number="years"></div>
                <div><label class="label" for="n-debt">{{ __('Loans to clear') }}</label><input id="n-debt" type="number" min="0" class="input" x-model.number="debts"></div>
                <div><label class="label" for="n-have">{{ __('Existing cover') }}</label><input id="n-have" type="number" min="0" class="input" x-model.number="existing"></div>
                <div><label class="label" for="n-kids">{{ __('Children') }}</label><input id="n-kids" type="number" min="0" max="10" class="input" x-model.number="children"></div>
                <div><label class="label" for="n-edu">{{ __('Education per child') }}</label><input id="n-edu" type="number" min="0" class="input" x-model.number="edu"></div>
            </div>
            <div class="rounded-xl bg-brand-50 p-4 dark:bg-brand-900/40">
                <p class="text-sm text-muted">{{ __('Suggested additional cover') }}</p>
                <p class="num font-display text-2xl font-extrabold text-brand-700 dark:text-brand-200" x-text="fmt(need)"></p>
            </div>
        </section>

        <section class="card card-pad space-y-4">
            <h2 class="font-display text-lg font-extrabold">{{ __('Savings goal') }}</h2>
            <p class="text-sm text-muted">{{ __('Monthly saving needed for an education or retirement goal.') }}</p>
            <div><label class="label" for="g-t">{{ __('Goal amount (Rs.)') }}</label><input id="g-t" type="number" min="0" class="input" x-model.number="target"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label" for="g-y">{{ __('Years') }}</label><input id="g-y" type="number" min="1" max="40" class="input" x-model.number="yearsToGoal"></div>
                <div><label class="label" for="g-r">{{ __('Growth % / year') }}</label><input id="g-r" type="number" min="0" max="20" step="0.5" class="input" x-model.number="growth"></div>
            </div>
            <div class="rounded-xl bg-lamp-100 p-4 dark:bg-lamp-600/15">
                <p class="text-sm text-muted">{{ __('Save each month') }}</p>
                <p class="num font-display text-2xl font-extrabold text-lamp-600 dark:text-lamp-300" x-text="fmt(Math.ceil(monthly))"></p>
            </div>
            <p class="hint">{{ __('Estimates only. Actual returns depend on the plan.') }}</p>
        </section>
    </div>
</x-app-layout>
