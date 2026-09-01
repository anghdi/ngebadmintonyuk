<?php

namespace App\Actions;

use App\Models\Membership;
use Illuminate\Validation\ValidationException;

class DeleteMembershipAction
{
    public function handle(Membership $membership): void
    {
        if ($membership->attendances()->exists() || $membership->topUpRequests()->exists()) {
            throw ValidationException::withMessages([
                'membership' => 'Paket dengan riwayat kehadiran atau top up tidak dapat dihapus. Nonaktifkan paket sebagai gantinya.',
            ]);
        }

        $membership->delete();
    }
}
