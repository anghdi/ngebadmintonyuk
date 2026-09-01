<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTopUpSettingRequest;
use App\Models\TopUpSetting;
use Illuminate\Http\RedirectResponse;

class TopUpSettingController extends Controller
{
    public function update(UpdateTopUpSettingRequest $request): RedirectResponse
    {
        TopUpSetting::query()->updateOrCreate(
            ['id' => 1],
            $request->validated() + [
                'credits' => TopUpSetting::DEFAULT_CREDITS,
                'updated_by' => $request->user()->id,
            ],
        );

        return back()->with('success', 'Paket top up berhasil diperbarui.');
    }
}
