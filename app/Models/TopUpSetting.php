<?php

namespace App\Models;

use Database\Factories\TopUpSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $amount
 * @property int $credits
 * @property int $updated_by
 */
#[Fillable(['amount', 'credits', 'updated_by'])]

class TopUpSetting extends Model
{
    /** @use HasFactory<TopUpSettingFactory> */
    use HasFactory;

    public const int DEFAULT_AMOUNT = 110000;

    public const int DEFAULT_CREDITS = 4;

    protected $attributes = [
        'amount' => self::DEFAULT_AMOUNT,
        'credits' => self::DEFAULT_CREDITS,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'credits' => 'integer',
        ];
    }

    public static function current(): self
    {
        $setting = self::query()->first() ?? new self;
        $setting->credits = self::DEFAULT_CREDITS;

        return $setting;
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
