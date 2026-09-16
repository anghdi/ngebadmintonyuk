<?php

namespace App\Actions;

use App\Models\FeedDesign;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishFeedDesignAction
{
    public function handle(FeedDesign $feedDesign): void
    {
        DB::transaction(function () use ($feedDesign): void {
            $design = FeedDesign::query()->with('connectedFrom')->lockForUpdate()->findOrFail($feedDesign->id);

            if ($design->exported_at === null || empty($design->exported_assets)) {
                throw ValidationException::withMessages(['publish' => 'Export batch terlebih dahulu sebelum menandainya sebagai dipublikasikan.']);
            }

            $parent = $design->connectedFrom;
            if ($parent !== null && ! $parent->is_seed && $parent->published_at === null) {
                throw ValidationException::withMessages(['publish' => 'Publikasikan parent Row '.$parent->row_number.' terlebih dahulu.']);
            }

            $design->update(['published_at' => $design->published_at ?? now()]);
        });
    }
}
