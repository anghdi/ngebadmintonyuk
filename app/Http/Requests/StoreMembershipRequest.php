<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'venue_name' => ['required', 'string', 'max:255'],
            'court_name' => ['required', 'string', 'max:255'],
            'price_per_session' => ['required', 'integer', 'min:0'],
            'initial_credits' => ['required', 'integer', 'min:1', 'max:100'],
            'starts_on' => ['required', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
