<?php

namespace App\Services;

use App\Models\FeedDesign;

class FeedLayoutStateService
{
    /** @return array<string, mixed> */
    public function next(?FeedDesign $previous, string $variant, bool $hasPhoto, int $postCount): array
    {
        $previousState = $previous?->connection_state ?? [];
        $backgrounds = ['off-white', 'royal-blue', 'navy'];
        $previousBackground = $previousState['background'] ?? 'off-white';
        $backgroundIndex = array_search($previousBackground, $backgrounds, true);
        $background = $backgrounds[((int) $backgroundIndex + 1) % count($backgrounds)];

        return [
            'background' => $background,
            'accent' => 'yellow',
            'court_line_exit' => [
                ['x' => $variant === 'kinetic' ? 0.72 : 0.34, 'edge' => 'top', 'direction' => $variant === 'sideline' ? 'vertical' : 'diagonal'],
            ],
            'density' => $hasPhoto ? 'medium' : 'low',
            'alignment' => $variant,
            'photo_presence' => $hasPhoto,
            'connection_mode' => $postCount === 1 ? 'rhythm' : 'continuous',
        ];
    }
}
