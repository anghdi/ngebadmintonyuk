<?php

namespace App\Actions;

use App\Models\TopUpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteTopUpRequestAction
{
    public function handle(TopUpRequest $topUpRequest): void
    {
        $proofPath = DB::transaction(function () use ($topUpRequest): string {
            $lockedTopUpRequest = TopUpRequest::query()->lockForUpdate()->findOrFail($topUpRequest->id);

            if ($lockedTopUpRequest->status === 'approved' || $lockedTopUpRequest->income_id !== null) {
                throw ValidationException::withMessages([
                    'top_up' => 'Top up yang sudah disetujui tidak dapat dihapus karena terhubung ke kas dan riwayat kuota.',
                ]);
            }

            $proofPath = $lockedTopUpRequest->proof_path;
            $lockedTopUpRequest->delete();

            return $proofPath;
        }, attempts: 3);

        Storage::disk('local')->delete($proofPath);
    }
}
