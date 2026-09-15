<?php

namespace App\Actions;

use App\Models\FeedDesign;
use App\Services\FeedLayoutStateService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class UpdateFeedDesignAction
{
    public function __construct(private CreateFeedDesignAction $creator, private FeedLayoutStateService $layoutState) {}

    /** @param array<string, mixed> $data */
    public function handle(FeedDesign $feedDesign, array $data, ?UploadedFile $photo): void
    {
        $oldPhoto = $feedDesign->photo_path;
        $newPhoto = $photo?->storeAs('feed-studio/source', Str::uuid().'.'.$photo->extension(), 'local');

        try {
            $feedDesign->update([
                ...Arr::except($data, ['photo', 'zoom', 'position_x', 'position_y']),
                'photo_path' => $newPhoto ?? $oldPhoto,
                'layout_settings' => $this->creator->settings($data),
                'connection_state' => $this->layoutState->next(
                    FeedDesign::query()->where('row_number', '<', $feedDesign->row_number)->latest('row_number')->first(),
                    $data['layout_variant'],
                    $newPhoto !== null || $oldPhoto !== null,
                    match ($data['format']) {
                        'connected_2' => 2, 'connected_3' => 3, default => 1
                    },
                ),
            ]);
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('local')->delete($newPhoto);
            }
            throw $exception;
        }

        if ($newPhoto !== null && $oldPhoto !== null) {
            Storage::disk('local')->delete($oldPhoto);
        }
    }
}
