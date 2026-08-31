<?php

namespace App\Actions;

use App\Models\Membership;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GrantMembershipAction
{
    /** @param array<string, mixed> $data */
    public function handle(User $member, array $data, User $administrator): Membership
    {
        if ($member->isAdmin()) {
            throw ValidationException::withMessages(['member' => 'Paket hanya dapat diberikan kepada akun member.']);
        }

        return DB::transaction(function () use ($member, $data, $administrator): Membership {
            $membership = Membership::create($data + [
                'user_id' => $member->id,
                'created_by' => $administrator->id,
            ]);
            MembershipTransaction::create([
                'membership_id' => $membership->id,
                'type' => 'credit',
                'quantity' => $membership->initial_credits,
                'notes' => 'Kuota paket diberikan',
                'created_by' => $administrator->id,
            ]);

            return $membership;
        }, attempts: 3);
    }
}
