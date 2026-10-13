<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\Payment;
use App\Support\Audit;
use App\Support\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, InsurancePolicy $policy): RedirectResponse
    {
        $this->authorize('update', $policy);
        $data = $request->validate([
            'paid_on' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'method' => ['required', Rule::in(array_keys(Options::PAYMENT_METHODS))],
            'reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'advance' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $policy, $data) {
            $advance = $request->boolean('advance', true) && $policy->next_due_date;
            $payment = $request->user()->payments()->create([
                'insurance_policy_id' => $policy->id,
                'paid_on' => $data['paid_on'],
                'amount' => $data['amount'],
                'commission' => round($data['amount'] * (float) $policy->commission_rate / 100, 2),
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'covers_due_date' => $advance ? $policy->next_due_date : null,
            ]);
            if ($advance) {
                $policy->next_due_date = $policy->dueAfter($policy->next_due_date);
                if ($policy->status === 'lapsed') {
                    $policy->status = 'active';
                }
                $policy->save();
            }
            $policy->client?->update(['last_contacted_at' => now()]);
            Audit::log('payment.recorded', $payment, (string) $payment->amount);
        });

        return back()->with('success', __('Payment recorded.'))->with('thank_you_for', $policy->id);
    }

    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless((int) $payment->user_id === (int) $request->user()->id, 404);

        DB::transaction(function () use ($payment) {
            $policy = $payment->policy;
            // undo the due-date move if this was the latest payment that moved it
            if ($payment->covers_due_date && $policy && $policy->next_due_date
                && $policy->dueAfter($payment->covers_due_date)?->isSameDay($policy->next_due_date)) {
                $policy->update(['next_due_date' => $payment->covers_due_date]);
            }
            $payment->delete();
            Audit::log('payment.deleted', $payment, (string) $payment->amount);
        });

        return back()->with('success', __('Payment removed.'));
    }
}
