<?php

namespace App\Actions;

use App\Models\PlaySession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeletePlaySessionAction
{
    public function handle(PlaySession $playSession): void
    {
        DB::transaction(function () use ($playSession): void {
            $session = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);

            if ($session->registrations()->exists() || $session->attendances()->exists()) {
                throw ValidationException::withMessages(['session' => 'Sesi dengan peserta atau absensi tidak dapat dihapus. Gunakan status dibatalkan setelah menyelesaikan pembayaran dan kuota.']);
            }

            $session->delete();
        }, attempts: 3);
    }
}
