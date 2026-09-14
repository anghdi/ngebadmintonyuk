<?php

namespace App\Http\Requests;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuestAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $registration = $this->route('registration');
        $session = $this->route('playSession');

        return ($this->user()?->isAdmin() ?? false)
            && $registration instanceof SessionRegistration
            && $session instanceof PlaySession
            && $registration->play_session_id === $session->id
            && $registration->guest_id !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'attendance_status' => ['required', Rule::in(['listed', 'present', 'no_show'])],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
