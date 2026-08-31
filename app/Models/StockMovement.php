<?php

namespace App\Models;

use Database\Factories\StockMovementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $shuttlecock_item_id
 * @property int|null $play_session_id
 * @property string $type
 * @property int $quantity
 * @property int|null $unit_cost
 * @property string|null $notes
 * @property int $created_by
 * @property Carbon $created_at
 */
#[Fillable(['shuttlecock_item_id', 'play_session_id', 'type', 'quantity', 'unit_cost', 'notes', 'created_by'])]
class StockMovement extends Model
{
    /** @use HasFactory<StockMovementFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'unit_cost' => 'integer'];
    }

    /** @return BelongsTo<ShuttlecockItem, $this> */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ShuttlecockItem::class, 'shuttlecock_item_id');
    }

    /** @return BelongsTo<PlaySession, $this> */
    public function playSession(): BelongsTo
    {
        return $this->belongsTo(PlaySession::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
