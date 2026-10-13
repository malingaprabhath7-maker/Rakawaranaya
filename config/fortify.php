<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'middleware' => ['web'],
    'auth_middleware' => 'auth',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'views' => true,

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    'redirects' => [
        'login' => '/dashboard',
        'logout' => '/',
        'register' => '/dashboard',
    ],

    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],
];
