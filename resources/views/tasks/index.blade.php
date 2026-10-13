@php use App\Support\Options; @endphp
<x-app-layout :title="__('Tasks')">
    <x-page-header :title="__('Tasks')" :subtitle="__('Calls, meetings and follow-ups.')"/>
    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        <section class="card">
            <div class="flex gap-2 border-b border-line px-5 py-3">
                <a href="{{ route('tasks.index') }}" class="{{ $show === 'open' ? 'btn-primary' : 'btn-secondary' }} btn-sm">{{ __('To do') }}</a>
                <a href="{{ route('tasks.index', ['show' => 'done']) }}" class="{{ $show === 'done' ? 'btn-primary' : 'btn-secondary' }} btn-sm">{{ __('Done') }}</a>
            </div>
            @forelse ($tasks as $task)@include('tasks._row', ['task' => $task])@empty<x-empty icon="tasks" :title="$show === 'done' ? __('No finished tasks yet') : __('You are all caught up')"/>@endforelse
            <div class="p-4">{{ $tasks->links() }}</div>
        </section>
        <form method="POST" action="{{ route('tasks.store') }}" class="card card-pad h-fit space-y-4">
            @csrf
            <h2 class="font-display text-lg font-extrabold">{{ __('New task') }}</h2>
            <x-field name="title" :label="__('What needs doing?')" required maxlength="160"/>
            <div class="grid grid-cols-2 gap-3">
                <x-field name="due_on" type="date" :label="__('Date')" :value="today()->format('Y-m-d')" required/>
                <x-field name="due_time" type="time" :label="__('Time')"/>
            </div>
            <x-select name="type" :label="__('Type')" :options="Options::t(Options::TASK_TYPES)" value="follow_up" required/>
            <x-select name="client_id" :label="__('Client')" :options="$clients->pluck('full_name', 'id')" :placeholder="__('None')"/>
            <x-field name="notes" type="textarea" :label="__('Notes')"/>
            <button class="btn-primary w-full"><x-icon name="plus" class="size-4"/> {{ __('Add task') }}</button>
        </form>
    </div>
</x-app-layout>
