<?php

namespace App\Http\Controllers;

use App\Http\Requests\PolicyRequest;
use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Support\Audit;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PolicyController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(array_keys(Options::POLICY_TYPES))],
            'status' => ['nullable', Rule::in(array_keys(Options::POLICY_STATUSES))],
            'insurer' => ['nullable', 'string', 'max:80'],
        ]);
        $user = $request->user();
        $like = isset($f['q']) ? '%'.str_replace(['%', '_'], ['\%', '\_'], $f['q']).'%' : null;

        $policies = $user->policies()->with('client')
            ->when($like, fn ($q) => $q->where(fn ($w) => $w->where('policy_number', 'like', $like)->orWhere('plan_name', 'like', $like)
                ->orWhere('vehicle_number', 'like', $like)
                ->orWhereHas('client', fn ($c) => $c->where('full_name', 'like', $like))))
            ->when($f['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['insurer'] ?? null, fn ($q, $v) => $q->where('insurer', $v))
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'proposal' THEN 1 ELSE 2 END")
            ->orderBy('next_due_date')
            ->paginate(25)->withQueryString();

        return view('policies.index', [
            'policies' => $policies,
            'filters' => $f,
            'insurers' => $user->policies()->distinct()->orderBy('insurer')->pluck('insurer'),
        ]);
    }

    public function create(Request $request): View
    {
        $client = null;
        if ($id = $request->query('client')) {
            $client = $request->user()->clients()->findOrFail($id);
        }

        return view('policies.create', [
            'policy' => new InsurancePolicy([
                'client_id' => $client?->id,
                'status' => 'active',
                'frequency' => 'monthly',
                'type' => 'life',
                'start_date' => today(),
                'insurer' => $request->user()->company,
            ]),
            'clients' => $request->user()->clients()->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function store(PolicyRequest $request): RedirectResponse
    {
        $data = $this->withDueDate($request->validated());
        $policy = $request->user()->policies()->create($data);

        // selling a policy turns a lead into a client
        $client = $request->user()->clients()->find($data['client_id']);
        if ($client && in_array($client->stage, ['lead', 'contacted', 'proposal']) && $policy->status === 'active') {
            $client->update(['stage' => 'client']);
        }
        Audit::log('policy.created', $policy);

        return redirect()->route('policies.show', $policy)->with('success', __('Policy saved.'));
    }

    public function show(InsurancePolicy $policy): View
    {
        $this->authorize('view', $policy);
        $policy->load(['client', 'payments', 'documents']);

        return view('policies.show', compact('policy'));
    }

    public function edit(Request $request, InsurancePolicy $policy): View
    {
        $this->authorize('update', $policy);

        return view('policies.edit', [
            'policy' => $policy,
            'clients' => $request->user()->clients()->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function update(PolicyRequest $request, InsurancePolicy $policy): RedirectResponse
    {
        $this->authorize('update', $policy);
        $policy->update($this->withDueDate($request->validated()));
        Audit::log('policy.updated', $policy);

        return redirect()->route('policies.show', $policy)->with('success', __('Policy updated.'));
    }

    public function destroy(InsurancePolicy $policy): RedirectResponse
    {
        $this->authorize('delete', $policy);
        $client = $policy->client;
        $policy->delete();
        Audit::log('policy.deleted', $policy, $policy->policy_number);

        return redirect()->route('clients.show', $client)->with('success', __('Policy deleted.'));
    }

    /** First due date = start date when not given; single-premium policies have none */
    private function withDueDate(array $data): array
    {
        if ($data['frequency'] === 'single') {
            $data['next_due_date'] = null;
        } elseif (empty($data['next_due_date'])) {
            $data['next_due_date'] = Carbon::parse($data['start_date'])->toDateString();
        }
        $data['commission_rate'] = $data['commission_rate'] ?? 0;

        return $data;
    }
}
