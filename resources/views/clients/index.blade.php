@php use App\Support\Options; use App\Support\Phone; @endphp
<x-app-layout :title="__('Clients')">
    <x-page-header :title="__('Clients')" :subtitle="trans_choice(':count person|:count people', $clients->total())">
        <x-slot:actions>
            <a href="{{ route('clients.import') }}" class="btn-secondary"><x-icon name="upload" class="size-4"/> {{ __('Import') }}</a>
            <a href="{{ route('export', 'clients') }}" class="btn-secondary"><x-icon name="download" class="size-4"/> {{ __('Export') }}</a>
            <a href="{{ route('clients.create') }}" class="btn-primary"><x-icon name="plus" class="size-4"/> {{ __('New client') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
        <a href="{{ route('clients.index', array_filter(['q' => $filters['q'] ?? null])) }}" class="{{ empty($filters['stage']) ? 'btn-primary' : 'btn-secondary' }} btn-sm whitespace-nowrap">{{ __('All') }} <span class="num opacity-70">{{ $counts->sum() }}</span></a>
        @foreach (Options::STAGES as $key => $label)
            <a href="{{ route('clients.index', array_filter(['stage' => $key, 'q' => $filters['q'] ?? null])) }}" class="{{ ($filters['stage'] ?? '') === $key ? 'btn-primary' : 'btn-secondary' }} btn-sm whitespace-nowrap">{{ __($label) }} <span class="num opacity-70">{{ $counts[$key] ?? 0 }}</span></a>
        @endforeach
    </div>

    <form class="mb-4 flex flex-wrap gap-2" method="GET" role="search">
        @if (!empty($filters['stage']))<input type="hidden" name="stage" value="{{ $filters['stage'] }}">@endif
        <div class="relative min-w-56 flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted"/>
            <input name="q" value="{{ $filters['q'] ?? '' }}" class="input pl-9" placeholder="{{ __('Name, phone, e-mail or city') }}" aria-label="{{ __('Search') }}">
        </div>
        <select name="sort" class="input w-auto" aria-label="{{ __('Sort') }}" x-data @change="$el.form.requestSubmit()">
            <option value="name" @selected(($filters['sort'] ?? 'name') === 'name')>{{ __('Name A–Z') }}</option>
            <option value="recent" @selected(($filters['sort'] ?? '') === 'recent')>{{ __('Newest first') }}</option>
            <option value="contacted" @selected(($filters['sort'] ?? '') === 'contacted')>{{ __('Recently contacted') }}</option>
        </select>
        <button class="btn-secondary">{{ __('Search') }}</button>
    </form>

    <div class="card overflow-hidden">
        @if ($clients->isEmpty())
            <x-empty icon="users" :title="__('No clients found')" :text="__('Add your first client, or import your existing list from Excel.')">
                <div class="mt-2 flex gap-2"><a href="{{ route('clients.create') }}" class="btn-primary btn-sm">{{ __('New client') }}</a><a href="{{ route('clients.import') }}" class="btn-secondary btn-sm">{{ __('Import') }}</a></div>
            </x-empty>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Contact') }}</th><th>{{ __('Stage') }}</th><th class="text-right">{{ __('Policies') }}</th><th>{{ __('Last contact') }}</th></tr></thead>
                    <tbody>
                    @foreach ($clients as $c)
                        <tr>
                            <td>
                                <a href="{{ route('clients.show', $c) }}" class="flex items-center gap-3">
                                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-100 text-xs font-extrabold text-brand-700 dark:bg-brand-900 dark:text-brand-200">{{ mb_strtoupper(mb_substr($c->full_name, 0, 1)) }}</span>
                                    <span><span class="block font-bold hover:underline">{{ $c->full_name }}</span><span class="block text-xs text-muted">{{ collect([$c->occupation, $c->city])->filter()->implode(' · ') }}</span></span>
                                </a>
                            </td>
                            <td class="whitespace-nowrap text-sm">{{ Phone::display($c->phone) }}@if($c->email)<span class="block text-xs text-muted">{{ $c->email }}</span>@endif</td>
                            <td><x-stage-badge :stage="$c->stage"/> @foreach ($c->tags ?? [] as $t)<span class="badge-gray">{{ $t }}</span>@endforeach</td>
                            <td class="num text-right font-semibold">{{ $c->active_policies_count }}</td>
                            <td class="whitespace-nowrap text-xs text-muted">{{ $c->last_contacted_at?->diffForHumans() ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <div class="mt-4">{{ $clients->links() }}</div>
</x-app-layout>
