<?php

namespace App\Http\Requests;

use App\Support\Options;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership is checked by ClientPolicy in the controller
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags');
        if (is_string($tags)) {
            $this->merge(['tags' => array_values(array_filter(array_map(fn ($t) => mb_substr(trim($t), 0, 30), explode(',', $tags))))]);
        }
        if ($this->filled('nic')) {
            $this->merge(['nic' => strtoupper(preg_replace('/\s+/', '', (string) $this->input('nic')))]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:160'],
            // old NIC: 9 digits + V/X; new NIC: 12 digits
            'nic' => ['nullable', 'string', 'regex:/^(\d{9}[VX]|\d{12})$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::in(array_keys(Options::GENDERS))],
            'marital_status' => ['nullable', Rule::in(array_keys(Options::MARITAL))],
            'dependents' => ['nullable', 'integer', 'min:0', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-()]{7,20}$/'],
            'whatsapp' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:80'],
            'occupation' => ['nullable', 'string', 'max:120'],
            'monthly_income' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'stage' => ['required', Rule::in(array_keys(Options::STAGES))],
            'source' => ['nullable', Rule::in(array_keys(Options::SOURCES))],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return ['nic.regex' => __('Enter a valid NIC: 9 digits with V/X, or 12 digits.')];
    }
}
