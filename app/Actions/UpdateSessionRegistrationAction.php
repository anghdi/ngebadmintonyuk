<?php

namespace App\Actions;

use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateSessionRegistrationAction
{
    public function __construct(private RecordAttendanceAction $recordAttendance) {}

    /** @param array{user_id: int, name: string, phone?: string|null, attendance_status: string, admin_notes?: string|null} $data */
    public function handle(SessionRegistration $registration, array $data, User $administrator): SessionRegistration
    {
        try {
            return DB::transaction(function () use ($registration, $data, $administrator): SessionRegistration {
                $playSession = $registration->playSession()->lockForUpdate()->firstOrFail();
                User::query()->lockForUpdate()->findOrFail($data['user_id']);
                $lockedRegistration = SessionRegistration::query()->lockForUpdate()->findOrFail($registration->id);
                [$member, $name, $phone] = $this->resolveIdentity($data);

                if ($lockedRegistration->user_id !== $member->id
                    && ($lockedRegistration->payment_status === 'paid' || $lockedRegistration->attendance_status !== 'listed')) {
                    throw ValidationException::withMessages(['account' => 'Kembalikan status ke terdaftar dan batalkan pembayaran sebelum mengganti pemain.']);
                }

                $position = $playSession->registrations()->where('id', '<=', $lockedRegistration->id)->count();

                if ($data['attendance_status'] !== 'listed' && ($position > $playSession->max_players || $playSession->status === 'cancelled')) {
                    throw ValidationException::withMessages(['attendance_status' => 'Absensi hanya untuk pemain utama pada sesi yang tidak dibatalkan.']);
                }

                $alreadyListed = SessionRegistration::query()
                    ->where('play_session_id', $lockedRegistration->play_session_id)
                    ->where('user_id', $member->id)
                    ->whereKeyNot($lockedRegistration->id)
                    ->exists();

                if ($alreadyListed) {
                    throw ValidationException::withMessages(['account' => 'Akun ini sudah masuk dalam daftar sesi.']);
                }

                if ($data['attendance_status'] === 'listed') {
                    $attendance = $playSession->attendances()->where('user_id', $lockedRegistration->user_id)->first();
                    $attendance?->transaction()->delete();
                    $attendance?->delete();
                } else {
                    $this->recordAttendance->handle(
                        $playSession,
                        $member,
                        $data['attendance_status'] === 'present' ? 'present' : 'absent',
                        $data['admin_notes'] ?? null,
                        $administrator,
                        $lockedRegistration->payment_method === 'membership',
                    );
                }

                if ($lockedRegistration->payment_method === 'membership') {
                    $lockedRegistration->payment_status = $data['attendance_status'] === 'present' ? 'paid' : 'unpaid';
                }

                $lockedRegistration->update(array_replace($data, [
                    'user_id' => $member->id,
                    'name' => $name,
                    'phone' => $phone,
                    'checked_by' => $administrator->id,
                    'checked_at' => now(),
                ]));
                $lockedRegistration->income?->details()->update(['name' => $name]);

                return $lockedRegistration;
            }, attempts: 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages(['account' => 'Akun ini sudah masuk dalam daftar sesi.']);
            }

            throw $exception;
        }
    }

    /**
     * @param  array{user_id: int, name: string, phone?: string|null}  $data
     * @return array{User, string, string|null}
     */
    private function resolveIdentity(array $data): array
    {
        $member = User::query()->where('role', 'member')->findOrFail($data['user_id']);
        $memberPhone = $member->phone ? SessionRegistration::normalizePhone($member->phone) : null;
        $phone = $memberPhone && Str::length($memberPhone) >= 10 ? $memberPhone : ($data['phone'] ?? null);

        return [$member, $member->name, $phone];
    }
}
