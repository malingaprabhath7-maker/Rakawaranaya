@php use App\Support\Options; @endphp
@csrf
<div class="grid gap-6 lg:grid-cols-[1fr_20rem]" x-data="{ type: @js(old('type', $policy->type)), freq: @js(old('frequency', $policy->frequency)) }">
    <div class="space-y-6">
        <section class="card card-pad">
            <h2 class="mb-4 font-display text-lg font-extrabold">{{ __('Policy') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-select name="client_id" :label="__('Client')" :options="$clients->pluck('full_name', 'id')" :value="$policy->client_id" :placeholder="__('Choose a client')" required class="sm:col-span-2"/>
                <x-field name="insurer" :label="__('Insurance company')" :value="$policy->insurer" list="insurer-list" required/>
                <datalist id="insurer-list">@foreach (Options::INSURERS as $i)<option value="{{ $i }}">@endforeach</datalist>
                <div>
                    <label class="label" for="f-type">{{ __('Type') }} <span class="text-red-500">*</span></label>
                    <select id="f-type" name="type" class="input" x-model="type" required>@foreach (Options::POLICY_TYPES as $k => $l)<option value="{{ $k }}">{{ __($l) }}</option>@endforeach</select>
                </div>
                <x-field name="plan_name" :label="__('Plan name')" :value="$policy->plan_name" :placeholder="__('e.g. Family Shield')"/>
                <x-field name="policy_number" :label="__('Policy number')" :value="$policy->policy_number" :hint="__('Leave empty while it is a proposal')"/>
                <div x-show="type === 'motor'" x-cloak><x-field name="vehicle_number" :label="__('Vehicle number')" :value="$policy->vehicle_number" placeholder="CAB-1234"/></div>
                <x-field name="sum_assured" type="number" min="0" step="1000" :label="__('Sum assured / cover (Rs.)')" :value="$policy->sum_assured"/>
            </div>
        </section>
        <section class="card card-pad">
            <h2 class="mb-4 font-display text-lg font-extrabold">{{ __('Premium & dates') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="premium" type="number" min="1" step="0.01" :label="__('Premium (Rs.)')" :value="$policy->premium" required/>
                <div>
                    <label class="label" for="f-frequency">{{ __('Paid') }} <span class="text-red-500">*</span></label>
                    <select id="f-frequency" name="frequency" class="input" x-model="freq" required>@foreach (Options::FREQUENCIES as $k => $l)<option value="{{ $k }}">{{ __($l) }}</option>@endforeach</select>
                </div>
                <x-field name="start_date" type="date" :label="__('Start date')" :value="$policy->start_date?->format('Y-m-d')" required/>
                <div x-show="freq !== 'single'"><x-field name="next_due_date" type="date" :label="__('Next premium due')" :value="$policy->next_due_date?->format('Y-m-d')" :hint="__('Leave empty to use the start date')"/></div>
                <x-field name="end_date" type="date" :label="__('Maturity / renewal date')" :value="$policy->end_date?->format('Y-m-d')" :hint="__('For motor and medical, the renewal date')"/>
            </div>
        </section>
    </div>
    <div class="space-y-6">
        <section class="card card-pad space-y-4">
            <x-select name="status" :label="__('Status')" :options="Options::t(Options::POLICY_STATUSES)" :value="$policy->status" required/>
            <x-field name="commission_rate" type="number" min="0" max="100" step="0.1" :label="__('Your commission (%)')" :value="$policy->commission_rate" :hint="__('Used to work out commission on each payment')"/>
            <x-field name="notes" type="textarea" rows="5" :label="__('Notes')" :value="$policy->notes" :placeholder="__('Nominees, riders, medical notes…')"/>
        </section>
        <div class="flex gap-2">
            <button class="btn-primary flex-1">{{ __('Save policy') }}</button>
            <a href="{{ $policy->exists ? route('policies.show', $policy) : url()->previous() }}" class="btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </div>
</div>
