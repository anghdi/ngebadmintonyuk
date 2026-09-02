<?php

namespace App\Services;

use App\Models\PushSubscription;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Throwable;

class FirebaseCloudMessaging
{
    private ?string $accessToken = null;

    public function send(PushSubscription $subscription, string $title, string $body, string $url): string
    {
        $projectId = $this->projectId();
        $response = Http::acceptJson()
            ->asJson()
            ->withToken($this->accessToken())
            ->connectTimeout(5)
            ->timeout(10)
            ->retry(
                [100, 300],
                fn (Throwable $exception, PendingRequest $request): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError()),
                throw: false,
            )
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => [
                    'token' => $subscription->installation_id,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => [
                        'url' => $url,
                    ],
                    'webpush' => [
                        'notification' => [
                            'icon' => url('/icon.png'),
                            'badge' => url('/icon.png'),
                        ],
                        'fcm_options' => [
                            'link' => $url,
                        ],
                    ],
                ],
            ]);

        if ($response->successful()) {
            return self::Sent;
        }

        if ($response->notFound()) {
            return self::Invalid;
        }

        Log::warning('FCM menolak pengiriman push notification.', [
            'status' => $response->status(),
            'error_status' => $response->json('error.status'),
        ]);

        return self::Failed;
    }

    private function accessToken(): string
    {
        if ($this->accessToken !== null) {
            return $this->accessToken;
        }

        $credentials = $this->credentials();
        $googleCredentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $credentials,
        );
        $token = $googleCredentials->fetchAuthToken();
        $accessToken = Arr::get($token, 'access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Google tidak memberikan access token untuk FCM.');
        }

        return $this->accessToken = $accessToken;
    }

    /** @return array<string, mixed> */
    private function credentials(): array
    {
        $path = config('services.firebase.credentials');

        if (! is_string($path) || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('File kredensial Firebase tidak ditemukan atau tidak dapat dibaca.');
        }

        try {
            $contents = file_get_contents($path);
            $credentials = json_decode($contents === false ? '' : $contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('File kredensial Firebase bukan JSON yang valid.', previous: $exception);
        }

        if (! is_array($credentials)
            || Arr::get($credentials, 'type') !== 'service_account'
            || Arr::get($credentials, 'project_id') !== $this->projectId()
            || ! Str::endsWith((string) Arr::get($credentials, 'client_email'), '.iam.gserviceaccount.com')) {
            throw new RuntimeException('File kredensial Firebase tidak cocok dengan project yang dikonfigurasi.');
        }

        return $credentials;
    }

    private function projectId(): string
    {
        $projectId = config('services.firebase.project_id');

        if (! is_string($projectId) || $projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID belum dikonfigurasi.');
        }

        return $projectId;
    }
}
