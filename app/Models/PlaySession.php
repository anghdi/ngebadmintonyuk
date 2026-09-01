<?php

namespace App\Models;

use Database\Factories\PlaySessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $scheduled_at
 * @property string $venue_name
 * @property string $court_name
 * @property int $price_per_session
 * @property string $status
 * @property string|null $notes
 * @property int $created_by
 */
#[Fillable(['scheduled_at', 'venue_name', 'court_name', 'price_per_session', 'status', 'notes', 'created_by'])]
class PlaySession extends Model
{
    /** @use HasFactory<PlaySessionFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'scheduled'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime', 'price_per_session' => 'integer'];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** @return HasMany<StockMovement, $this> */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /** @return HasMany<SessionRegistration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(SessionRegistration::class);
    }
}
