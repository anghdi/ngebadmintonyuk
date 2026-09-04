<?php

namespace App\Actions;

use App\Models\User;

class ResetLegacyPushSubscriptionsAction
{
    public function handle(User $user): bool
    {
        return $user->pushSubscriptions()
            ->where('driver', 'fcm')
            ->delete() > 0;
    }
}
