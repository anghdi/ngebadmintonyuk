<?php

namespace App\Http\Requests;

use App\Models\ShuttlecockItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShuttlecockItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $uniqueItem = Rule::unique(ShuttlecockItem::class, 'name')
            ->where(fn ($query) => $query->where('brand', $this->input('brand')))
            ->ignore($this->route('shuttlecockItem'));

        return [
            'name' => ['required', 'string', 'max:255', $uniqueItem],
            'brand' => ['nullable', 'string', 'max:255'],
            'pieces_per_tube' => ['required', 'integer', 'min:1', 'max:100'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
