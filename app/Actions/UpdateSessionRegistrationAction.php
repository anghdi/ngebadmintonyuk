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
    /** @param array{user_id: int, name: string, phone?: string|null, payment_method: string, payment_status: string, attendance_status: string, admin_notes?: string|null} $data */
    public function handle(SessionRegistration $registration, array $data, User $administrator): SessionRegistration
    {
        try {
            return DB::transaction(function () use ($registration, $data, $administrator): SessionRegistration {
                $lockedRegistration = SessionRegistration::query()->lockForUpdate()->findOrFail($registration->id);
                [$member, $name, $phone] = $this->resolveIdentity($data);

                $alreadyListed = SessionRegistration::query()
                    ->where('play_session_id', $lockedRegistration->play_session_id)
                    ->where('user_id', $member->id)
                    ->whereKeyNot($lockedRegistration->id)
                    ->exists();

                if ($alreadyListed) {
                    throw ValidationException::withMessages(['account' => 'Akun ini sudah masuk dalam daftar sesi.']);
                }

                $lockedRegistration->update(array_replace($data, [
                    'user_id' => $member->id,
                    'name' => $name,
                    'phone' => $phone,
                    'checked_by' => $administrator->id,
                    'checked_at' => now(),
                ]));

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
