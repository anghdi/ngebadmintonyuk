<?php

namespace App\Actions;

use App\Models\FeedDesign;
use App\Services\FeedLayoutStateService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteFeedDesignAction
{
    public function __construct(private FeedLayoutStateService $layoutState) {}

    public function handle(FeedDesign $feedDesign): void
    {
        abort_if($feedDesign->is_seed, 422, 'Seed Row tidak dapat dihapus.');
        abort_if($feedDesign->published_at !== null, 422, 'Batch yang sudah dipublikasikan tidak dapat dihapus.');
        $paths = array_filter([$feedDesign->photo_path, $feedDesign->thumbnail_path, ...($feedDesign->exported_assets ?? [])]);

        DB::transaction(function () use ($feedDesign, &$paths): void {
            $design = FeedDesign::query()->with('connectedFrom')->lockForUpdate()->findOrFail($feedDesign->id);
            $paths = [
                ...$paths,
                ...$this->layoutState->reconnectChildren($design, $design->connectedFrom),
            ];

            $design->delete();
        });

        Storage::disk('local')->delete(array_values(array_unique($paths)));
    }
}
