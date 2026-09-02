<?php

namespace App\Http\Requests;

use App\Models\PlaySession;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendPushNotificationRequest extends FormRequest
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
            'type' => ['required', Rule::in(['schedule', 'slots', 'important'])],
            'audience' => ['required', Rule::in(['all', 'session'])],
            'play_session_id' => [
                Rule::requiredIf(fn (): bool => $this->input('audience') === 'session'
                    || in_array($this->input('type'), ['schedule', 'slots'], true)),
                'nullable',
                'integer',
                Rule::exists(PlaySession::class, 'id'),
            ],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'type' => 'jenis notifikasi',
            'audience' => 'penerima',
            'play_session_id' => 'jadwal',
            'title' => 'judul',
            'body' => 'isi notifikasi',
        ];
    }
}
