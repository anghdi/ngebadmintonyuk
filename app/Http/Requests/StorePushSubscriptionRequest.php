<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePushSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'driver' => ['required', Rule::in(['fcm', 'webpush'])],
            'installation_id' => ['required_if:driver,fcm', 'nullable', 'string', 'max:191'],
            'endpoint' => ['required_if:driver,webpush', 'nullable', 'url', 'max:2048'],
            'public_key' => ['required_if:driver,webpush', 'nullable', 'string', 'max:1000'],
            'auth_token' => ['required_if:driver,webpush', 'nullable', 'string', 'max:1000'],
            'content_encoding' => ['nullable', Rule::in(['aes128gcm', 'aesgcm'])],
            'user_agent' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'installation_id' => 'identitas perangkat',
            'endpoint' => 'endpoint notifikasi',
            'public_key' => 'kunci publik perangkat',
            'auth_token' => 'token autentikasi perangkat',
            'user_agent' => 'informasi browser',
        ];
    }
}
