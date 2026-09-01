<?php

namespace App\Http\Requests;

use App\Models\SessionRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'payment_method' => ['required', Rule::in(['transfer', 'cash'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => SessionRegistration::normalizePhone((string) $this->input('phone'))]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['phone' => 'nomor WhatsApp', 'payment_method' => 'metode pembayaran'];
    }
}
