<?php

namespace App\Actions;

use App\Models\Guest;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateGuestAttendanceAction
{
    /** @param array{attendance_status: string, admin_notes?: string|null} $data */
    public function handle(SessionRegistration $registration, array $data, User $administrator): SessionRegistration
    {
        return DB::transaction(function () use ($registration, $data, $administrator): SessionRegistration {
            $session = $registration->playSession()->lockForUpdate()->firstOrFail();
            Guest::query()->lockForUpdate()->findOrFail($registration->guest_id);
            $locked = SessionRegistration::query()->lockForUpdate()->findOrFail($registration->id);
            if ($locked->guest_id === null || $locked->user_id !== null || $locked->payment_method === 'membership') {
                throw ValidationException::withMessages(['attendance_status' => 'Data bukan pendaftaran tamu.']);
            }
            $position = $session->registrations()->where('id', '<=', $locked->id)->count();
            if ($data['attendance_status'] !== 'listed' && ($position > $session->max_players || $session->status === 'cancelled')) {
                throw ValidationException::withMessages(['attendance_status' => 'Absensi hanya untuk slot utama pada sesi yang tidak dibatalkan.']);
            }
            $locked->update([
                'attendance_status' => $data['attendance_status'], 'admin_notes' => $data['admin_notes'] ?? null,
                'checked_by' => $administrator->id, 'checked_at' => now(),
            ]);

            return $locked;
        }, attempts: 3);
    }
}
