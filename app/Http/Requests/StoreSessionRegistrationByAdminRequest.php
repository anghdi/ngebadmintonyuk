<?php

namespace App\Http\Requests;

use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionRegistrationByAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists(User::class, 'id')->where(fn ($query) => $query->where('role', 'member')),
            ],
            'name' => ['nullable', 'required_without:user_id', 'string', 'max:255'],
            'phone' => ['nullable', 'required_without:user_id', 'regex:/^[0-9]{10,15}$/'],
            'payment_method' => ['required', Rule::in(['transfer', 'cash'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = SessionRegistration::normalizePhone((string) $this->input('phone'));

        $this->merge(['phone' => $phone !== '' ? $phone : null]);
    }
}
