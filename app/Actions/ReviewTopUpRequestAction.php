<?php

namespace App\Actions;

use App\Models\MembershipTransaction;
use App\Models\TopUpRequest;
use App\Models\TopUpSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewTopUpRequestAction
{
    /** @param array{status: string, review_notes?: string|null} $data */
    public function handle(TopUpRequest $topUpRequest, array $data, User $administrator): TopUpRequest
    {
        return DB::transaction(function () use ($topUpRequest, $data, $administrator): TopUpRequest {
            $request = TopUpRequest::query()->lockForUpdate()->findOrFail($topUpRequest->id);

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Pengajuan ini telah diverifikasi.']);
            }

            if ($data['status'] === 'approved') {
                MembershipTransaction::create([
                    'membership_id' => $request->membership_id,
                    'type' => 'credit',
                    'quantity' => TopUpSetting::DEFAULT_CREDITS,
                    'notes' => 'Top up kuota #'.$request->id,
                    'created_by' => $administrator->id,
                ]);
            }

            $request->update([
                'status' => $data['status'],
                'credits' => $data['status'] === 'approved' ? TopUpSetting::DEFAULT_CREDITS : $request->credits,
                'review_notes' => $data['review_notes'] ?? null,
                'reviewed_by' => $administrator->id,
                'reviewed_at' => now(),
            ]);

            return $request;
        }, attempts: 3);
    }
}
