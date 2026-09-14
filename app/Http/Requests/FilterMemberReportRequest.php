<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterMemberReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['required', Rule::in(['members', 'guests'])],
            'profile' => ['nullable', Rule::in(['complete', 'incomplete'])],
            'birthday_month' => ['nullable', 'integer', 'between:1,12'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_date' => $this->input('start_date') ?: today()->startOfMonth()->toDateString(),
            'end_date' => $this->input('end_date') ?: today()->toDateString(),
            'type' => $this->input('type') ?: 'members',
        ]);
    }
}
