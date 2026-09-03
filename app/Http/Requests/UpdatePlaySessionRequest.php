<?php

namespace App\Http\Requests;

use App\Models\PlaySession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePlaySessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'scheduled_at' => ['required', 'date'],
            'venue_name' => ['required', 'string', 'max:255'],
            'court_name' => ['required', 'string', 'max:255'],
            'price_per_session' => ['required', 'integer', 'min:0'],
            'max_players' => ['required', 'integer', 'min:1', 'max:200'],
            'max_waiting_players' => ['required', 'integer', 'min:0', 'max:200'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $playSession = $this->route('play_session');

                if (! $playSession instanceof PlaySession) {
                    return;
                }

                $registrationCount = $playSession->registrations()->count();
                $confirmedCount = min($registrationCount, $playSession->max_players);
                $proposedCapacity = $this->integer('max_players') + $this->integer('max_waiting_players');

                if ($confirmedCount > $this->integer('max_players')) {
                    $validator->errors()->add('max_players', 'Slot utama tidak boleh menggeser pemain yang sudah terdaftar ke waiting list.');
                }

                if ($registrationCount > $proposedCapacity) {
                    $validator->errors()->add('max_waiting_players', 'Total slot utama dan waiting list tidak boleh kurang dari jumlah pendaftar.');
                }

                $hasRecordedActivity = $playSession->attendances()->exists()
                    || $playSession->registrations()->where('payment_status', 'paid')->exists();

                if (! $hasRecordedActivity) {
                    return;
                }

                $coreDataChanged = $playSession->scheduled_at->format('Y-m-d\TH:i') !== $this->input('scheduled_at')
                    || $playSession->venue_name !== $this->input('venue_name')
                    || $playSession->court_name !== $this->input('court_name')
                    || $playSession->price_per_session !== $this->integer('price_per_session');

                if ($coreDataChanged) {
                    $validator->errors()->add('scheduled_at', 'Jadwal, lapangan, dan harga tidak dapat diubah setelah pembayaran atau absensi tercatat.');
                }
            },
        ];
    }
}
