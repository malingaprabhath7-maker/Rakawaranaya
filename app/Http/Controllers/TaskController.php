<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $show = $request->query('show', 'open');
        $tasks = $request->user()->tasks()->with('client')
            ->when($show === 'open', fn ($q) => $q->whereNull('done_at')->orderBy('due_on')->orderBy('due_time'))
            ->when($show === 'done', fn ($q) => $q->whereNotNull('done_at')->latest('done_at'))
            ->paginate(30)->withQueryString();

        return view('tasks.index', [
            'tasks' => $tasks,
            'show' => $show,
            'clients' => $request->user()->clients()->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $request->user()->tasks()->create($data);

        return back()->with('success', __('Task added.'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);
        if ($request->has('toggle')) {
            $task->update(['done_at' => $task->done_at ? null : now()]);
            if ($task->done_at && $task->client) {
                $task->client->update(['last_contacted_at' => now()]);
            }

            return back();
        }
        $task->update($this->validated($request));

        return back()->with('success', __('Task updated.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);
        $task->delete();

        return back()->with('success', __('Task deleted.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::in(array_keys(Options::TASK_TYPES))],
            'due_on' => ['required', 'date'],
            'due_time' => ['nullable', 'date_format:H:i'],
            'client_id' => ['nullable', 'string', Rule::exists('clients', 'id')->where('user_id', $request->user()->id)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
