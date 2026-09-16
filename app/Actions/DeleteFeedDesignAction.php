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
        $paths = array_filter([$feedDesign->photo_path, $feedDesign->thumbnail_path, ...($feedDesign->exported_assets ?? [])]);
        $previous = FeedDesign::query()
            ->where('row_number', '<', $feedDesign->row_number)
            ->latest('row_number')
            ->first();

        DB::transaction(function () use ($feedDesign, $previous, &$paths): void {
            $feedDesign->delete();

            if ($previous !== null) {
                $paths = [
                    ...$paths,
                    ...$this->layoutState->synchronizeFollowingRows($previous),
                ];
            }
        });

        Storage::disk('local')->delete(array_values(array_unique($paths)));
    }
}
