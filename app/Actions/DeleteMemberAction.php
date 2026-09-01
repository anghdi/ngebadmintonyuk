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

            $paths = $lockedMember->topUpRequests()->pluck('proof_path');
            $lockedMember->delete();

            return $paths;
        }, attempts: 3);

        Storage::disk('local')->delete($proofPaths->all());
    }
}
