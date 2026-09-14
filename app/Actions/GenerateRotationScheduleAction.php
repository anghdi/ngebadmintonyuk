<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Services\RotationScheduleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateRotationScheduleAction
{
    public function __construct(private RotationScheduleService $schedules) {}

    public function handle(PlaySession $session, int $roundCount, int $expectedVersion, string $fingerprint): void
    {
        DB::transaction(function () use ($session, $roundCount, $expectedVersion, $fingerprint): void {
            $locked = PlaySession::query()->lockForUpdate()->findOrFail($session->id);
            $registrations = $locked->registrations()->oldest('id')->limit($locked->max_players)->get();
            $roster = $this->schedules->roster($registrations);
            $slots = $locked->court_count * 4;
            $minimum = max(1, (int) ceil(count($roster) / $slots));

            if ($locked->status !== 'scheduled') {
                throw ValidationException::withMessages(['round_count' => 'Rotasi hanya dapat dibuat untuk sesi terjadwal.']);
            }
            if (! in_array($locked->court_count, [1, 2], true) || count($roster) < $slots) {
                throw ValidationException::withMessages(['round_count' => 'Perlu minimal '.$slots.' pemain utama untuk '.$locked->court_count.' lapangan.']);
            }
            if ($roundCount < $minimum || $roundCount > 80) {
                throw ValidationException::withMessages(['round_count' => 'Pilih '.$minimum.'–80 ronde agar semua pemain mendapat giliran.']);
            }
            if (($locked->rotation_schedule['version'] ?? 0) !== $expectedVersion || $this->schedules->fingerprint($roster, $locked->court_count) !== $fingerprint) {
                throw ValidationException::withMessages(['round_count' => 'List atau jadwal telah berubah. Muat ulang halaman sebelum generate.']);
            }

            $locked->rotation_schedule = $this->schedules->generate($roster, $locked->court_count, $roundCount) + ['version' => $expectedVersion + 1];
            $locked->save();
        });
    }
}
