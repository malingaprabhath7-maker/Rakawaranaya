<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /** @param  array<string, string>  $input */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'company' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => $this->passwordRules(),
            'terms' => ['accepted'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => mb_strtolower($input['email']),
            'company' => $input['company'] ?? null,
            'phone' => $input['phone'] ?? null,
            'locale' => app()->getLocale(),
            'password' => Hash::make($input['password']),
        ]);
        Audit::log('account.created', $user, null, $user->id);

        return $user;
    }
}
