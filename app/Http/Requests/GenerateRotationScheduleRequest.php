<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GenerateRotationScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sets_per_match' => ['required', 'integer', 'between:1,2'],
            'session_duration_minutes' => ['required', 'integer', 'between:1,1440'],
            'minutes_per_set' => ['required', 'integer', 'between:1,120'],
            'expected_version' => ['required', 'integer', 'min:0'],
            'roster_fingerprint' => ['required', 'string', 'size:64'],
        ];
    }
}
