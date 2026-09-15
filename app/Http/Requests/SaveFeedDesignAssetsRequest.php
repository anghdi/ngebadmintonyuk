<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SaveFeedDesignAssetsRequest extends FormRequest
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
            'assets' => ['required', 'array', 'between:1,3'],
            'assets.*' => ['required', 'file', 'mimes:png', 'mimetypes:image/png', 'max:8192'],
            'thumbnail' => ['required', 'file', 'mimes:png', 'mimetypes:image/png', 'max:2048'],
            'layout_variant' => ['required', 'string', 'in:editorial,kinetic,sideline'],
            'zoom' => ['required', 'numeric', 'between:1,3'],
            'position_x' => ['required', 'numeric', 'between:-1,1'],
            'position_y' => ['required', 'numeric', 'between:-1,1'],
        ];
    }
}
