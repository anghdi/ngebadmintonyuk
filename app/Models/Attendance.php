<?php

namespace App\Models;

use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $play_session_id
 * @property int $user_id
 * @property int|null $membership_id
 * @property string $status
 * @property string|null $notes
 * @property int $recorded_by
 */
#[Fillable(['play_session_id', 'user_id', 'membership_id', 'status', 'notes', 'recorded_by'])]
class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasFactory;

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Membership, $this> */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /** @return BelongsTo<User, $this> */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** @return HasOne<MembershipTransaction, $this> */
    public function transaction(): HasOne
    {
        return $this->hasOne(MembershipTransaction::class);
    }
}
