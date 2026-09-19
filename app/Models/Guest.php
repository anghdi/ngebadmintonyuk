<?php

namespace App\Models;

use Database\Factories\GuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $playing_level
 */
#[Fillable(['name', 'phone', 'playing_level'])]
class Guest extends Model
{
    /** @use HasFactory<GuestFactory> */
    use HasFactory;

    /** @return HasMany<SessionRegistration, $this> */
    public function registrations(): HasMany
    {
        return $this->hasMany(SessionRegistration::class);
    }
}
