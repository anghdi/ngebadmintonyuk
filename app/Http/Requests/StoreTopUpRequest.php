<?php

namespace App\Http\Requests;

use App\Models\Membership;
use App\Models\TopUpSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTopUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $topUpSetting = TopUpSetting::current();
        $hasActiveMembership = $this->user()?->memberships()->where('status', 'active')->exists() ?? false;

        return [
            'amount' => ['required', 'integer', Rule::in([$topUpSetting->amount])],
            'membership_id' => [
                Rule::requiredIf($hasActiveMembership),
                'nullable',
                'integer',
                Rule::exists(Membership::class, 'id')->where(fn ($query) => $query
                    ->where('user_id', $this->user()->id)
                    ->where('status', 'active')),
            ],
            'bank' => ['required', Rule::in(array_keys(config('community.top_up.accounts')))],
            'proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
                'max:4096',
            ],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pendingRequests = $this->user()?->topUpRequests()->where('status', 'pending');
                $hasPendingRequest = $this->filled('membership_id')
                    ? $pendingRequests?->where('membership_id', $this->integer('membership_id'))->exists()
                    : $pendingRequests?->exists();

                if ($hasPendingRequest) {
                    $validator->errors()->add('membership_id', 'Paket ini masih memiliki pengajuan yang menunggu verifikasi.');
                }
            },
        ];
    }
}
