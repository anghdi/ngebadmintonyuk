<?php

namespace App\Models;

use Database\Factories\PushNotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['type', 'audience', 'play_session_id', 'title', 'body', 'url', 'recipient_count', 'device_count', 'success_count', 'failure_count', 'sent_by'])]
class PushNotification extends Model
{
    /** @use HasFactory<PushNotificationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'recipient_count' => 'integer',
            'device_count' => 'integer',
            'success_count' => 'integer',
            'failure_count' => 'integer',
        ];
    }

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
