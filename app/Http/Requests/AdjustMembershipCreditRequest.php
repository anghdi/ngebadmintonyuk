<?php

namespace App\Http\Requests;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AdjustMembershipCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');
        $membership = $this->route('membership');

        return ($this->user()?->isAdmin() ?? false)
            && $member instanceof User
            && $membership instanceof Membership
            && $membership->user_id === $member->id;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'quantity' => 'jumlah kuota',
            'notes' => 'alasan',
        ];
    }
}
