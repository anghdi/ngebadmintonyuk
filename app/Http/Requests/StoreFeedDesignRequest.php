<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedDesignRequest extends FormRequest
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
        return self::designRules();
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public static function designRules(): array
    {
        return [
            'format' => ['required', Rule::in(['single', 'connected_2', 'connected_3'])],
            'content_type' => ['required', Rule::in(['mabar', 'announcement', 'community', 'editorial', 'hero'])],
            'headline' => ['required', 'string', 'max:90'],
            'supporting_text' => ['nullable', 'string', 'max:180'],
            'event_date' => ['nullable', 'date'],
            'event_time' => ['nullable', 'date_format:H:i'],
            'venue' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'string', 'max:30'],
            'cta' => ['nullable', 'string', 'max:40'],
            'layout_variant' => ['required', Rule::in(['editorial', 'kinetic', 'sideline'])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:10240'],
            'zoom' => ['nullable', 'numeric', 'between:1,3'],
            'position_x' => ['nullable', 'numeric', 'between:-1,1'],
            'position_y' => ['nullable', 'numeric', 'between:-1,1'],
        ];
    }
}
