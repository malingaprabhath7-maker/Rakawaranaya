<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Applied to every web request: the shared demo account can't change its password or 2FA (Fortify routes) */
class GuardDemoAccount
{
    private const BLOCKED = ['user-password.update', 'two-factor.enable', 'two-factor.confirm', 'two-factor.disable', 'two-factor.regenerate-recovery-codes'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_demo && in_array($request->route()?->getName(), self::BLOCKED, true)) {
            return back()->with('error', __('This is disabled in the demo. Create a free account to use it.'));
        }

        return $next($request);
    }
}
