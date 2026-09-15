<?php

namespace App\Models;

use Database\Factories\FeedDesignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['created_by', 'format', 'content_type', 'headline', 'supporting_text', 'event_date', 'event_time', 'venue', 'price', 'cta', 'layout_variant', 'photo_path', 'layout_settings', 'row_number', 'grid_position', 'is_seed', 'connection_state', 'exported_assets', 'thumbnail_path'])]
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
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
