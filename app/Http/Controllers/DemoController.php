<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    /** Signs visitors into a shared sample account so they can try the app without registering */
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(config('rakawarana.demo_enabled'), 404);

        $user = User::where('email', config('rakawarana.demo_email'))->first()
            ?? app(DemoSeeder::class)->createDemo();

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('locale', $request->session()->get('locale', 'si'));

        return redirect()->route('dashboard')->with('success', __('Welcome to the demo! The data here is made up and resets every night.'));
    }
}
