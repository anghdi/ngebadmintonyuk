<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeletePushSubscriptionRequest;
use App\Http\Requests\StorePushSubscriptionRequest;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $driver = $data['driver'];
        $installationId = $driver === 'webpush'
            ? hash('sha256', $data['endpoint'])
            : $data['installation_id'];
        $subscription = PushSubscription::updateOrCreate(
            ['installation_id' => $installationId],
            [
                'user_id' => $request->user()->id,
                'driver' => $driver,
                'endpoint' => $driver === 'webpush' ? $data['endpoint'] : null,
                'public_key' => $driver === 'webpush' ? $data['public_key'] : null,
                'auth_token' => $driver === 'webpush' ? $data['auth_token'] : null,
                'content_encoding' => $driver === 'webpush' ? ($data['content_encoding'] ?? 'aes128gcm') : null,
                'user_agent' => $data['user_agent'] ?? null,
            ],
        );

        return response()->json([
            'active' => true,
            'id' => $subscription->id,
            'installation_id' => $subscription->installation_id,
        ]);
    }

    public function destroy(DeletePushSubscriptionRequest $request): Response
    {
        PushSubscription::query()
            ->whereBelongsTo($request->user())
            ->where('installation_id', $request->validated('installation_id'))
            ->delete();

        return response()->noContent();
    }
}
