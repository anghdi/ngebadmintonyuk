<?php

namespace App\Models;

use Database\Factories\SessionRegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $play_session_id
 * @property int|null $user_id
 * @property string $name
 * @property string $phone
 * @property string $payment_method
 * @property string $payment_status
 * @property string $attendance_status
 * @property string|null $admin_notes
 * @property int|null $checked_by
 * @property Carbon|null $checked_at
 */
#[Fillable(['play_session_id', 'user_id', 'name', 'phone', 'payment_method', 'payment_status', 'attendance_status', 'admin_notes', 'checked_by', 'checked_at'])]
class SessionRegistration extends Model
{
    /** @use HasFactory<SessionRegistrationFactory> */
    use HasFactory;

    protected $attributes = [
        'payment_status' => 'unpaid',
        'attendance_status' => 'listed',
    ];

    protected function casts(): array
    {
        return ['checked_at' => 'datetime'];
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = Str::of($phone)->replaceMatches('/\D+/', '');

        if ($digits->startsWith('0')) {
            return '62'.$digits->after('0');
        }

        if ($digits->startsWith('8')) {
            return '62'.$digits;
        }

        return (string) $digits;
    }

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

    /** @return BelongsTo<User, $this> */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
