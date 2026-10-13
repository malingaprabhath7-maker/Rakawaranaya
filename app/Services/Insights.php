<?php

namespace App\Services;

use App\Models\Client;
use App\Models\InsurancePolicy;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * All the numbers for the dashboard, the daily work list, reports and the team view.
 * Queries are always scoped to one advisor.
 */
class Insights
{
    public function __construct(private User $user, private ?Carbon $today = null)
    {
        $this->today = ($today ?? now())->copy()->startOfDay();
    }

    public static function for(User $user, ?Carbon $today = null): self
    {
        return new self($user, $today);
    }

    /** Active policies with a premium due on or before $days from today (overdue included) */
    public function duePremiums(int $days = 14): Collection
    {
        return $this->user->policies()->active()
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $this->today->copy()->addDays($days))
            ->with('client')
            ->orderBy('next_due_date')
            ->get();
    }

    /** Policies whose term ends (motor/health renewals, maturities) within $days */
    public function renewals(int $days = 60): Collection
    {
        return $this->user->policies()
            ->whereIn('status', ['active', 'lapsed'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $this->today->copy()->subDays(7))
            ->whereDate('end_date', '<=', $this->today->copy()->addDays($days))
            ->with('client')
            ->orderBy('end_date')
            ->get();
    }

    /** Clients with a birthday in the next $days (0 = today), nearest first */
    public function birthdays(int $days = 7): Collection
    {
        return $this->user->clients()->whereNotNull('date_of_birth')->get()
            ->map(function (Client $c) {
                $c->setAttribute('days_to_birthday', $c->daysToBirthday($this->today));

                return $c;
            })
            ->filter(fn (Client $c) => $c->days_to_birthday <= $days)
            ->sortBy('days_to_birthday')
            ->values();
    }

    public function openTasks(int $days = 7): Collection
    {
        return $this->user->tasks()->whereNull('done_at')
            ->whereDate('due_on', '<=', $this->today->copy()->addDays($days))
            ->with('client')
            ->orderBy('due_on')->orderBy('due_time')
            ->get();
    }

    /** @return array{collected: float, commission: float, count: int} */
    public function collections(Carbon $from, Carbon $to): array
    {
        $q = $this->user->payments()->whereDate('paid_on', '>=', $from)->whereDate('paid_on', '<=', $to);

        return [
            'collected' => (float) (clone $q)->sum('amount'),
            'commission' => (float) (clone $q)->sum('commission'),
            'count' => (clone $q)->count(),
        ];
    }

    /** New business written (by start date) in a period, as annualised premium */
    public function newBusiness(Carbon $from, Carbon $to): array
    {
        $policies = $this->user->policies()
            ->whereIn('status', ['active', 'proposal'])
            ->whereDate('start_date', '>=', $from)->whereDate('start_date', '<=', $to)
            ->get();

        return [
            'count' => $policies->count(),
            'annual_premium' => round($policies->sum(fn (InsurancePolicy $p) => $p->annualPremium()), 2),
            'policies' => $policies,
        ];
    }

    /** Collections and commission for each of the last $n months, oldest first */
    public function monthly(int $n = 6): Collection
    {
        $start = $this->today->copy()->startOfMonth()->subMonths($n - 1);
        $payments = $this->user->payments()->whereDate('paid_on', '>=', $start)->get(['paid_on', 'amount', 'commission']);

        return collect(range(0, $n - 1))->map(function ($i) use ($start, $payments) {
            $m = $start->copy()->addMonths($i);
            $inMonth = $payments->filter(fn (Payment $p) => $p->paid_on->isSameMonth($m));

            return [
                'label' => $m->translatedFormat('M'),
                'month' => $m->format('Y-m'),
                'collected' => round((float) $inMonth->sum('amount'), 2),
                'commission' => round((float) $inMonth->sum('commission'), 2),
            ];
        });
    }

    /** @return array<string,int> active policy count per type */
    public function byType(): array
    {
        return $this->user->policies()->active()
            ->selectRaw('type, count(*) as c')->groupBy('type')
            ->pluck('c', 'type')->map(fn ($v) => (int) $v)->all();
    }

    /** @return array<string,int> client count per pipeline stage */
    public function pipeline(): array
    {
        return $this->user->clients()
            ->selectRaw('stage, count(*) as c')->groupBy('stage')
            ->pluck('c', 'stage')->map(fn ($v) => (int) $v)->all();
    }

    public function summary(): array
    {
        $monthStart = $this->today->copy()->startOfMonth();
        $monthEnd = $this->today->copy()->endOfMonth();
        $active = $this->user->policies()->active()->get(['id', 'premium', 'frequency', 'sum_assured', 'next_due_date', 'status']);
        $due = $this->duePremiums(7);

        return [
            'clients' => $this->user->clients()->where('stage', 'client')->count(),
            'leads' => $this->user->clients()->whereIn('stage', ['lead', 'contacted', 'proposal'])->count(),
            'active_policies' => $active->count(),
            'annual_book' => round($active->sum(fn (InsurancePolicy $p) => $p->annualPremium()), 2),
            'month' => $this->collections($monthStart, $monthEnd),
            'new_business' => $this->newBusiness($monthStart, $monthEnd),
            'target' => (float) $this->user->monthly_target,
            'overdue' => $due->filter(fn (InsurancePolicy $p) => $p->next_due_date->lt($this->today))->count(),
            'due_week' => $due->filter(fn (InsurancePolicy $p) => $p->next_due_date->gte($this->today))->count(),
        ];
    }
}
