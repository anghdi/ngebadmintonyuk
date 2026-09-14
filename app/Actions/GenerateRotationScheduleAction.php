<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Services\RotationScheduleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateRotationScheduleAction
{
    public function __construct(private RotationScheduleService $schedules) {}

    public function handle(PlaySession $session, int $setsPerMatch, int $expectedVersion, string $fingerprint, int $sessionDurationMinutes = 180, int $minutesPerSet = 15): void
    {
        DB::transaction(function () use ($session, $setsPerMatch, $expectedVersion, $fingerprint, $sessionDurationMinutes, $minutesPerSet): void {
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

            if ($sessionDurationMinutes < 1 || $sessionDurationMinutes > 1440 || $minutesPerSet < 1 || $minutesPerSet > 120) {
                throw ValidationException::withMessages(['session_duration_minutes' => 'Periksa durasi sesi dan menit per set.']);
            }
            $minutesPerRound = $setsPerMatch * $minutesPerSet;
            $roundCount = min(80, intdiv($sessionDurationMinutes, $minutesPerRound));
            if ($roundCount < 1) {
                throw ValidationException::withMessages(['session_duration_minutes' => 'Durasi sesi tidak cukup untuk satu ronde.']);
            }
            $locked->rotation_schedule = $this->schedules->generate($roster, $locked->court_count, $roundCount) + [
                'version' => $expectedVersion + 1,
                'published_at' => null,
                'sets_per_match' => $setsPerMatch,
                'points_per_set' => 21,
                'session_duration_minutes' => $sessionDurationMinutes,
                'minutes_per_set' => $minutesPerSet,
                'minutes_per_round' => $minutesPerRound,
                'play_until' => $locked->scheduled_at->copy()->addMinutes($sessionDurationMinutes)->format('H:i'),
            ];
            $locked->save();
        });
    }
}
