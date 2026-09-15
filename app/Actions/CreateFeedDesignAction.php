<?php

namespace App\Actions;

use App\Models\FeedDesign;
use App\Models\User;
use App\Services\FeedLayoutStateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CreateFeedDesignAction
{
    public function __construct(private FeedLayoutStateService $layoutState) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data, User $creator, ?UploadedFile $photo): FeedDesign
    {
        return DB::transaction(function () use ($data, $creator, $photo): FeedDesign {
            $previous = FeedDesign::query()->latest('row_number')->lockForUpdate()->first();
            $rowNumber = ($previous?->row_number ?? -1) + 1;
            $photoPath = $photo?->storeAs('feed-studio/source', Str::uuid().'.'.$photo->extension(), 'local');
            $postCount = match ($data['format']) {
                'connected_2' => 2,
                'connected_3' => 3,
                default => 1,
            };

            try {
                return FeedDesign::query()->create([
                    ...Arr::except($data, ['photo', 'zoom', 'position_x', 'position_y']),
                    'created_by' => $creator->id,
                    'photo_path' => $photoPath,
                    'row_number' => $rowNumber,
                    'grid_position' => FeedDesign::query()->sum(DB::raw("CASE WHEN format = 'connected_3' THEN 3 WHEN format = 'connected_2' THEN 2 ELSE 1 END")),
                    'layout_settings' => $this->settings($data),
                    'connection_state' => $this->layoutState->next($previous, $data['layout_variant'], $photo !== null, $postCount),
                ]);
            } catch (Throwable $exception) {
                if ($photoPath !== null) {
                    Storage::disk('local')->delete($photoPath);
                }

                throw $exception;
            }
        });
    }

    public function ensureSeed(User $creator): void
    {
        $seedState = [
            'visual_engine_version' => 2,
            'background' => 'off-white',
            'accent' => 'yellow',
            'density' => 'low',
            'alignment' => 'editorial',
            'photo_presence' => false,
            'connection_mode' => 'continuous',
            'incoming_connection' => [],
            'court_line_exit' => [['x' => 0.78, 'edge' => 'top', 'direction' => 'diagonal-right']],
        ];
        $seed = FeedDesign::query()->where('is_seed', true)->first();

        if ($seed !== null) {
            $seed->update([
                'format' => 'connected_3',
                'content_type' => 'hero',
                'headline' => 'NgeBadminton YUK!',
                'supporting_text' => 'Lagi nyari temen main? · Ramean lebih seru.',
                'connection_state' => $seedState,
            ]);

            return;
        }

        FeedDesign::query()->firstOrCreate(['row_number' => 0], [
            'created_by' => $creator->id,
            'format' => 'connected_3',
            'content_type' => 'hero',
            'headline' => 'NgeBadminton YUK!',
            'supporting_text' => 'Lagi nyari temen main? · Ramean lebih seru.',
            'layout_variant' => 'editorial',
            'grid_position' => 0,
            'is_seed' => true,
            'layout_settings' => ['zoom' => 1, 'x' => 0, 'y' => 0],
            'connection_state' => $seedState,
        ]);
    }

    /** @param array<string, mixed> $data
     * @return array{zoom: float, x: float, y: float}
     */
    public function settings(array $data): array
    {
        return ['zoom' => (float) ($data['zoom'] ?? 1), 'x' => (float) ($data['position_x'] ?? 0), 'y' => (float) ($data['position_y'] ?? 0)];
    }
}
