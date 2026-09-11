<?php

namespace App\Actions;

use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkSessionRegistrationPresentAction
{
    public function __construct(
        private RecordSessionRegistrationPaymentAction $recordPayment,
        private UpdateSessionRegistrationAction $updateRegistration,
    ) {}

    public function handle(SessionRegistration $registration, User $administrator): SessionRegistration
    {
        return DB::transaction(function () use ($registration, $administrator): SessionRegistration {
            $lockedRegistration = SessionRegistration::query()
                ->lockForUpdate()
                ->findOrFail($registration->id);

            if ($lockedRegistration->user_id === null) {
                throw ValidationException::withMessages([
                    'attendance' => 'Data lama tanpa akun harus diperbarui terlebih dahulu.',
                ]);
            }

            if ($lockedRegistration->attendance_status === 'present') {
                return $lockedRegistration->refresh();
            }

            $this->recordPayment->handle(
                $lockedRegistration,
                $lockedRegistration->payment_method,
                $lockedRegistration->payment_method !== 'membership',
                $administrator,
            );

            return $this->updateRegistration->handle($lockedRegistration, [
                'user_id' => $lockedRegistration->user_id,
                'name' => $lockedRegistration->name,
                'phone' => $lockedRegistration->phone,
                'attendance_status' => 'present',
                'admin_notes' => $lockedRegistration->admin_notes,
            ], $administrator);
        }, attempts: 3);
    }
}
