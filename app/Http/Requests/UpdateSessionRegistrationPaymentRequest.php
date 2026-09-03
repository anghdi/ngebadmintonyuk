<?php

namespace App\Http\Requests;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSessionRegistrationPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('registration');
        $playSession = $this->route('playSession');

        return ($this->user()?->isAdmin() ?? false)
            && $registration instanceof SessionRegistration
            && $playSession instanceof PlaySession
            && $registration->play_session_id === $playSession->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['transfer', 'cash'])],
            'is_paid' => ['required', 'boolean'],
        ];
    }
}
