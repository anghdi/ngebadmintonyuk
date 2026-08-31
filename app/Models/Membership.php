<?php

namespace App\Models;

use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $venue_name
 * @property string $court_name
 * @property int $price_per_session
 * @property int $initial_credits
 * @property Carbon $starts_on
 * @property Carbon|null $expires_on
 * @property string $status
 * @property string|null $notes
 * @property int $created_by
 * @property int|null $balance
 */
#[Fillable(['user_id', 'venue_name', 'court_name', 'price_per_session', 'initial_credits', 'starts_on', 'expires_on', 'status', 'notes', 'created_by'])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    protected $attributes = ['initial_credits' => 4, 'status' => 'active'];

    protected function casts(): array
    {
        return [
            'price_per_session' => 'integer',
            'initial_credits' => 'integer',
            'starts_on' => 'date',
            'expires_on' => 'date',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<MembershipTransaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(MembershipTransaction::class);
    }

    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
