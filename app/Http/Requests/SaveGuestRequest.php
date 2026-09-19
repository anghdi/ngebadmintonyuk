<?php

namespace App\Http\Requests;

use App\Models\Guest;
use App\Models\SessionRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^[0-9]{10,15}$/', Rule::unique(Guest::class)->ignore($this->route('guest'))],
            'playing_level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = $this->input('phone');
        if (is_string($phone)) {
            $this->merge(['phone' => trim($phone) === '' ? null : SessionRegistration::normalizePhone($phone)]);
        }
    }
}
