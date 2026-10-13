<?php

namespace App\Http\Requests;

use App\Support\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // the client must belong to the signed-in advisor
            'client_id' => ['required', 'string', Rule::exists('clients', 'id')->where('user_id', $this->user()->id)->whereNull('deleted_at')],
            'insurer' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(array_keys(Options::POLICY_TYPES))],
            'policy_number' => ['nullable', 'string', 'max:60'],
            'plan_name' => ['nullable', 'string', 'max:120'],
            'sum_assured' => ['nullable', 'numeric', 'min:0', 'max:10000000000'],
            'premium' => ['required', 'numeric', 'min:1', 'max:1000000000'],
            'frequency' => ['required', Rule::in(array_keys(Options::FREQUENCIES))],
            'start_date' => ['required', 'date'],
            'next_due_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(array_keys(Options::POLICY_STATUSES))],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vehicle_number' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
