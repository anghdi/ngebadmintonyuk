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
    public function handle(User $member, Membership $membership, int $amount, string $bank, UploadedFile $proof): TopUpRequest
    {
        $proofPath = $proof->store('top-up-proofs/'.$member->id, 'local');

        if (! $proofPath) {
            throw new RuntimeException('Bukti transfer gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($member, $membership, $amount, $bank, $proofPath): TopUpRequest {
                $lockedMembership = Membership::query()->lockForUpdate()->findOrFail($membership->id);

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
}
