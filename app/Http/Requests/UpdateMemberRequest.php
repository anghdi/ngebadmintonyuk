<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');

        return ($this->user()?->isAdmin() ?? false) && $member instanceof User && ! $member->isAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->route('member'))],
            'phone' => ['nullable', 'string', 'max:30'],
        ];
    }
}
