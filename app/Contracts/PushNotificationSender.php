<?php

namespace App\Contracts;

use App\Models\PushSubscription;

interface PushNotificationSender
{
    public const string Sent = 'sent';

    public const string Invalid = 'invalid';

    public const string Failed = 'failed';

    public function send(PushSubscription $subscription, string $title, string $body, string $url): string;
}
