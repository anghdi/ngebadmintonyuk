<?php

namespace App\Actions;

use App\Models\Membership;
use App\Models\MembershipTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustMembershipCreditAction
{
    public function handle(Membership $membership, int $quantity, ?string $notes, User $administrator): MembershipTransaction
    {
        return DB::transaction(function () use ($membership, $quantity, $notes, $administrator): MembershipTransaction {
            $lockedMembership = Membership::query()->lockForUpdate()->findOrFail($membership->id);
            $balance = (int) $lockedMembership->transactions()->sum('quantity');

            if ($quantity > $balance) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah pengurangan melebihi sisa kuota member.',
                ]);
            }

            return $lockedMembership->transactions()->create([
                'type' => 'adjustment',
                'quantity' => -$quantity,
                'notes' => $notes ?: 'Pengurangan kuota oleh admin',
                'created_by' => $administrator->id,
            ]);
        }, attempts: 3);
    }
}
