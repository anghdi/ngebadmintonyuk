<?php

namespace App\Actions;

use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateSessionRegistrationAction
{
    /** @param array{payment_status: string, attendance_status: string, admin_notes?: string|null} $data */
    public function handle(SessionRegistration $registration, array $data, User $administrator): SessionRegistration
    {
        return DB::transaction(function () use ($registration, $data, $administrator): SessionRegistration {
            $lockedRegistration = SessionRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            $lockedRegistration->update($data + [
                'checked_by' => $administrator->id,
                'checked_at' => now(),
            ]);

            return $lockedRegistration;
        }, attempts: 3);
    }
}
