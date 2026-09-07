<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteMemberAction
{
    public function handle(User $member): void
    {
        $proofPaths = DB::transaction(function () use ($member) {
            $lockedMember = User::query()->lockForUpdate()->findOrFail($member->id);

            if ($lockedMember->isAdmin()) {
                throw ValidationException::withMessages(['member' => 'Akun administrator tidak dapat dihapus.']);
            }

            if ($lockedMember->topUpRequests()->exists() || $lockedMember->attendances()->exists()
                || $lockedMember->sessionRegistrations()->where(fn ($query) => $query->where('payment_status', 'paid')->orWhere('attendance_status', '!=', 'listed'))->exists()) {
                throw ValidationException::withMessages(['member' => 'Akun dengan riwayat top up, pembayaran atau absensi tidak dapat dihapus.']);
            }

            $paths = $lockedMember->topUpRequests()->pluck('proof_path');
            $lockedMember->delete();

            return $paths;
        }, attempts: 3);

        Storage::disk('local')->delete($proofPaths->all());
    }
}
