@php use App\Support\Money; @endphp
<x-mail::message>
# {{ __('Good morning, :name', ['name' => explode(' ', $user->name)[0]]) }}

{{ __('Here is what needs your attention today.') }}

@if ($data['due']->isNotEmpty())
## {{ __('Premiums due') }}
@foreach ($data['due'] as $p)
- **{{ $p->client?->full_name }}** — {{ $p->label }}, {{ Money::format($p->premium) }} ({{ $p->next_due_date->format('Y-m-d') }})
@endforeach
@endif

@if ($data['renewals']->isNotEmpty())
## {{ __('Renewals') }}
@foreach ($data['renewals'] as $p)
- **{{ $p->client?->full_name }}** — {{ $p->label }} ({{ $p->end_date->format('Y-m-d') }})
@endforeach
@endif

@if ($data['birthdays']->isNotEmpty())
## {{ __('Birthdays today') }}
@foreach ($data['birthdays'] as $c)
- 🎂 **{{ $c->full_name }}**
@endforeach
@endif

@if ($data['tasks']->isNotEmpty())
## {{ __('Tasks') }}
@foreach ($data['tasks'] as $t)
- {{ $t->title }}@if($t->client) — {{ $t->client->full_name }}@endif
@endforeach
@endif

<x-mail::button :url="route('due')">
{{ __('Open today\'s list') }}
</x-mail::button>

{{ __('Rakawarana') }}
</x-mail::message>
