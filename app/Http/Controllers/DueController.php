<?php

namespace App\Http\Controllers;

use App\Services\Insights;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The advisor's daily work list: overdue and upcoming premiums, renewals, birthdays and tasks */
class DueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $in = Insights::for($request->user());
        $due = $in->duePremiums(30);
        $today = today();

        return view('due.index', [
            'overdue' => $due->filter(fn ($p) => $p->next_due_date->lt($today)),
            'upcoming' => $due->filter(fn ($p) => $p->next_due_date->gte($today)),
            'renewals' => $in->renewals(60),
            'birthdays' => $in->birthdays(14),
            'tasks' => $in->openTasks(7),
        ]);
    }
}
