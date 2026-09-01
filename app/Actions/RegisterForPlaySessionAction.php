<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterForPlaySessionAction
{
    /** @param array{name: string, phone: string, payment_method: string} $data */
    public function handle(PlaySession $playSession, array $data, ?User $user): SessionRegistration
    {
        try {
            return DB::transaction(function () use ($playSession, $data, $user): SessionRegistration {
                $lockedSession = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);

                if ($lockedSession->status !== 'scheduled' || $lockedSession->scheduled_at->isPast()) {
                    throw ValidationException::withMessages(['session' => 'Pendaftaran untuk sesi ini sudah ditutup.']);
                }

                if ($lockedSession->registrations()->count() >= $lockedSession->max_players) {
                    throw ValidationException::withMessages(['session' => 'Daftar pemain untuk sesi ini sudah penuh.']);
                }

                $noShowCount = SessionRegistration::query()
                    ->where('phone', $data['phone'])
                    ->where('attendance_status', 'no_show')
                    ->count();

                if ($noShowCount >= 3) {
                    throw ValidationException::withMessages(['phone' => 'Nomor ini diblokir karena tiga kali tidak hadir. Hubungi admin untuk peninjauan.']);
                }

                return $lockedSession->registrations()->create([
                    'user_id' => $user && ! $user->isAdmin() ? $user->id : null,
                    'name' => $user && ! $user->isAdmin() ? $user->name : $data['name'],
                    'phone' => $data['phone'],
                    'payment_method' => $data['payment_method'],
                ]);
            }, attempts: 3);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                throw ValidationException::withMessages(['phone' => 'Nomor ini sudah terdaftar pada sesi tersebut.']);
            }

            throw $exception;
        }
    }
}
