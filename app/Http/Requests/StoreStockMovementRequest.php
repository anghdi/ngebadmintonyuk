<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'shuttlecock_item_id' => ['required', 'exists:shuttlecock_items,id'],
            'play_session_id' => ['nullable', 'exists:play_sessions,id'],
            'type' => ['required', Rule::in(['purchase', 'usage', 'adjustment'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
