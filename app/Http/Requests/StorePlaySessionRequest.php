<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlaySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date'],
            'venue_name' => ['required', 'string', 'max:255'],
            'court_name' => ['required', 'string', 'max:255'],
            'price_per_session' => ['required', 'integer', 'min:0'],
            'max_players' => ['required', 'integer', 'min:1', 'max:200'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
