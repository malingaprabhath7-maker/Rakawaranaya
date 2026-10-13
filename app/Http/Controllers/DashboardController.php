<?php

namespace App\Http\Controllers;

use App\Services\Insights;
use App\Support\Options;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $in = Insights::for($user);

        return view('dashboard', [
            'summary' => $in->summary(),
            'due' => $in->duePremiums(14)->take(6),
            'renewals' => $in->renewals(45)->take(5),
            'birthdays' => $in->birthdays(7)->take(5),
            'tasks' => $in->openTasks(3)->take(6),
            'monthly' => $in->monthly(6),
            'byType' => collect($in->byType())->mapWithKeys(fn ($c, $t) => [__(Options::POLICY_TYPES[$t] ?? $t) => $c]),
            'pipeline' => $in->pipeline(),
        ]);
    }
}
