<?php

namespace App\Actions;

use App\Models\Attendance;
use App\Models\Membership;
use App\Models\PlaySession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordAttendanceAction
{
    public function handle(PlaySession $playSession, User $member, string $status, ?string $notes, User $administrator, bool $usesCredits = true): Attendance
    {
        return DB::transaction(function () use ($playSession, $member, $status, $notes, $administrator, $usesCredits): Attendance {
            $playSession = PlaySession::query()->lockForUpdate()->findOrFail($playSession->id);
            User::query()->lockForUpdate()->findOrFail($member->id);

            if ($status === 'present' && $playSession->status === 'cancelled') {
                throw ValidationException::withMessages(['status' => 'Sesi yang dibatalkan tidak dapat mencatat kehadiran.']);
            }

            if (! in_array($status, ['present', 'absent'], true)) {
                throw ValidationException::withMessages(['status' => 'Tidak hadir tidak boleh memotong kuota.']);
            }

            $attendance = Attendance::query()
                ->whereBelongsTo($playSession)
                ->whereBelongsTo($member)
                ->lockForUpdate()
                ->first() ?? new Attendance(['play_session_id' => $playSession->id, 'user_id' => $member->id]);

            $hasUsage = $attendance->exists && $attendance->transaction()->exists();
            if ($attendance->exists && $attendance->status === $status && $hasUsage === ($usesCredits && $status === 'present')) {
                $attendance->update(['notes' => $notes, 'recorded_by' => $administrator->id]);

                return $attendance;
            }

            if ($attendance->exists) {
                $attendance->transaction()->delete();
            }

            $membership = $usesCredits && $status === 'present'
                ? $this->compatibleMembership($playSession, $member)
                : null;

            $attendance->fill([
                'membership_id' => $membership?->id,
                'status' => $status,
                'notes' => $notes,
                'recorded_by' => $administrator->id,
            ])->save();

            if ($membership) {
                $membership->transactions()->create([
                    'attendance_id' => $attendance->id,
                    'type' => 'usage',
                    'quantity' => -1,
                    'notes' => 'Hadir bermain',
                    'created_by' => $administrator->id,
                ]);
            }

            return $attendance;
        }, attempts: 3);
    }

    private function compatibleMembership(PlaySession $playSession, User $member): Membership
    {
        $memberships = Membership::query()
            ->whereBelongsTo($member)
            ->where('status', 'active')
            ->where(function ($query) use ($playSession): void {
                $query->where(function ($query) use ($playSession): void {
                    $query->where('venue_name', $playSession->venue_name)
                        ->where('court_name', $playSession->court_name)
                        ->where('price_per_session', $playSession->price_per_session);
                })->orWhere(function ($query): void {
                    $query->where('venue_name', Membership::COMMUNITY_VENUE)
                        ->where('court_name', Membership::COMMUNITY_COURT)
                        ->where('price_per_session', Membership::COMMUNITY_PRICE);
                });
            })
            ->whereDate('starts_on', '<=', $playSession->scheduled_at->toDateString())
            ->where(fn ($query) => $query->whereNull('expires_on')->orWhereDate('expires_on', '>=', $playSession->scheduled_at->toDateString()))
            ->withSum('transactions as balance', 'quantity')
            ->oldest('starts_on')
            ->oldest('id')
            ->lockForUpdate()
            ->get()
            ->filter(fn (Membership $membership): bool => (int) $membership->balance > 0);

        $membership = $memberships->first(fn (Membership $membership): bool => ! $membership->isCommunityPackage())
            ?? $memberships->first(fn (Membership $membership): bool => $membership->isCommunityPackage());

        if (! $membership) {
            throw ValidationException::withMessages([
                'status' => 'Member tidak memiliki kuota yang dapat digunakan untuk sesi ini.',
            ]);
        }

        return $membership;
    }
}
