<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /** @param  array<string, string>  $input */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, ['password' => $this->passwordRules()])->validate();
        $user->forceFill(['password' => Hash::make($input['password'])])->save();
        Audit::log('password.reset', $user, null, $user->id);
    }
}
