@php use App\Support\Money; use App\Support\Options; @endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>@include('partials.head', ['title' => __('Monthly report')])</head>
<body class="bg-white text-black">
<main class="mx-auto max-w-3xl p-8 text-sm">
    <div class="no-print mb-6 flex gap-2"><button class="btn-primary" x-data @click="window.print()"><x-icon name="print" class="size-4"/> {{ __('Print / Save as PDF') }}</button><a href="{{ route('reports.index', ['month' => $month]) }}" class="btn-secondary">{{ __('Back') }}</a></div>
    <header class="flex items-start justify-between border-b-2 border-black pb-4">
        <div><p class="font-display text-2xl font-extrabold">{{ __('Monthly report') }} — {{ $from->translatedFormat('F Y') }}</p>
            <p class="mt-1">{{ $user->name }}@if($user->agent_code) · {{ $user->agent_code }}@endif @if($user->company) · {{ $user->company }}@endif</p></div>
        <p class="text-right text-xs">{{ __('Generated') }} {{ now()->format('Y-m-d H:i') }}</p>
    </header>
    <table class="mt-6 w-full">
        <tr><td class="py-1">{{ __('Collected') }}</td><td class="num py-1 text-right font-bold">{{ Money::format($collections['collected']) }}</td></tr>
        <tr><td class="py-1">{{ __('Commission') }}</td><td class="num py-1 text-right font-bold">{{ Money::format($collections['commission']) }}</td></tr>
        <tr><td class="py-1">{{ __('New business (annual)') }}</td><td class="num py-1 text-right font-bold">{{ Money::format($newBusiness['annual_premium']) }} ({{ $newBusiness['count'] }})</td></tr>
        @if ($target)<tr><td class="py-1">{{ __('Monthly target') }}</td><td class="num py-1 text-right font-bold">{{ Money::format($target) }} · {{ round($newBusiness['annual_premium'] / $target * 100) }}%</td></tr>@endif
    </table>
    <h2 class="mt-8 font-bold">{{ __('Payments') }}</h2>
    <table class="mt-2 w-full border-collapse">
        <thead><tr class="border-b border-black text-left"><th class="py-1.5">{{ __('Date') }}</th><th>{{ __('Client') }}</th><th>{{ __('Policy') }}</th><th class="text-right">{{ __('Amount') }}</th><th class="text-right">{{ __('Commission') }}</th></tr></thead>
        <tbody>@foreach ($payments as $p)<tr class="border-b border-gray-300"><td class="num py-1.5">{{ $p->paid_on->format('Y-m-d') }}</td><td>{{ $p->policy?->client?->full_name }}</td><td>{{ $p->policy?->policy_number }}</td><td class="num text-right">{{ Money::format($p->amount) }}</td><td class="num text-right">{{ Money::format($p->commission) }}</td></tr>@endforeach</tbody>
    </table>
</main>
</body>
</html>
