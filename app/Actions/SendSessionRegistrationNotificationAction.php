<?php

namespace App\Actions;

use App\Models\PlaySession;
use App\Models\SessionRegistration;
use App\Models\User;
use Throwable;

class SendSessionRegistrationNotificationAction
{
    public function __construct(private SendPushNotificationAction $sendPushNotification) {}

    public function joined(SessionRegistration $registration, PlaySession $playSession, User $actor): void
    {
        $this->send(
            type: 'session_joined',
            title: "{$registration->name} ikut sesi",
            body: "{$registration->name} ikut pada sesi tanggal {$playSession->scheduled_at->format('d/m/Y')} pukul {$playSession->scheduled_at->format('H:i')} WITA di {$playSession->venue_name}.",
            playSession: $playSession,
            actor: $actor,
        );
    }

    public function cancelled(SessionRegistration $registration, PlaySession $playSession, User $actor): void
    {
        $this->send(
            type: 'session_cancelled',
            title: "{$registration->name} batal ikut sesi",
            body: "{$registration->name} batal ikut pada sesi tanggal {$playSession->scheduled_at->format('d/m/Y')} pukul {$playSession->scheduled_at->format('H:i')} WITA di {$playSession->venue_name}.",
            playSession: $playSession,
            actor: $actor,
        );
    }

    private function send(string $type, string $title, string $body, PlaySession $playSession, User $actor): void
    {
        try {
            $this->sendPushNotification->handle([
                'type' => $type,
                'audience' => 'all',
                'play_session_id' => $playSession->id,
                'title' => $title,
                'body' => $body,
            ], $actor);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
