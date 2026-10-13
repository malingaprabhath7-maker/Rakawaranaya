@php use App\Support\Options; @endphp
@csrf
<div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
    <div class="space-y-6">
        <section class="card card-pad">
            <h2 class="mb-4 font-display text-lg font-extrabold">{{ __('Personal details') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="full_name" :label="__('Full name')" :value="$client->full_name" required class="sm:col-span-2" autocomplete="off"/>
                <x-field name="nic" :label="__('NIC number')" :value="$client->nic" :hint="__('Stored encrypted. Old (123456789V) or new (12 digits).')" autocomplete="off"/>
                <x-field name="date_of_birth" type="date" :label="__('Date of birth')" :value="$client->date_of_birth?->format('Y-m-d')"/>
                <x-select name="gender" :label="__('Gender')" :options="Options::t(Options::GENDERS)" :value="$client->gender" :placeholder="__('Select')"/>
                <x-select name="marital_status" :label="__('Marital status')" :options="Options::t(Options::MARITAL)" :value="$client->marital_status" :placeholder="__('Select')"/>
                <x-field name="dependents" type="number" min="0" max="20" :label="__('Dependents')" :value="$client->dependents"/>
                <x-field name="occupation" :label="__('Occupation')" :value="$client->occupation"/>
                <x-field name="monthly_income" type="number" min="0" step="100" :label="__('Monthly income (Rs.)')" :value="$client->monthly_income"/>
            </div>
        </section>
        <section class="card card-pad">
            <h2 class="mb-4 font-display text-lg font-extrabold">{{ __('Contact') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-field name="phone" type="tel" :label="__('Mobile')" :value="$client->phone" placeholder="077 123 4567"/>
                <x-field name="whatsapp" type="tel" :label="__('WhatsApp')" :value="$client->whatsapp" :hint="__('Leave empty if same as mobile')"/>
                <x-field name="email" type="email" :label="__('E-mail')" :value="$client->email" class="sm:col-span-2"/>
                <x-field name="address" type="textarea" :label="__('Address')" :value="$client->address" class="sm:col-span-2"/>
                <x-field name="city" :label="__('City / Town')" :value="$client->city"/>
            </div>
        </section>
    </div>
    <div class="space-y-6">
        <section class="card card-pad space-y-4">
            <h2 class="font-display text-lg font-extrabold">{{ __('Sales') }}</h2>
            <x-select name="stage" :label="__('Stage')" :options="Options::t(Options::STAGES)" :value="$client->stage ?? 'lead'" required/>
            <x-select name="source" :label="__('Where did they come from?')" :options="Options::t(Options::SOURCES)" :value="$client->source" :placeholder="__('Select')"/>
            <x-field name="tags" :label="__('Tags')" :value="implode(', ', $client->tags ?? [])" :hint="__('Separate with commas, e.g. VIP, Family plan')"/>
            <x-field name="notes" type="textarea" rows="5" :label="__('Notes')" :value="$client->notes"/>
        </section>
        <div class="flex gap-2">
            <button class="btn-primary flex-1">{{ __('Save client') }}</button>
            <a href="{{ $client->exists ? route('clients.show', $client) : route('clients.index') }}" class="btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </div>
</div>
