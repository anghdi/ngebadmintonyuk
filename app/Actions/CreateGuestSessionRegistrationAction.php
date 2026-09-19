<?php

namespace App\Actions;

use App\Models\Guest;
use App\Models\PlaySession;
use App\Models\SessionRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateGuestSessionRegistrationAction
{
    /** @param array{guest_id: int, payment_method: string} $data */
    public function handle(PlaySession $playSession, array $data): SessionRegistration
    {
        return DB::transaction(function () use ($playSession, $data): SessionRegistration {
            $session = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);
            $guest = Guest::query()->lockForUpdate()->findOrFail($data['guest_id']);
            if ($session->status !== 'scheduled' || $session->scheduled_at->isPast()) {
                throw ValidationException::withMessages(['session' => 'Pendaftaran sesi sudah ditutup.']);
            }
            if (! in_array($data['payment_method'], ['cash', 'transfer'], true)) {
                throw ValidationException::withMessages(['payment_method' => 'Tamu hanya dapat membayar tunai atau transfer.']);
            }
            if (! in_array($guest->playing_level, ['beginner', 'intermediate', 'advanced'], true)) {
                throw ValidationException::withMessages(['guest_id' => 'Isi level tamu di menu Tamu sebelum menambahkannya ke sesi.']);
            }
            if ($session->registrations()->where('guest_id', $guest->id)->exists()) {
                throw ValidationException::withMessages(['guest_id' => 'Tamu sudah masuk daftar sesi.']);
            }
            if ($session->registrations()->count() >= $session->max_players + $session->max_waiting_players) {
                throw ValidationException::withMessages(['session' => 'Slot utama dan waiting list sudah penuh.']);
            }
            if ($guest->registrations()->where('attendance_status', 'no_show')->count() >= 3) {
                throw ValidationException::withMessages(['guest_id' => 'Tamu diblokir karena tiga kali tidak hadir.']);
            }

            return $session->registrations()->create([
                'guest_id' => $guest->id, 'user_id' => null,
                'name' => $guest->name, 'phone' => $guest->phone,
                'payment_method' => $data['payment_method'],
            ]);
        }, attempts: 3);
    }
}
