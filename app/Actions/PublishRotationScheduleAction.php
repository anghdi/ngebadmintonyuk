<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Services\RotationScheduleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishRotationScheduleAction
{
    public function __construct(private RotationScheduleService $schedules) {}

    public function handle(PlaySession $session, int $expectedVersion): void
    {
        DB::transaction(function () use ($session, $expectedVersion): void {
            $locked = PlaySession::query()->lockForUpdate()->findOrFail($session->id);
            $schedule = $locked->rotation_schedule;
            $roster = $this->schedules->roster($locked->registrations()->oldest('id')->limit($locked->max_players)->get());
            if ($locked->status !== 'scheduled' || $schedule === null
                || ($schedule['version'] ?? 0) !== $expectedVersion
                || ($schedule['fingerprint'] ?? null) !== $this->schedules->fingerprint($roster, $locked->court_count)) {
                throw ValidationException::withMessages(['rotation' => 'Draf atau list berubah. Review jadwal terbaru sebelum publikasi.']);
            }
            if (($schedule['published_at'] ?? null) !== null) {
                return;
            }
            $schedule['published_at'] = now()->toIso8601String();
            $locked->rotation_schedule = $schedule;
            $locked->save();
        });
    }
}
