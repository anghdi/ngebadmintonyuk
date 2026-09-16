<?php

namespace App\Actions;

use App\Models\FeedDesign;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SaveFeedDesignAssetsAction
{
    /** @param list<UploadedFile> $assets */
    /** @param array{layout_variant: string, zoom: float|int|string, position_x: float|int|string, position_y: float|int|string} $settings */
    public function handle(FeedDesign $feedDesign, array $assets, UploadedFile $thumbnail, array $settings): void
    {
        abort_if($feedDesign->published_at !== null, 422, 'Batch yang sudah dipublikasikan tidak dapat diekspor ulang.');
        $directory = 'feed-studio/exports/'.$feedDesign->id.'/'.Str::uuid();
        $newPaths = [];
        $oldPaths = array_filter([$feedDesign->thumbnail_path, ...($feedDesign->exported_assets ?? [])]);

        try {
            $paths = collect($assets)->values()->map(function (UploadedFile $asset, int $index) use ($directory, &$newPaths): string {
                $path = $asset->storeAs($directory, sprintf('%02d-upload-%s.png', $index + 1, ['first', 'second', 'last'][$index] ?? 'next'), 'local');
                $newPaths[] = $path;

                return $path;
            })->all();

            $thumbnailPath = $thumbnail->storeAs($directory, 'thumbnail.png', 'local');
            $newPaths[] = $thumbnailPath;
            $connectionState = $feedDesign->connection_state ?? [];
            $connectionState['alignment'] = $settings['layout_variant'];

            $feedDesign->update([
                'exported_assets' => $paths,
                'thumbnail_path' => $thumbnailPath,
                'layout_variant' => $settings['layout_variant'],
                'layout_settings' => ['zoom' => (float) $settings['zoom'], 'x' => (float) $settings['position_x'], 'y' => (float) $settings['position_y']],
                'connection_state' => $connectionState,
                'exported_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPaths);

            throw $exception;
        }

        Storage::disk('local')->delete($oldPaths);
    }
}
