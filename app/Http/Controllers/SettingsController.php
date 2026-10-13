<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Support\Audit;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function profile(Request $request): View
    {
        return view('settings.profile', ['user' => $request->user()]);
    }

    public function security(Request $request): View
    {
        $sessions = collect();
        if (config('session.driver') === 'database') {
            $sessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->id)->orderByDesc('last_activity')->get()
                ->map(fn ($s) => (object) [
                    'ip' => $s->ip_address,
                    'agent' => $this->device((string) $s->user_agent),
                    'current' => $s->id === $request->session()->getId(),
                    'last_active' => \Illuminate\Support\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                ]);
        }

        return view('settings.security', [
            'user' => $request->user(),
            'sessions' => $sessions,
            'activity' => ActivityLog::where('user_id', $request->user()->id)->latest('created_at')->limit(25)->get(),
        ]);
    }

    public function messages(Request $request): View
    {
        return view('settings.messages', ['user' => $request->user(), 'defaults' => WhatsApp::DEFAULT_TEMPLATES]);
    }

    public function updateMessages(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (array_keys(WhatsApp::DEFAULT_TEMPLATES) as $key) {
            $rules["templates.$key.si"] = ['nullable', 'string', 'max:600'];
            $rules["templates.$key.en"] = ['nullable', 'string', 'max:600'];
        }
        $data = $request->validate($rules);
        $user = $request->user();
        $settings = $user->settings ?? [];
        $settings['templates'] = collect($data['templates'] ?? [])->map(fn ($t) => array_filter($t))->filter()->all();
        $user->forceFill(['settings' => $settings])->save();

        return back()->with('success', __('Message templates saved.'));
    }

    public function logoutOtherSessions(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        Auth::logoutOtherDevices($request->input('password'));
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())->delete();
        }
        Audit::log('sessions.logged_out_others');

        return back()->with('success', __('Signed out of all other devices.'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $user = $request->user();
        Storage::disk('local')->deleteDirectory('documents/'.$user->id);
        Audit::log('account.deleted', $user, $user->email);
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', __('Your account and all its data were deleted.'));
    }

    private function device(string $agent): string
    {
        $os = match (true) {
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Linux') => 'Linux',
            default => __('Unknown device'),
        };
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Chrome/') => 'Chrome',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Safari/') => 'Safari',
            default => '',
        };

        return trim("$browser · $os", ' ·');
    }
}
