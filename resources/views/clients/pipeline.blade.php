@php use App\Support\Options; use App\Support\Money; @endphp
<x-app-layout :title="__('Pipeline')">
    <x-page-header :title="__('Sales pipeline')" :subtitle="__('Drag a card to move a person to the next stage.')">
        <x-slot:actions><a href="{{ route('clients.create', ['stage' => 'lead']) }}" class="btn-primary"><x-icon name="plus" class="size-4"/> {{ __('New lead') }}</a></x-slot:actions>
    </x-page-header>

    <div x-data="pipeline()" class="-mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:px-0 lg:grid lg:grid-cols-4 lg:overflow-visible">
        @foreach (['lead', 'contacted', 'proposal', 'client'] as $stage)
            @php $cards = $columns[$stage] ?? collect(); @endphp
            <section data-column class="w-72 shrink-0 snap-start rounded-2xl bg-surface-2/70 p-3 transition lg:w-auto"
                     :class="over === '{{ $stage }}' ? 'ring-2 ring-brand-400' : ''"
                     @dragover.prevent="over = '{{ $stage }}'" @dragleave="over = null" @drop.prevent="drop('{{ $stage }}', $event)">
                <h2 class="mb-3 flex items-center justify-between px-1 text-sm font-extrabold">
                    <span>{{ __(Options::STAGES[$stage]) }}</span>
                    <span data-count class="num rounded-full bg-surface px-2 py-0.5 text-xs text-muted">{{ $cards->count() }}</span>
                </h2>
                <div data-cards class="min-h-24 space-y-2">
                    @foreach ($cards->take(60) as $c)
                        <article id="card-{{ $c->id }}" data-card draggable="true" @dragstart="start($event, '{{ $c->id }}', '{{ route('clients.stage', $c) }}')"
                                 class="card cursor-grab p-3 shadow-xs active:cursor-grabbing">
                            <a href="{{ route('clients.show', $c) }}" class="block font-bold hover:underline">{{ $c->full_name }}</a>
                            <p class="mt-0.5 text-xs text-muted">{{ collect([$c->occupation, $c->city])->filter()->implode(' · ') ?: '—' }}</p>
                            @if ($c->notes)<p class="mt-2 line-clamp-2 text-xs">{{ $c->notes }}</p>@endif
                            <div class="mt-2 flex items-center justify-between text-[0.7rem] text-muted">
                                <span>{{ $c->updated_at->diffForHumans(short: true) }}</span>
                                {{-- keyboard / touch alternative to dragging --}}
                                <form method="POST" action="{{ route('clients.stage', $c) }}" class="flex items-center gap-1">@csrf @method('PATCH')
                                    <label class="sr-only" for="st-{{ $c->id }}">{{ __('Stage') }}</label>
                                    <select id="st-{{ $c->id }}" name="stage" class="rounded-md border border-line bg-surface px-1 py-0.5 text-[0.7rem]" x-data @change="$el.form.submit()">
                                        @foreach (Options::STAGES as $k => $l)<option value="{{ $k }}" @selected($k === $stage)>{{ __($l) }}</option>@endforeach
                                    </select>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-app-layout>
