<?php

namespace App\Services;

use App\Contracts\PushNotificationSender;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;
use JsonException;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use RuntimeException;

class WebPushNotificationSender
{
    public function send(PushSubscription $pushSubscription, string $title, string $body, string $url): string
    {
        $endpoint = $pushSubscription->endpoint;
        $publicKey = $pushSubscription->public_key;
        $authToken = $pushSubscription->auth_token;

        if (! is_string($endpoint) || $endpoint === ''
            || ! is_string($publicKey) || $publicKey === ''
            || ! is_string($authToken) || $authToken === '') {
            return PushNotificationSender::Invalid;
        }

        $webPush = new WebPush([
            'VAPID' => $this->vapidConfiguration(),
        ], [
            'TTL' => 86400,
            'urgency' => 'normal',
        ]);
        $subscription = Subscription::create([
            'endpoint' => $endpoint,
            'publicKey' => $publicKey,
            'authToken' => $authToken,
            'contentEncoding' => $pushSubscription->content_encoding ?? 'aes128gcm',
        ]);

        try {
            $payload = json_encode([
                'source' => 'webpush',
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'icon' => url('/notification-icon.png'),
                'badge' => url('/notification-badge-96.png'),
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Isi notifikasi tidak dapat diproses.', previous: $exception);
        }

        $report = $webPush->sendOneNotification($subscription, $payload);

        if ($report->isSuccess()) {
            return PushNotificationSender::Sent;
        }

        if ($report->isSubscriptionExpired()) {
            return PushNotificationSender::Invalid;
        }

        Log::warning('Layanan Web Push menolak pengiriman notifikasi.', [
            'status' => $report->getResponse()?->getStatusCode(),
            'reason' => $report->getReason(),
        ]);

        return PushNotificationSender::Failed;
    }

    /** @return array{subject: string, publicKey: string, privateKey: string} */
    private function vapidConfiguration(): array
    {
        $subject = config('services.webpush.subject');
        $publicKey = config('services.webpush.public_key');
        $privateKey = config('services.webpush.private_key');

        if (! is_string($subject) || $subject === ''
            || ! is_string($publicKey) || $publicKey === ''
            || ! is_string($privateKey) || $privateKey === '') {
            throw new RuntimeException('Konfigurasi VAPID Web Push belum lengkap.');
        }

        return compact('subject', 'publicKey', 'privateKey');
    }
}
