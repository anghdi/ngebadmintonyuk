<?php

namespace App\Models;

use Database\Factories\TopUpRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $membership_id
 * @property int $amount
 * @property string $bank
 * @property string $proof_path
 * @property string $status
 * @property int|null $credits
 * @property string|null $review_notes
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 */
#[Fillable(['user_id', 'membership_id', 'amount', 'bank', 'proof_path', 'status', 'credits', 'review_notes', 'reviewed_by', 'reviewed_at'])]

class TopUpRequest extends Model
{
    /** @use HasFactory<TopUpRequestFactory> */
    use HasFactory;

    protected $attributes = ['amount' => 110000, 'status' => 'pending'];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'credits' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<Membership, $this> */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
