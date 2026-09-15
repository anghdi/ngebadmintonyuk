<?php

namespace App\Actions;

use App\Models\FeedDesign;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SaveFeedDesignAssetsAction
{
    /** @param list<UploadedFile> $assets */
    /** @param array{layout_variant: string, zoom: float|int|string, position_x: float|int|string, position_y: float|int|string} $settings */
    public function handle(FeedDesign $feedDesign, array $assets, UploadedFile $thumbnail, array $settings): void
    {
        $directory = 'feed-studio/exports/'.$feedDesign->id;
        $paths = collect($assets)->values()->map(fn (UploadedFile $asset, int $index): string => $asset->storeAs($directory, sprintf('%02d-upload-%s.png', $index + 1, ['first', 'second', 'last'][$index] ?? 'next'), 'local'))->all();
        $thumbnailPath = $thumbnail->storeAs($directory, 'thumbnail.png', 'local');
        Storage::disk('local')->delete(array_filter([$feedDesign->thumbnail_path, ...($feedDesign->exported_assets ?? [])]));
        $connectionState = $feedDesign->connection_state ?? [];
        $connectionState['alignment'] = $settings['layout_variant'];
        $feedDesign->update([
            'exported_assets' => $paths,
            'thumbnail_path' => $thumbnailPath,
            'layout_variant' => $settings['layout_variant'],
            'layout_settings' => ['zoom' => (float) $settings['zoom'], 'x' => (float) $settings['position_x'], 'y' => (float) $settings['position_y']],
            'connection_state' => $connectionState,
        ]);
    }
}
