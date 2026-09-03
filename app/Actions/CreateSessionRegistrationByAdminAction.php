<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSessionRegistrationByAdminAction
{
    /** @param array{user_id: int, payment_method: string} $data */
    public function handle(PlaySession $playSession, array $data): SessionRegistration
    {
        try {
            return DB::transaction(function () use ($playSession, $data): SessionRegistration {
                $lockedSession = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);
                [$member, $name, $phone] = $this->resolveIdentity($data);

                if ($lockedSession->registrations()->where('user_id', $member->id)->exists()) {
                    throw ValidationException::withMessages(['account' => 'Akun ini sudah masuk dalam daftar sesi.']);
                }

                $totalCapacity = $lockedSession->max_players + $lockedSession->max_waiting_players;

                if ($lockedSession->registrations()->count() >= $totalCapacity) {
                    throw ValidationException::withMessages(['session' => 'Slot utama dan waiting list sudah penuh.']);
                }

                $noShowCount = SessionRegistration::query()
                    ->where('user_id', $member->id)
                    ->where('attendance_status', 'no_show')
                    ->count();

                if ($noShowCount >= 3) {
                    throw ValidationException::withMessages(['phone' => 'Pemain ini diblokir karena tiga kali tidak hadir.']);
                }

                return $lockedSession->registrations()->create([
                    'user_id' => $member->id,
                    'name' => $name,
                    'phone' => $phone,
                    'payment_method' => $data['payment_method'],
                ]);
            }, attempts: 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages(['account' => 'Akun ini sudah masuk dalam daftar sesi.']);
            }

            throw $exception;
        }
    }

    /**
     * @param  array{user_id: int}  $data
     * @return array{User, string, string|null}
     */
    private function resolveIdentity(array $data): array
    {
        $member = User::query()->where('role', 'member')->findOrFail($data['user_id']);
        $phone = $member->phone ? SessionRegistration::normalizePhone($member->phone) : null;

        return [$member, $member->name, $phone];
    }
}
