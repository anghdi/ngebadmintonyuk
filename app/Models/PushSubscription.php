<?php

namespace App\Models;

use Database\Factories\PushSubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'driver',
    'installation_id',
    'endpoint',
    'public_key',
    'auth_token',
    'content_encoding',
    'user_agent',
])]
class PushSubscription extends Model
{
    /** @use HasFactory<PushSubscriptionFactory> */
    use HasFactory;

    /** @var array<string, mixed> */
    protected $attributes = [
        'driver' => 'fcm',
    ];

    /** @var list<string> */
    protected $hidden = [
        'endpoint',
        'public_key',
        'auth_token',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'endpoint' => 'encrypted',
            'public_key' => 'encrypted',
            'auth_token' => 'encrypted',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
