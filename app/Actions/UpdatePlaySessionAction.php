<?php

namespace App\Actions;

use App\Models\PlaySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdatePlaySessionAction
{
    /** @param array<string, mixed> $data */
    public function handle(PlaySession $playSession, array $data): PlaySession
    {
        return DB::transaction(function () use ($playSession, $data): PlaySession {
            $session = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);
            $count = $session->registrations()->count();

            if ((int) $data['max_players'] < min($count, $session->max_players)
                || (int) $data['max_players'] + (int) $data['max_waiting_players'] < $count) {
                throw ValidationException::withMessages(['max_players' => 'Kapasitas tidak boleh menggeser atau menghilangkan peserta yang sudah terdaftar.']);
            }

            $hasPayments = $session->registrations()->where('payment_status', 'paid')->exists();
            $hasAttendance = $session->attendances()->exists();
            $coreFields = ['scheduled_at', 'venue_name', 'court_name', 'price_per_session'];
            $session->fill($data);

            if (($hasPayments || $hasAttendance) && $session->isDirty($coreFields)) {
                throw ValidationException::withMessages(['scheduled_at' => 'Selesaikan koreksi pembayaran dan absensi sebelum mengubah jadwal, lapangan atau harga.']);
            }

            if ($data['status'] === 'cancelled' && ($hasPayments || $session->attendances()->whereHas('transaction')->exists())) {
                throw ValidationException::withMessages(['status' => 'Kembalikan kuota melalui koreksi absensi dan selesaikan pengembalian pembayaran sebelum membatalkan sesi.']);
            }

            $session->save();

            return $session;
        }, attempts: 3);
    }
}
