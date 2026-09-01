<?php

namespace App\Actions;

use App\Models\SessionRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteSessionRegistrationAction
{
    public function handle(SessionRegistration $registration): void
    {
        DB::transaction(function () use ($registration): void {
            $lockedRegistration = SessionRegistration::query()->lockForUpdate()->findOrFail($registration->id);

            if ($lockedRegistration->attendance_status !== 'listed' || $lockedRegistration->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'registration' => 'Daftar yang sudah dibayar atau memiliki status kehadiran tidak dapat dihapus.',
                ]);
            }

            $lockedRegistration->delete();
        }, attempts: 3);
    }
}
