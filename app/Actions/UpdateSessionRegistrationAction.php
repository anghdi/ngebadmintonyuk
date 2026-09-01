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
    /** @param array{user_id?: int|null, name: string, phone: string, payment_method: string, payment_status: string, attendance_status: string, admin_notes?: string|null} $data */
    public function handle(SessionRegistration $registration, array $data, User $administrator): SessionRegistration
    {
        try {
            return DB::transaction(function () use ($registration, $data, $administrator): SessionRegistration {
                $lockedRegistration = SessionRegistration::query()->lockForUpdate()->findOrFail($registration->id);
                [$member, $name, $phone] = $this->resolveIdentity($data);

                $alreadyListed = SessionRegistration::query()
                    ->where('play_session_id', $lockedRegistration->play_session_id)
                    ->where('phone', $phone)
                    ->whereKeyNot($lockedRegistration->id)
                    ->exists();

                if ($alreadyListed) {
                    throw ValidationException::withMessages(['phone' => 'Pemain ini sudah masuk dalam daftar sesi.']);
                }

                $lockedRegistration->update(array_replace($data, [
                    'user_id' => $member?->id,
                    'name' => $name,
                    'phone' => $phone,
                    'checked_by' => $administrator->id,
                    'checked_at' => now(),
                ]));

                return $lockedRegistration;
            }, attempts: 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages(['phone' => 'Pemain ini sudah masuk dalam daftar sesi.']);
            }

            throw $exception;
        }
    }

    /**
     * @param  array{user_id?: int|null, name: string, phone: string}  $data
     * @return array{User|null, string, string}
     */
    private function resolveIdentity(array $data): array
    {
        $member = isset($data['user_id'])
            ? User::query()->where('role', 'member')->findOrFail($data['user_id'])
            : null;
        $memberPhone = $member?->phone ? SessionRegistration::normalizePhone($member->phone) : null;
        $phone = $memberPhone && Str::length($memberPhone) >= 10 ? $memberPhone : $data['phone'];

        return [$member, $member?->name ?? $data['name'], $phone];
    }
}
