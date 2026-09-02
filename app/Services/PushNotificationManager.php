<?php

namespace App\Services;

use App\Contracts\PushNotificationSender;
use App\Models\PushSubscription;

class PushNotificationManager implements PushNotificationSender
{
    public function __construct(
        private FirebaseCloudMessaging $firebase,
        private WebPushNotificationSender $webPush,
    ) {}

    public function send(PushSubscription $subscription, string $title, string $body, string $url): string
    {
        return match ($subscription->driver) {
            'webpush' => $this->webPush->send($subscription, $title, $body, $url),
            default => $this->firebase->send($subscription, $title, $body, $url),
        };
    }
}
