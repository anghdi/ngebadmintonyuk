<?php

namespace App\Models;

use Database\Factories\MembershipTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $membership_id
 * @property int|null $attendance_id
 * @property string $type
 * @property int $quantity
 * @property string|null $notes
 * @property int $created_by
 */
#[Fillable(['membership_id', 'attendance_id', 'type', 'quantity', 'notes', 'created_by'])]
class MembershipTransaction extends Model
{
    /** @use HasFactory<MembershipTransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    /** @return BelongsTo<Membership, $this> */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /** @return BelongsTo<Attendance, $this> */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
