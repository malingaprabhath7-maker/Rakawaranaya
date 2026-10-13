@php use App\Support\Money; use App\Support\Options; @endphp
<x-app-layout :title="__('Policies')">
    <x-page-header :title="__('Policies')" :subtitle="trans_choice(':count policy|:count policies', $policies->total())">
        <x-slot:actions>
            <a href="{{ route('export', 'policies') }}" class="btn-secondary"><x-icon name="download" class="size-4"/> {{ __('Export') }}</a>
            <a href="{{ route('policies.create') }}" class="btn-primary"><x-icon name="plus" class="size-4"/> {{ __('New policy') }}</a>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="card card-pad mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_1fr_auto]" role="search">
        <input name="q" value="{{ $filters['q'] ?? '' }}" class="input" placeholder="{{ __('Client, policy no., plan or vehicle') }}" aria-label="{{ __('Search') }}">
        <select name="type" class="input" aria-label="{{ __('Type') }}"><option value="">{{ __('All types') }}</option>@foreach (Options::POLICY_TYPES as $k => $l)<option value="{{ $k }}" @selected(($filters['type'] ?? '') === $k)>{{ __($l) }}</option>@endforeach</select>
        <select name="status" class="input" aria-label="{{ __('Status') }}"><option value="">{{ __('All statuses') }}</option>@foreach (Options::POLICY_STATUSES as $k => $l)<option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ __($l) }}</option>@endforeach</select>
        <select name="insurer" class="input" aria-label="{{ __('Insurer') }}"><option value="">{{ __('All insurers') }}</option>@foreach ($insurers as $i)<option value="{{ $i }}" @selected(($filters['insurer'] ?? '') === $i)>{{ $i }}</option>@endforeach</select>
        <button class="btn-secondary">{{ __('Filter') }}</button>
    </form>

    <div class="card overflow-hidden">
        @if ($policies->isEmpty())
            <x-empty icon="shield" :title="__('No policies found')"><a href="{{ route('policies.create') }}" class="btn-primary btn-sm mt-2">{{ __('New policy') }}</a></x-empty>
        @else
            <div class="table-wrap"><table class="table">
                <thead><tr><th>{{ __('Client') }}</th><th>{{ __('Policy') }}</th><th class="text-right">{{ __('Premium') }}</th><th>{{ __('Next due') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                @foreach ($policies as $p)
                    <tr>
                        <td><a href="{{ route('clients.show', $p->client_id) }}" class="font-bold hover:underline">{{ $p->client?->full_name }}</a></td>
                        <td><a href="{{ route('policies.show', $p) }}" class="block hover:underline"><span class="font-semibold">{{ $p->label }}</span><span class="block text-xs text-muted">{{ $p->policy_number }} @if($p->vehicle_number)· {{ $p->vehicle_number }}@endif</span></a></td>
                        <td class="num whitespace-nowrap text-right font-semibold">{{ Money::format($p->premium) }}<span class="block text-xs font-normal text-muted">{{ __(Options::FREQUENCIES[$p->frequency] ?? '') }}</span></td>
                        <td class="whitespace-nowrap text-sm"><x-due-date :date="$p->status === 'active' ? $p->next_due_date : null"/></td>
                        <td class="whitespace-nowrap"><x-status-badge :policy="$p"/></td>
                    </tr>
                @endforeach
                </tbody>
            </table></div>
        @endif
    </div>
    <div class="mt-4">{{ $policies->links() }}</div>
</x-app-layout>
