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
    public function synchronizeDescendants(FeedDesign $parent): array
    {
        return array_values(array_unique($this->synchronizeChildren($parent, [])));
    }

    /** @return list<string> */
    public function reconnectChildren(FeedDesign $removed, ?FeedDesign $replacement): array
    {
        $invalidatedAssetPaths = [];
        $children = FeedDesign::query()
            ->where('connected_from_id', $removed->id)
            ->oldest('row_number')
            ->lockForUpdate()
            ->get();

        foreach ($children as $child) {
            $invalidatedAssetPaths = [
                ...$invalidatedAssetPaths,
                ...$this->synchronizeBranch($child, $replacement, []),
            ];
        }

        return array_values(array_unique($invalidatedAssetPaths));
    }

    /**
     * @param  list<int>  $visited
     * @return list<string>
     */
    private function synchronizeChildren(FeedDesign $parent, array $visited): array
    {
        if (in_array($parent->id, $visited, true)) {
            return [];
        }

        $visited[] = $parent->id;
        $invalidatedAssetPaths = [];
        $children = FeedDesign::query()
            ->where('connected_from_id', $parent->id)
            ->oldest('row_number')
            ->lockForUpdate()
            ->get();

        foreach ($children as $child) {
            $invalidatedAssetPaths = [
                ...$invalidatedAssetPaths,
                ...$this->synchronizeBranch($child, $parent, $visited),
            ];
        }

        return $invalidatedAssetPaths;
    }

    /**
     * @param  list<int>  $visited
     * @return list<string>
     */
    private function synchronizeBranch(FeedDesign $design, ?FeedDesign $parent, array $visited): array
    {
        if (in_array($design->id, $visited, true)) {
            return [];
        }

        $gridPosition = ($parent?->grid_position ?? 0) + ($parent?->postCount() ?? 0);
        $connectionState = $this->next(
            $parent,
            $design->layout_variant,
            $design->photo_path !== null,
            $design->postCount(),
        );
        $connectionChanged = $design->connection_state !== $connectionState;
        $parentChanged = $design->connected_from_id !== $parent?->id;
        $invalidatedAssetPaths = [];
        $updates = [
            'connected_from_id' => $parent?->id,
            'grid_position' => $gridPosition,
            'connection_state' => $connectionState,
        ];

        if ($connectionChanged || $parentChanged) {
            $invalidatedAssetPaths = array_filter([
                $design->thumbnail_path,
                ...($design->exported_assets ?? []),
            ]);
            $updates = [
                ...$updates,
                'exported_assets' => null,
                'thumbnail_path' => null,
                'exported_at' => null,
            ];
        }

        if ($design->grid_position !== $gridPosition || $connectionChanged || $parentChanged) {
            $design->update($updates);
        }

        return [
            ...$invalidatedAssetPaths,
            ...$this->synchronizeChildren($design, $visited),
        ];
    }
}
