<?php

namespace App\Http\Requests;

use App\Models\Guest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuestSessionRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'guest_id' => ['required', 'integer', Rule::exists(Guest::class, 'id')],
            'payment_method' => ['required', Rule::in(['cash', 'transfer'])],
        ];
    }
}
