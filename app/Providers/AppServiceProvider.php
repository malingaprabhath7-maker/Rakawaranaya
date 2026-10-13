<?php

namespace App\Providers;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8)->letters()->numbers();

            return $this->app->isProduction() ? $rule->mixedCase()->uncompromised() : $rule;
        });

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }

        Paginator::defaultView('pagination::tailwind');
        Paginator::defaultSimpleView('pagination::simple-tailwind');

        Event::listen(function (Login $event) {
            if ($event->user instanceof User) {
                $event->user->forceFill(['last_login_at' => now(), 'last_login_ip' => request()->ip()])->saveQuietly();
                if ($event->user->locale) {
                    session(['locale' => $event->user->locale]);
                }
                Audit::log('login', null, null, $event->user->id);
            }
        });
        Event::listen(function (Failed $event) {
            if ($event->user instanceof User) {
                Audit::log('login.failed', null, null, $event->user->id);
            }
        });
        Event::listen(function (Logout $event) {
            if ($event->user instanceof User) {
                Audit::log('logout', null, null, $event->user->id);
            }
        });
    }
}
