<?php

namespace App\Http\Requests;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMembershipRequest extends FormRequest
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
            'venue_name' => ['required', 'string', 'max:255'],
            'court_name' => ['required', 'string', 'max:255'],
            'price_per_session' => ['required', 'integer', 'min:0'],
            'starts_on' => ['required', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $membership = $this->route('membership');

                if (! $membership instanceof Membership || ! $membership->attendances()->exists()) {
                    return;
                }

                $coreDataChanged = $membership->venue_name !== $this->input('venue_name')
                    || $membership->court_name !== $this->input('court_name')
                    || $membership->price_per_session !== $this->integer('price_per_session')
                    || $membership->starts_on->format('Y-m-d') !== $this->input('starts_on');

                if ($coreDataChanged) {
                    $validator->errors()->add('venue_name', 'Venue, lapangan, harga, dan tanggal mulai tidak dapat diubah setelah paket digunakan.');
                }
            },
        ];
    }
}
