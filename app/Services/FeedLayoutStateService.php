<?php

namespace App\Services;

use App\Models\FeedDesign;

class FeedLayoutStateService
{
    /** @return array<string, mixed> */
    public function next(?FeedDesign $previous, string $variant, bool $hasPhoto, int $postCount): array
    {
        $previousState = $previous?->connection_state ?? [];
        $previousExit = $previousState['court_line_exit'][0] ?? null;
        $backgrounds = ['off-white', 'royal-blue', 'navy'];
        $previousBackground = $previousState['background'] ?? 'off-white';
        $backgroundIndex = array_search($previousBackground, $backgrounds, true);
        $background = $backgrounds[((int) $backgroundIndex + 1) % count($backgrounds)];
        $exit = match ($variant) {
            'kinetic' => ['x' => 0.68, 'edge' => 'top', 'direction' => 'diagonal-right'],
            'sideline' => ['x' => 0.84, 'edge' => 'top', 'direction' => 'vertical'],
            default => ['x' => 0.26, 'edge' => 'top', 'direction' => 'diagonal-left'],
        };

        return [
            'visual_engine_version' => 2,
            'background' => $background,
            'accent' => 'yellow',
            'incoming_connection' => $previousExit === null ? [] : [[
                'x' => (float) $previousExit['x'],
                'edge' => 'bottom',
                'direction' => $previousExit['direction'],
                'source_row' => $previous?->row_number,
            ]],
            'court_line_exit' => [$exit],
            'density' => $hasPhoto ? 'medium' : 'low',
            'alignment' => $variant,
            'photo_presence' => $hasPhoto,
            'connection_mode' => $postCount === 1 ? 'rhythm' : 'continuous',
        ];
    }

    /** @return list<string> */
    public function synchronizeFollowingRows(FeedDesign $previous): array
    {
        $invalidatedAssetPaths = [];
        $gridPosition = $previous->grid_position + $previous->postCount();

        $followingRows = FeedDesign::query()
            ->where('row_number', '>', $previous->row_number)
            ->where('is_seed', false)
            ->oldest('row_number')
            ->get();

        foreach ($followingRows as $followingRow) {
            $connectionState = $this->next(
                $previous,
                $followingRow->layout_variant,
                $followingRow->photo_path !== null,
                $followingRow->postCount(),
            );
            $connectionChanged = $followingRow->connection_state !== $connectionState;
            $updates = [
                'grid_position' => $gridPosition,
                'connection_state' => $connectionState,
            ];

            if ($connectionChanged) {
                $invalidatedAssetPaths = [
                    ...$invalidatedAssetPaths,
                    ...array_filter([
                        $followingRow->thumbnail_path,
                        ...($followingRow->exported_assets ?? []),
                    ]),
                ];
                $updates['exported_assets'] = null;
                $updates['thumbnail_path'] = null;
            }

            if ($followingRow->grid_position !== $gridPosition || $connectionChanged) {
                $followingRow->update($updates);
            }

            $gridPosition += $followingRow->postCount();
            $previous = $followingRow;
        }

        return array_values(array_unique($invalidatedAssetPaths));
    }
}
