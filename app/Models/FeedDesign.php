<?php

namespace App\Models;

use Database\Factories\FeedDesignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['created_by', 'connected_from_id', 'format', 'content_type', 'headline', 'supporting_text', 'event_date', 'event_time', 'venue', 'price', 'cta', 'layout_variant', 'photo_path', 'layout_settings', 'row_number', 'grid_position', 'is_seed', 'connection_state', 'exported_assets', 'thumbnail_path', 'exported_at', 'published_at'])]
class FeedDesign extends Model
{
    /** @use HasFactory<FeedDesignFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'layout_settings' => 'array',
            'connection_state' => 'array',
            'exported_assets' => 'array',
            'is_seed' => 'boolean',
            'exported_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<FeedDesign, $this> */
    public function connectedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'connected_from_id');
    }

    /** @return HasMany<FeedDesign, $this> */
    public function connectedRows(): HasMany
    {
        return $this->hasMany(self::class, 'connected_from_id');
    }

    public function workflowLabel(): string
    {
        return match (true) {
            $this->is_seed => 'Fondasi',
            $this->published_at !== null => 'Dipublikasikan',
            $this->exported_at !== null => 'Sudah diekspor',
            default => 'Draf',
        };
    }

    public function workflowTone(): string
    {
        return match (true) {
            $this->published_at !== null => 'active',
            $this->exported_at !== null || $this->is_seed => 'warning',
            default => 'muted',
        };
    }

    public function postCount(): int
    {
        return match ($this->format) {
            'connected_2' => 2,
            'connected_3' => 3,
            default => 1,
        };
    }
}
