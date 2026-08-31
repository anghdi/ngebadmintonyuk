<?php

namespace App\Models;

use Database\Factories\ShuttlecockItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $brand
 * @property int $pieces_per_tube
 * @property int $minimum_stock
 * @property bool $is_active
 * @property int $created_by
 * @property int|null $stock
 */
#[Fillable(['name', 'brand', 'pieces_per_tube', 'minimum_stock', 'is_active', 'created_by'])]
class ShuttlecockItem extends Model
{
    /** @use HasFactory<ShuttlecockItemFactory> */
    use HasFactory;

    protected $attributes = ['pieces_per_tube' => 12, 'minimum_stock' => 12, 'is_active' => true];

    protected function casts(): array
    {
        return ['pieces_per_tube' => 'integer', 'minimum_stock' => 'integer', 'is_active' => 'boolean'];
    }

    /** @return HasMany<StockMovement, $this> */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
