<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use App\Support\Audit;
use App\Support\Options;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'stage' => ['nullable', Rule::in(array_keys(Options::STAGES))],
            'tag' => ['nullable', 'string', 'max:30'],
            'sort' => ['nullable', Rule::in(['name', 'recent', 'contacted'])],
        ]);

        $clients = $request->user()->clients()
            ->search($filters['q'] ?? null)
            ->when($filters['stage'] ?? null, fn ($q, $s) => $q->where('stage', $s))
            ->when($filters['tag'] ?? null, fn ($q, $t) => $q->whereJsonContains('tags', $t))
            ->withCount(['policies as active_policies_count' => fn ($q) => $q->where('status', 'active')])
            ->when(($filters['sort'] ?? 'name') === 'name', fn ($q) => $q->orderBy('full_name'))
            ->when(($filters['sort'] ?? null) === 'recent', fn ($q) => $q->latest())
            ->when(($filters['sort'] ?? null) === 'contacted', fn ($q) => $q->orderByDesc('last_contacted_at'))
            ->paginate(20)->withQueryString();

        return view('clients.index', [
            'clients' => $clients,
            'filters' => $filters,
            'counts' => $request->user()->clients()->selectRaw('stage, count(*) c')->groupBy('stage')->pluck('c', 'stage'),
        ]);
    }

    public function pipeline(Request $request): View
    {
        $clients = $request->user()->clients()
            ->whereIn('stage', ['lead', 'contacted', 'proposal', 'client'])
            ->orderByDesc('updated_at')->get()->groupBy('stage');

        return view('clients.pipeline', ['columns' => $clients]);
    }

    public function create(Request $request): View
    {
        return view('clients.create', ['client' => new Client(['stage' => $request->query('stage', 'lead')])]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = $request->user()->clients()->create($request->validated());
        Audit::log('client.created', $client);

        return redirect()->route('clients.show', $client)->with('success', __('Client saved.'));
    }

    public function show(Request $request, Client $client): View
    {
        $this->authorize('view', $client);
        $client->load(['policies' => fn ($q) => $q->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'proposal' THEN 1 ELSE 2 END")->orderBy('next_due_date'),
            'tasks' => fn ($q) => $q->orderByRaw('done_at IS NOT NULL')->orderBy('due_on')->limit(20),
            'documents' => fn ($q) => $q->latest()]);
        $payments = $request->user()->payments()
            ->whereIn('insurance_policy_id', $client->policies->pluck('id'))
            ->with('policy')->latest('paid_on')->limit(15)->get();

        return view('clients.show', compact('client', 'payments'));
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);
        $client->update($request->validated());
        Audit::log('client.updated', $client);

        return redirect()->route('clients.show', $client)->with('success', __('Client updated.'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);
        $client->delete();
        Audit::log('client.deleted', $client, $client->full_name);

        return redirect()->route('clients.index')->with('success', __('Client moved to trash.'));
    }

    /** Pipeline: move a card to another stage */
    public function stage(Request $request, Client $client): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $client);
        $data = $request->validate(['stage' => ['required', Rule::in(array_keys(Options::STAGES))]]);
        $client->update(['stage' => $data['stage'], 'last_contacted_at' => now()]);
        Audit::log('client.stage', $client, $data['stage']);

        return $request->expectsJson() ? response()->json(['ok' => true]) : back()->with('success', __('Stage updated.'));
    }

    /** "Contacted today" button and WhatsApp clicks */
    public function touch(Request $request, Client $client): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $client);
        $client->update(['last_contacted_at' => now()]);

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }

    /** Reveal the full NIC (logged) */
    public function revealNic(Client $client): JsonResponse
    {
        $this->authorize('view', $client);
        Audit::log('client.nic_viewed', $client);

        return response()->json(['nic' => $client->nic]);
    }
}
