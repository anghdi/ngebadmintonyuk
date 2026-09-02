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
        $subscription = PushSubscription::updateOrCreate(
            ['installation_id' => $request->validated('installation_id')],
            [
                'user_id' => $request->user()->id,
                'user_agent' => $request->validated('user_agent'),
            ],
        );

        return response()->json(['active' => true, 'id' => $subscription->id]);
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
