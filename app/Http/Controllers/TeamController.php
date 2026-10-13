<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Services\Insights;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A branch manager creates a team and shares the code; advisors join. The manager sees each
 * advisor's numbers (not their clients — those stay private to the advisor).
 */
class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load('team.owner');
        $members = collect();
        if ($user->isManager()) {
            $from = now()->startOfMonth();
            $to = now()->endOfMonth();
            $members = $user->team->members()->orderBy('name')->get()->map(function (User $m) use ($from, $to) {
                $in = Insights::for($m);

                return [
                    'user' => $m,
                    'collections' => $in->collections($from, $to),
                    'new' => $in->newBusiness($from, $to),
                    'active' => $m->policies()->active()->count(),
                    'overdue' => $in->duePremiums(0)->count(),
                ];
            });
        }

        return view('team.index', ['user' => $user, 'members' => $members]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->team_id, 422);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $team = Team::create(['name' => $data['name'], 'owner_id' => $user->id, 'invite_code' => Team::newInviteCode()]);
        $user->forceFill(['team_id' => $team->id, 'role' => 'manager'])->save();
        Audit::log('team.created', $team);

        return back()->with('success', __('Team created. Share the code with your advisors.'));
    }

    public function join(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate(['code' => ['required', 'string', 'max:16']]);
        $team = Team::where('invite_code', strtoupper(trim($data['code'])))->first();
        if (! $team) {
            return back()->withErrors(['code' => __('That team code is not valid.')]);
        }
        if ($user->isManager()) {
            return back()->with('error', __('Managers cannot join another team.'));
        }
        $user->forceFill(['team_id' => $team->id])->save();
        Audit::log('team.joined', $team);

        return back()->with('success', __('You joined :team.', ['team' => $team->name]));
    }

    public function leave(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->isManager(), 422);
        $user->forceFill(['team_id' => null])->save();
        Audit::log('team.left');

        return back()->with('success', __('You left the team.'));
    }

    public function remove(Request $request, User $member): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isManager() && (int) $member->team_id === (int) $user->team_id && $member->id !== $user->id, 404);
        $member->forceFill(['team_id' => null])->save();
        Audit::log('team.member_removed', $member);

        return back()->with('success', __('Member removed.'));
    }

    public function regenerate(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isManager(), 404);
        $user->team->update(['invite_code' => Team::newInviteCode()]);

        return back()->with('success', __('New team code created. The old one no longer works.'));
    }
}
