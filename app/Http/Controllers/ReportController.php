<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Services\Insights;
use App\Support\Audit;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', $this->data($request));
    }

    public function print(Request $request): View
    {
        return view('reports.print', $this->data($request));
    }

    private function data(Request $request): array
    {
        $month = $request->validate(['month' => ['nullable', 'date_format:Y-m']])['month'] ?? now()->format('Y-m');
        $from = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $to = $from->copy()->endOfMonth();
        $user = $request->user();
        $in = Insights::for($user);
        $payments = $user->payments()->with('policy.client')
            ->whereDate('paid_on', '>=', $from)->whereDate('paid_on', '<=', $to)->orderBy('paid_on')->get();
        $new = $in->newBusiness($from, $to);

        return [
            'month' => $month,
            'from' => $from,
            'collections' => $in->collections($from, $to),
            'newBusiness' => $new,
            'payments' => $payments,
            'byInsurer' => $payments->groupBy(fn ($p) => $p->policy?->insurer ?? '—')->map(fn ($g) => ['amount' => $g->sum('amount'), 'commission' => $g->sum('commission'), 'count' => $g->count()])->sortByDesc('amount'),
            'byType' => $new['policies']->groupBy('type')->map(fn ($g) => ['count' => $g->count(), 'annual' => $g->sum(fn (InsurancePolicy $p) => $p->annualPremium())]),
            'target' => (float) $user->monthly_target,
            'user' => $user,
        ];
    }

    public function export(Request $request, string $what): StreamedResponse
    {
        abort_unless(in_array($what, ['clients', 'policies', 'payments'], true), 404);
        $user = $request->user();
        Audit::log('export.'.$what);
        $filename = "rakawarana-{$what}-".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($user, $what) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Sinhala correctly
            $row = fn (array $r) => fputcsv($out, array_map(fn ($v) => $this->safeCell($v), $r));

            if ($what === 'clients') {
                $row(['Name', 'NIC', 'Date of birth', 'Gender', 'Phone', 'WhatsApp', 'Email', 'City', 'Address', 'Occupation', 'Stage', 'Source', 'Tags', 'Notes']);
                $user->clients()->orderBy('full_name')->chunk(500, function ($chunk) use ($row) {
                    foreach ($chunk as $c) {
                        $row([$c->full_name, $c->nic, $c->date_of_birth?->toDateString(), $c->gender, $c->phone, $c->whatsapp, $c->email, $c->city, $c->address, $c->occupation, $c->stage, $c->source, implode(', ', $c->tags ?? []), $c->notes]);
                    }
                });
            } elseif ($what === 'policies') {
                $row(['Client', 'Insurer', 'Type', 'Policy number', 'Plan', 'Sum assured', 'Premium', 'Frequency', 'Start', 'Next due', 'End', 'Status', 'Commission %', 'Vehicle']);
                $user->policies()->with('client')->orderBy('start_date')->chunk(500, function ($chunk) use ($row) {
                    foreach ($chunk as $p) {
                        $row([$p->client?->full_name, $p->insurer, $p->type, $p->policy_number, $p->plan_name, $p->sum_assured, $p->premium, $p->frequency, $p->start_date?->toDateString(), $p->next_due_date?->toDateString(), $p->end_date?->toDateString(), $p->status, $p->commission_rate, $p->vehicle_number]);
                    }
                });
            } else {
                $row(['Paid on', 'Client', 'Insurer', 'Policy number', 'Amount', 'Commission', 'Method', 'Reference']);
                $user->payments()->with('policy.client')->orderBy('paid_on')->chunk(500, function ($chunk) use ($row) {
                    foreach ($chunk as $p) {
                        $row([$p->paid_on->toDateString(), $p->policy?->client?->full_name, $p->policy?->insurer, $p->policy?->policy_number, $p->amount, $p->commission, $p->method, $p->reference]);
                    }
                });
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Stops spreadsheet formula injection (=, +, -, @ at the start of a cell) */
    private function safeCell(mixed $v): string
    {
        $s = (string) ($v ?? '');

        return preg_match('/^[=+\-@\t\r]/', $s) && ! is_numeric($s) ? "'".$s : $s;
    }
}
