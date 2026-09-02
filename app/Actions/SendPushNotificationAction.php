<?php

namespace App\Actions;

use App\Contracts\PushNotificationSender;
use App\Models\PlaySession;
use App\Models\PushNotification;
use App\Models\PushSubscription;
use App\Models\User;
use Throwable;

class SendPushNotificationAction
{
    public function __construct(private PushNotificationSender $sender) {}

    /**
     * @param  array{type: string, audience: string, play_session_id?: int|null, title: string, body: string}  $data
     */
    public function handle(array $data, User $administrator): PushNotification
    {
        $playSession = isset($data['play_session_id'])
            ? PlaySession::findOrFail($data['play_session_id'])
            : null;
        $subscriptions = PushSubscription::query()
            ->whereHas('user', function ($query) use ($data, $playSession): void {
                $query->where('role', 'member');

                if ($data['audience'] === 'session' && $playSession !== null) {
                    $query->whereHas('sessionRegistrations', fn ($registrationQuery) => $registrationQuery
                        ->whereBelongsTo($playSession)
                        ->where('attendance_status', 'listed'));
                }
            });
        $url = $playSession === null
            ? route('dashboard')
            : route('public-sessions.show', $playSession);
        $notification = PushNotification::create([
            ...$data,
            'play_session_id' => $playSession?->id,
            'url' => $url,
            'recipient_count' => (clone $subscriptions)->distinct()->count('user_id'),
            'device_count' => (clone $subscriptions)->count(),
            'sent_by' => $administrator->id,
        ]);
        $successCount = 0;
        $failureCount = 0;

        $subscriptions->eachById(function (PushSubscription $subscription) use ($data, $url, &$successCount, &$failureCount): void {
            try {
                $result = $this->sender->send(
                    $subscription->installation_id,
                    $data['title'],
                    $data['body'],
                    $url,
                );

                if ($result === PushNotificationSender::Sent) {
                    $successCount++;

                    return;
                }

                if ($result === PushNotificationSender::Invalid) {
                    $subscription->delete();
                }
            } catch (Throwable $exception) {
                report($exception);
            }

            $failureCount++;
        });

        $notification->update([
            'success_count' => $successCount,
            'failure_count' => $failureCount,
        ]);

        return $notification->refresh();
    }
}
