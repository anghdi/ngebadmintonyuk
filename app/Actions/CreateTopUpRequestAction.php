<?php

namespace App\Actions;

use App\Models\Membership;
use App\Models\TopUpRequest;
use App\Models\TopUpSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CreateTopUpRequestAction
{
    public function handle(User $member, ?Membership $membership, int $amount, string $bank, UploadedFile $proof): TopUpRequest
    {
        $proofPath = $proof->store('top-up-proofs/'.$member->id, 'local');

        if (! $proofPath) {
            throw new RuntimeException('Bukti transfer gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($member, $membership, $amount, $bank, $proofPath): TopUpRequest {
                $lockedMember = User::query()->lockForUpdate()->findOrFail($member->id);
                $lockedMembership = $membership
                    ? Membership::query()->lockForUpdate()->findOrFail($membership->id)
                    : $this->communityMembership($lockedMember);

                if ($lockedMembership->user_id !== $member->id || $lockedMembership->status !== 'active') {
                    throw ValidationException::withMessages(['membership_id' => 'Paket tidak tersedia untuk top up.']);
                }

                if ($lockedMembership->topUpRequests()->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages(['membership_id' => 'Paket ini masih memiliki pengajuan yang menunggu verifikasi.']);
                }

                return TopUpRequest::create([
                    'user_id' => $member->id,
                    'membership_id' => $lockedMembership->id,
                    'amount' => $amount,
                    'credits' => TopUpSetting::DEFAULT_CREDITS,
                    'bank' => $bank,
                    'proof_path' => $proofPath,
                ]);
            }, attempts: 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($proofPath);

            throw $exception;
        }
    }

    private function communityMembership(User $member): Membership
    {
        $membership = Membership::query()
            ->whereBelongsTo($member)
            ->where('venue_name', Membership::COMMUNITY_VENUE)
            ->where('court_name', Membership::COMMUNITY_COURT)
            ->where('price_per_session', Membership::COMMUNITY_PRICE)
            ->lockForUpdate()
            ->first();

        if ($membership) {
            if ($membership->status !== 'active') {
                $membership->update(['status' => 'active']);
            }

            return $membership;
        }

        return Membership::create([
            'user_id' => $member->id,
            'venue_name' => Membership::COMMUNITY_VENUE,
            'court_name' => Membership::COMMUNITY_COURT,
            'price_per_session' => Membership::COMMUNITY_PRICE,
            'initial_credits' => TopUpSetting::DEFAULT_CREDITS,
            'starts_on' => today(),
            'status' => 'active',
            'notes' => 'Paket otomatis untuk top up kuota.',
            'created_by' => $member->id,
        ]);
    }
}
