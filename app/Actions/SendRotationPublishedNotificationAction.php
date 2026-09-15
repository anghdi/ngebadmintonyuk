<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Models\User;
use Throwable;

class SendRotationPublishedNotificationAction
{
    public function __construct(private SendPushNotificationAction $sendPushNotification) {}

    public function handle(PlaySession $playSession, User $actor): void
    {
        $recipientUserIds = collect($playSession->rotation_schedule['roster'] ?? [])
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        try {
            $this->sendPushNotification->handle([
                'type' => 'rotation_published',
                'audience' => 'session',
                'play_session_id' => $playSession->id,
                'recipient_user_ids' => $recipientUserIds,
                'title' => 'Rotasi bermain sudah tersedia',
                'body' => "Cek pasangan dan lawanmu untuk sesi {$playSession->scheduled_at->format('d/m/Y')}.",
                'url' => route('rotations.show', $playSession),
            ], $actor);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
