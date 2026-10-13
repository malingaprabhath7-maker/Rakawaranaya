@php $overdue = !$task->done_at && $task->due_on->lt(today()); @endphp
<div class="flex items-center gap-3 border-b border-line px-5 py-3 last:border-0">
    <form method="POST" action="{{ route('tasks.update', $task) }}">@csrf @method('PATCH')
        <input type="hidden" name="toggle" value="1">
        <button class="grid size-6 place-items-center rounded-lg border-2 {{ $task->done_at ? 'border-brand-500 bg-brand-500 text-white' : 'border-line hover:border-brand-400' }}" aria-label="{{ $task->done_at ? __('Mark as not done') : __('Mark as done') }}">
            @if ($task->done_at)<x-icon name="check" class="size-3.5"/>@endif
        </button>
    </form>
    <div class="min-w-0 flex-1">
        <p class="truncate font-semibold {{ $task->done_at ? 'text-muted line-through' : '' }}">{{ $task->title }}</p>
        <p class="truncate text-xs text-muted">
            {{ __(\App\Support\Options::TASK_TYPES[$task->type] ?? $task->type) }}
            @if ($task->client) · <a href="{{ route('clients.show', $task->client) }}" class="hover:underline">{{ $task->client->full_name }}</a>@endif
        </p>
    </div>
    <span class="num text-right text-xs font-semibold {{ $overdue ? 'text-red-600 dark:text-red-400' : 'text-muted' }}">
        {{ $task->due_on->isToday() ? __('Today') : ($task->due_on->isTomorrow() ? __('Tomorrow') : $task->due_on->format('M j')) }}
        @if ($task->due_time)<span class="block">{{ \Illuminate\Support\Str::substr($task->due_time, 0, 5) }}</span>@endif
    </span>
    @unless ($compact ?? false)
        <form method="POST" action="{{ route('tasks.destroy', $task) }}" x-data @submit="confirm(@js(__('Delete this task?'))) || $event.preventDefault()">@csrf @method('DELETE')
            <button class="btn-ghost btn-sm" aria-label="{{ __('Delete') }}"><x-icon name="trash" class="size-4"/></button>
        </form>
    @endunless
</div>
