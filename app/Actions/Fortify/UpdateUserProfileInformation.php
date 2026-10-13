<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /** @param  array<string, mixed>  $input */
    public function update(User $user, array $input): void
    {
        $data = Validator::make($input, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:120'],
            'agent_code' => ['nullable', 'string', 'max:40'],
            'monthly_target' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'locale' => ['required', Rule::in(['si', 'en'])],
        ])->validateWithBag('updateProfileInformation');

        $data['email'] = mb_strtolower($data['email']);
        $data['monthly_target'] = $data['monthly_target'] ?? 0;
        if ($user->is_demo) {
            unset($data['email']);   // the shared demo account keeps its login
        }
        $settings = $user->settings ?? [];
        $settings['digest'] = ! empty($input['digest']);
        $data['settings'] = $settings;
        $user->forceFill($data)->save();
        session(['locale' => $user->locale]);
        Audit::log('profile.updated', $user);
    }
}
