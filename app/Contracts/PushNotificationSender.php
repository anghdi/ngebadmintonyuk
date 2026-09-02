<?php

namespace App\Contracts;

interface PushNotificationSender
{
    public const string Sent = 'sent';

    public const string Invalid = 'invalid';

    public const string Failed = 'failed';

    public function send(string $installationId, string $title, string $body, string $url): string;
}
