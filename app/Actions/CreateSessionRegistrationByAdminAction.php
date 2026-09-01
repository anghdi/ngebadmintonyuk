<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateSessionRegistrationByAdminAction
{
    /** @param array{user_id?: int|null, name?: string|null, phone?: string|null, payment_method: string} $data */
    public function handle(PlaySession $playSession, array $data): SessionRegistration
    {
        try {
            return DB::transaction(function () use ($playSession, $data): SessionRegistration {
                $lockedSession = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);
                [$member, $name, $phone] = $this->resolveIdentity($data);

                $noShowCount = SessionRegistration::query()
                    ->where('phone', $phone)
                    ->where('attendance_status', 'no_show')
                    ->count();

                if ($noShowCount >= 3) {
                    throw ValidationException::withMessages(['phone' => 'Pemain ini diblokir karena tiga kali tidak hadir.']);
                }

                return $lockedSession->registrations()->create([
                    'user_id' => $member?->id,
                    'name' => $name,
                    'phone' => $phone,
                    'payment_method' => $data['payment_method'],
                ]);
            }, attempts: 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages(['phone' => 'Pemain ini sudah masuk dalam daftar sesi.']);
            }

            throw $exception;
        }
    }

    /**
     * @param  array{user_id?: int|null, name?: string|null, phone?: string|null}  $data
     * @return array{User|null, string, string}
     */
    private function resolveIdentity(array $data): array
    {
        $member = isset($data['user_id'])
            ? User::query()->where('role', 'member')->findOrFail($data['user_id'])
            : null;
        $memberPhone = $member?->phone ? SessionRegistration::normalizePhone($member->phone) : null;
        $phone = $memberPhone && Str::length($memberPhone) >= 10 ? $memberPhone : ($data['phone'] ?? null);
        $name = $member?->name ?? ($data['name'] ?? null);

        if (! $phone) {
            throw ValidationException::withMessages(['phone' => 'Nomor WhatsApp member belum tersedia.']);
        }

        if (! $name) {
            throw ValidationException::withMessages(['name' => 'Nama pemain wajib diisi.']);
        }

        return [$member, $name, $phone];
    }
}
