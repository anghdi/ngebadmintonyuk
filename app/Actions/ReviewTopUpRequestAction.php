<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\Income;
use App\Models\Membership;
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
            User::query()->lockForUpdate()->findOrFail($topUpRequest->user_id);
            $request = TopUpRequest::query()->lockForUpdate()->findOrFail($topUpRequest->id);

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Pengajuan ini telah diverifikasi.']);
            }

            if ($data['status'] === 'approved') {
                $membership = Membership::query()->lockForUpdate()->findOrFail($request->membership_id);

                if ($membership->user_id !== $request->user_id || $membership->status !== 'active') {
                    throw ValidationException::withMessages(['status' => 'Paket harus aktif dan dimiliki pemohon sebelum top up disetujui.']);
                }

                $income = Income::query()->create([
                    'user_id' => $administrator->id,
                    'category_id' => Category::query()->firstOrCreate(['name' => 'Top Up Kuota', 'type' => 'income'])->id,
                    'date' => today()->toDateString(),
                    'description' => 'Persetujuan top up kuota #'.$request->id,
                ]);
                $income->details()->create([
                    'name' => $request->member->name,
                    'amount' => $request->amount,
                    'note' => 'Transfer '.strtoupper($request->bank).' · 4 kuota',
                ]);
                $request->income_id = $income->id;

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
