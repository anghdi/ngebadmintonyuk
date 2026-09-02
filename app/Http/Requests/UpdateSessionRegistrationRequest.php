<?php

namespace App\Http\Requests;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSessionRegistrationRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists(User::class, 'id')->where(fn ($query) => $query->where('role', 'member')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            'payment_method' => ['required', Rule::in(['transfer', 'cash'])],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid'])],
            'attendance_status' => ['required', Rule::in(['listed', 'present', 'no_show'])],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = SessionRegistration::normalizePhone((string) $this->input('phone'));

        $this->merge(['phone' => $phone !== '' ? $phone : null]);
    }
}
