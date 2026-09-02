<?php

namespace App\Http\Requests;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CancelSessionRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $playSession = $this->route('playSession');
        $registration = $this->route('registration');

        return $this->user() !== null
            && ! $this->user()->isAdmin()
            && $playSession instanceof PlaySession
            && $registration instanceof SessionRegistration
            && $registration->play_session_id === $playSession->id
            && $registration->user_id === $this->user()->id
            && $playSession->status === 'scheduled'
            && $playSession->scheduled_at->isFuture();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
