<?php

namespace App\Actions;

use App\Models\FeedDesign;
use App\Services\FeedLayoutStateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UpdateFeedDesignAction
{
    public function __construct(private CreateFeedDesignAction $creator, private FeedLayoutStateService $layoutState) {}

    /** @param array<string, mixed> $data */
    public function handle(FeedDesign $feedDesign, array $data, ?UploadedFile $photo): void
    {
        abort_if($feedDesign->published_at !== null, 422, 'Batch yang sudah dipublikasikan tidak dapat diubah.');
        $oldPhoto = $feedDesign->photo_path;
        $newPhoto = $photo?->storeAs('feed-studio/source', Str::uuid().'.'.$photo->extension(), 'local');
        $oldAssetPaths = array_filter([$feedDesign->thumbnail_path, ...($feedDesign->exported_assets ?? [])]);
        $currentSettings = $feedDesign->layout_settings ?? [];
        $settings = $this->creator->settings([
            ...$data,
            'zoom' => $data['zoom'] ?? $currentSettings['zoom'] ?? 1,
            'position_x' => $data['position_x'] ?? $currentSettings['x'] ?? 0,
            'position_y' => $data['position_y'] ?? $currentSettings['y'] ?? 0,
        ]);

        try {
            DB::transaction(function () use ($feedDesign, $data, $newPhoto, $oldPhoto, $settings, &$oldAssetPaths): void {
                $feedDesign->update([
                    ...Arr::except($data, ['photo', 'zoom', 'position_x', 'position_y']),
                    'photo_path' => $newPhoto ?? $oldPhoto,
                    'layout_settings' => $settings,
                    'connection_state' => $this->layoutState->next(
                        FeedDesign::query()->where('row_number', '<', $feedDesign->row_number)->latest('row_number')->first(),
                        $data['layout_variant'],
                        $newPhoto !== null || $oldPhoto !== null,
                        match ($data['format']) {
                            'connected_2' => 2, 'connected_3' => 3, default => 1
                        },
                    ),
                    'exported_assets' => null,
                    'thumbnail_path' => null,
                    'exported_at' => null,
                ]);

                $oldAssetPaths = [
                    ...$oldAssetPaths,
                    ...$this->layoutState->synchronizeDescendants($feedDesign),
                ];
            });
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('local')->delete($newPhoto);
            }
            throw $exception;
        }

        if ($newPhoto !== null && $oldPhoto !== null) {
            Storage::disk('local')->delete($oldPhoto);
        }

        Storage::disk('local')->delete(array_values(array_unique($oldAssetPaths)));
    }
}
