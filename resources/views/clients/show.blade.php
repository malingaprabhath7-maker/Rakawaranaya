@php
    use App\Support\Money; use App\Support\Options; use App\Support\Phone; use App\Support\WhatsApp;
    $user = auth()->user();
    $active = $client->policies->where('status', 'active');
    $annual = $active->sum(fn ($p) => $p->annualPremium());
    $cover = $active->where('type', '!=', 'motor')->sum('sum_assured');
    $days = $client->daysToBirthday();
@endphp
<x-app-layout :title="$client->full_name">
    <div class="mb-6 flex flex-wrap items-start gap-4">
        <span class="grid size-16 shrink-0 place-items-center rounded-2xl bg-brand-600 font-display text-2xl font-extrabold text-white dark:bg-brand-500">{{ mb_strtoupper(mb_substr($client->full_name, 0, 1)) }}</span>
        <div class="min-w-0 flex-1">
            <h1 class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $client->full_name }}</h1>
            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-sm text-muted">
                <x-stage-badge :stage="$client->stage"/>
                @foreach ($client->tags ?? [] as $t)<span class="badge-gray">{{ $t }}</span>@endforeach
                @if ($client->age)<span>{{ trans_choice(':count year|:count years', $client->age) }}</span>@endif
                @if ($client->occupation)<span>· {{ $client->occupation }}</span>@endif
                @if ($client->city)<span>· {{ $client->city }}</span>@endif
                @if ($days !== null && $days <= 7)<span class="badge-lamp"><x-icon name="cake" class="size-3.5"/> {{ $days === 0 ? __('Birthday today!') : trans_choice('Birthday in :count day|Birthday in :count days', $days) }}</span>@endif
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-wa-button :href="WhatsApp::link($user, $client, 'follow_up')" :client="$client" :small="false"/>
            @if ($client->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $client->phone) }}" class="btn-secondary" x-data="touch('{{ route('clients.touch', $client) }}')" @click="ping()"><x-icon name="phone" class="size-4"/> {{ __('Call') }}</a>@endif
            <a href="{{ route('clients.edit', $client) }}" class="btn-secondary"><x-icon name="edit" class="size-4"/> {{ __('Edit') }}</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                <x-stat :label="__('Active policies')" :value="$active->count()" icon="shield"/>
                <x-stat :label="__('Annual premium')" :value="Money::format($annual)" icon="money" tone="lamp"/>
                <x-stat :label="__('Life & health cover')" :value="Money::short($cover)" icon="heart" tone="blue" class="col-span-2 md:col-span-1"/>
            </div>

            <section class="card">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h2 class="font-display text-lg font-extrabold">{{ __('Policies') }}</h2>
                    <a href="{{ route('policies.create', ['client' => $client->id]) }}" class="btn-primary btn-sm"><x-icon name="plus" class="size-4"/> {{ __('Add policy') }}</a>
                </div>
                @forelse ($client->policies as $p)
                    <a href="{{ route('policies.show', $p) }}" class="flex flex-wrap items-center gap-3 border-b border-line px-5 py-3.5 transition last:border-0 hover:bg-surface-2/60">
                        <span class="grid size-10 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-200"><x-icon :name="$p->type === 'motor' ? 'car' : ($p->type === 'health' ? 'heart' : 'shield')" class="size-5"/></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-bold">{{ $p->label }}</span>
                            <span class="block truncate text-xs text-muted">{{ $p->policy_number ?: __('No policy number yet') }} · {{ Money::format($p->premium) }} / {{ __(Options::FREQUENCIES[$p->frequency] ?? $p->frequency) }}</span>
                        </span>
                        <span class="text-right text-sm"><x-due-date :date="$p->next_due_date"/></span>
                        <span class="flex flex-col items-end gap-1"><x-status-badge :policy="$p"/></span>
                    </a>
                @empty
                    <x-empty icon="shield" :title="__('No policies yet')" :text="__('Add the first policy to start tracking premiums and renewals.')"/>
                @endforelse
            </section>

            <section class="card">
                <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Recent payments') }}</h2>
                @if ($payments->isEmpty())
                    <x-empty icon="money" :title="__('No payments recorded')"/>
                @else
                    <div class="table-wrap"><table class="table">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Policy') }}</th><th class="text-right">{{ __('Amount') }}</th><th>{{ __('Method') }}</th></tr></thead>
                        <tbody>@foreach ($payments as $pay)<tr>
                            <td class="num whitespace-nowrap">{{ $pay->paid_on->format('Y-m-d') }}</td>
                            <td class="text-sm">{{ $pay->policy?->label }}</td>
                            <td class="num text-right font-semibold">{{ Money::format($pay->amount) }}</td>
                            <td class="text-sm text-muted">{{ __(Options::PAYMENT_METHODS[$pay->method] ?? $pay->method) }}</td>
                        </tr>@endforeach</tbody>
                    </table></div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card card-pad">
                <h2 class="mb-3 font-display text-lg font-extrabold">{{ __('Details') }}</h2>
                <dl class="space-y-3 text-sm">
                    @if ($client->phone)<div><dt class="text-xs font-semibold text-muted">{{ __('Mobile') }}</dt><dd class="num font-semibold">{{ Phone::display($client->phone) }}</dd></div>@endif
                    @if ($client->whatsapp && $client->whatsapp !== $client->phone)<div><dt class="text-xs font-semibold text-muted">WhatsApp</dt><dd class="num font-semibold">{{ Phone::display($client->whatsapp) }}</dd></div>@endif
                    @if ($client->email)<div><dt class="text-xs font-semibold text-muted">{{ __('E-mail') }}</dt><dd class="break-all font-semibold">{{ $client->email }}</dd></div>@endif
                    @if ($client->nic)
                        <div x-data="nicReveal('{{ route('clients.nic', $client) }}')">
                            <dt class="text-xs font-semibold text-muted">{{ __('NIC') }}</dt>
                            <dd class="flex items-center gap-2"><span class="num font-semibold" x-text="shown || @js($client->masked_nic)">{{ $client->masked_nic }}</span>
                                <button type="button" class="btn-ghost btn-sm" @click="reveal()" :aria-label="shown ? @js(__('Hide')) : @js(__('Show'))"><x-icon name="eye" class="size-4"/></button></dd>
                        </div>
                    @endif
                    @if ($client->date_of_birth)<div><dt class="text-xs font-semibold text-muted">{{ __('Date of birth') }}</dt><dd class="num font-semibold">{{ $client->date_of_birth->format('Y-m-d') }}</dd></div>@endif
                    @if ($client->marital_status || $client->dependents !== null)<div><dt class="text-xs font-semibold text-muted">{{ __('Family') }}</dt><dd class="font-semibold">{{ collect([$client->marital_status ? __(Options::MARITAL[$client->marital_status]) : null, $client->dependents !== null ? trans_choice(':count dependent|:count dependents', $client->dependents) : null])->filter()->implode(' · ') }}</dd></div>@endif
                    @if ($client->monthly_income)<div><dt class="text-xs font-semibold text-muted">{{ __('Monthly income') }}</dt><dd class="num font-semibold">{{ Money::format($client->monthly_income) }}</dd></div>@endif
                    @if ($client->address)<div><dt class="text-xs font-semibold text-muted">{{ __('Address') }}</dt><dd class="whitespace-pre-line">{{ $client->address }}</dd></div>@endif
                    @if ($client->source)<div><dt class="text-xs font-semibold text-muted">{{ __('Source') }}</dt><dd>{{ __(Options::SOURCES[$client->source] ?? $client->source) }}</dd></div>@endif
                    <div><dt class="text-xs font-semibold text-muted">{{ __('Last contact') }}</dt><dd>{{ $client->last_contacted_at?->diffForHumans() ?? '—' }}</dd></div>
                </dl>
                @if ($client->notes)<div class="mt-4 rounded-xl bg-surface-2 p-3 text-sm whitespace-pre-line">{{ $client->notes }}</div>@endif
            </section>

            <section class="card">
                <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Tasks') }}</h2>
                @foreach ($client->tasks as $task)@include('tasks._row', ['task' => $task])@endforeach
                <form method="POST" action="{{ route('tasks.store') }}" class="space-y-3 p-5">
                    @csrf
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input name="title" class="input" placeholder="{{ __('e.g. Call about education plan') }}" required maxlength="160" aria-label="{{ __('Task') }}">
                    <div class="flex gap-2">
                        <input type="date" name="due_on" value="{{ today()->addDay()->format('Y-m-d') }}" class="input" required aria-label="{{ __('Date') }}">
                        <select name="type" class="input" aria-label="{{ __('Type') }}">@foreach (Options::TASK_TYPES as $k => $l)<option value="{{ $k }}" @selected($k === 'follow_up')>{{ __($l) }}</option>@endforeach</select>
                    </div>
                    <button class="btn-secondary w-full"><x-icon name="plus" class="size-4"/> {{ __('Add task') }}</button>
                </form>
            </section>

            <section class="card">
                <h2 class="border-b border-line px-5 py-4 font-display text-lg font-extrabold">{{ __('Documents') }}</h2>
                @foreach ($client->documents as $doc)
                    <div class="flex items-center gap-3 border-b border-line px-5 py-3">
                        <x-icon name="doc" class="size-5 text-muted"/>
                        <a href="{{ route('documents.show', $doc) }}" class="min-w-0 flex-1 truncate text-sm font-semibold hover:underline">{{ $doc->name }}</a>
                        <span class="text-xs text-muted">{{ number_format($doc->size / 1024) }} KB</span>
                        <form method="POST" action="{{ route('documents.destroy', $doc) }}" x-data @submit="confirm(@js(__('Delete this document?'))) || $event.preventDefault()">@csrf @method('DELETE')<button class="btn-ghost btn-sm" aria-label="{{ __('Delete') }}"><x-icon name="trash" class="size-4"/></button></form>
                    </div>
                @endforeach
                <form method="POST" action="{{ route('documents.store', $client) }}" enctype="multipart/form-data" class="space-y-3 p-5">
                    @csrf
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp" class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-surface-2 file:px-3 file:py-2 file:font-semibold" aria-label="{{ __('File') }}">
                    <input name="name" class="input" placeholder="{{ __('Name (optional), e.g. Proposal form') }}" maxlength="160" aria-label="{{ __('Name') }}">
                    @error('file')<p class="error">{{ $message }}</p>@enderror
                    <button class="btn-secondary w-full"><x-icon name="upload" class="size-4"/> {{ __('Upload') }}</button>
                    <p class="hint">{{ __('PDF or photo, up to 5 MB. Stored privately.') }}</p>
                </form>
            </section>
        </aside>
    </div>
</x-app-layout>
