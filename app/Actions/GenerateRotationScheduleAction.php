<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Services\RotationScheduleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateRotationScheduleAction
{
    public function __construct(private RotationScheduleService $schedules) {}

    public function handle(PlaySession $session, int $setsPerMatch, int $expectedVersion, string $fingerprint): void
    {
        DB::transaction(function () use ($session, $setsPerMatch, $expectedVersion, $fingerprint): void {
            $locked = PlaySession::query()->lockForUpdate()->findOrFail($session->id);
            $registrations = $locked->registrations()->oldest('id')->limit($locked->max_players)->get();
            $roster = $this->schedules->roster($registrations);
            $slots = $locked->court_count * 4;

            if ($locked->status !== 'scheduled') {
                throw ValidationException::withMessages(['sets_per_match' => 'Rotasi hanya dapat dibuat untuk sesi terjadwal.']);
            }
            if (! in_array($locked->court_count, [1, 2], true) || count($roster) < $slots) {
                throw ValidationException::withMessages(['sets_per_match' => 'Perlu minimal '.$slots.' pemain utama untuk '.$locked->court_count.' lapangan.']);
            }
            if (! in_array($setsPerMatch, [1, 2], true)) {
                throw ValidationException::withMessages(['sets_per_match' => 'Pilih 1 atau 2 set × 21 poin.']);
            }
            if (($locked->rotation_schedule['version'] ?? 0) !== $expectedVersion || $this->schedules->fingerprint($roster, $locked->court_count) !== $fingerprint) {
                throw ValidationException::withMessages(['sets_per_match' => 'List atau jadwal telah berubah. Muat ulang halaman sebelum generate.']);
            }

            $targetGames = $setsPerMatch === 1 ? 3 : 2;
            $roundCount = min(80, (int) ceil(count($roster) * $targetGames / $slots));
            $locked->rotation_schedule = $this->schedules->generate($roster, $locked->court_count, $roundCount) + [
                'version' => $expectedVersion + 1,
                'published_at' => null,
                'sets_per_match' => $setsPerMatch,
                'points_per_set' => 21,
                'play_until' => '23:00',
            ];
            $locked->save();
        });
    }
}
