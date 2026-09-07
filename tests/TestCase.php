<?php

namespace Tests;

use App\Contracts\PushNotificationSender;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function actingAsNotifiedMember(User $member): static
    {
        $subscription = PushSubscription::factory()->for($member)->create(['driver' => 'webpush']);
        $this->mock(PushNotificationSender::class)
            ->shouldReceive('send')->andReturn(PushNotificationSender::Sent);

        return $this->actingAs($member)->withSession(['push_installation_id' => $subscription->installation_id]);
    }
}
