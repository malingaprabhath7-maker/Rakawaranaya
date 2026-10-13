<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The shared demo account cannot change login details, 2FA or delete itself */
class BlockDemoUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_demo) {
            if ($request->expectsJson()) {
                abort(403, __('This is disabled in the demo.'));
            }

            return back()->with('error', __('This is disabled in the demo. Create a free account to use it.'));
        }

        return $next($request);
    }
}
