<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Income;
use App\Models\SessionRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordSessionRegistrationPaymentAction
{
    public function handle(SessionRegistration $registration, string $paymentMethod, bool $isPaid, User $administrator): SessionRegistration
    {
        return DB::transaction(function () use ($registration, $paymentMethod, $isPaid, $administrator): SessionRegistration {
            $lockedRegistration = SessionRegistration::query()
                ->with(['income', 'playSession'])
                ->lockForUpdate()
                ->findOrFail($registration->id);

            $position = SessionRegistration::query()
                ->where('play_session_id', $lockedRegistration->play_session_id)
                ->where('id', '<=', $lockedRegistration->id)
                ->count();

            if ($isPaid && $position > $lockedRegistration->playSession->max_players) {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran waiting list dapat dicatat setelah pemain masuk slot utama.',
                ]);
            }

            if (! $isPaid) {
                $income = $lockedRegistration->income;
                $lockedRegistration->update([
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'unpaid',
                    'income_id' => null,
                    'checked_by' => $administrator->id,
                    'checked_at' => now(),
                ]);
                $income?->delete();

                return $lockedRegistration->refresh();
            }

            $category = Category::query()->firstOrCreate([
                'name' => 'Iuran Lapangan',
                'type' => 'income',
            ]);
            $income = $lockedRegistration->income ?? Income::query()->create([
                'user_id' => $administrator->id,
                'category_id' => $category->id,
                'date' => $lockedRegistration->playSession->scheduled_at->toDateString(),
                'description' => 'Iuran lapangan '.$lockedRegistration->playSession->venue_name,
            ]);

            $income->update([
                'category_id' => $category->id,
                'date' => $lockedRegistration->playSession->scheduled_at->toDateString(),
                'description' => 'Iuran lapangan '.$lockedRegistration->playSession->venue_name,
            ]);
            $income->details()->delete();
            $income->details()->create([
                'name' => $lockedRegistration->name,
                'amount' => $lockedRegistration->playSession->price_per_session,
                'note' => $paymentMethod === 'transfer' ? 'Transfer' : 'Tunai',
            ]);
            $lockedRegistration->update([
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'income_id' => $income->id,
                'checked_by' => $administrator->id,
                'checked_at' => now(),
            ]);

            return $lockedRegistration->refresh();
        }, attempts: 3);
    }
}
