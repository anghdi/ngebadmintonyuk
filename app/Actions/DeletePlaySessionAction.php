<?php

namespace App\Actions;

use App\Models\PlaySession;
use Illuminate\Support\Facades\DB;

class DeletePlaySessionAction
{
    public function handle(PlaySession $playSession): void
    {
        DB::transaction(function () use ($playSession): void {
            PlaySession::query()->lockForUpdate()->findOrFail($playSession->id)->delete();
        }, attempts: 3);
    }
}
