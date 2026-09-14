<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before_or_equal:today', 'after_or_equal:'.today()->subYears(120)->toDateString()],
            'nickname' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'playing_level' => ['nullable', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'avatar' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp', 'max:2048', 'dimensions:max_width=4096,max_height=4096'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'date_of_birth.required' => 'Tanggal lahir wajib diisi.',
            'date_of_birth.date_format' => 'Tanggal lahir tidak valid.',
            'date_of_birth.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'date_of_birth.after_or_equal' => 'Periksa kembali tanggal lahir.',
            'avatar.max' => 'Ukuran foto maksimal 2 MB.',
        ];
    }

    /** @return array{name: string, date_of_birth: string, nickname: string|null, phone: string|null, playing_level: string|null} */
    public function profileData(): array
    {
        return [
            'name' => $this->validated('name'),
            'date_of_birth' => $this->validated('date_of_birth'),
            'nickname' => $this->validated('nickname'),
            'phone' => $this->validated('phone'),
            'playing_level' => $this->validated('playing_level'),
        ];
    }
}
