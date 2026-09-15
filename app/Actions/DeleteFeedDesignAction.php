<?php

namespace App\Actions;

use App\Models\FeedDesign;
use Illuminate\Support\Facades\Storage;

class DeleteFeedDesignAction
{
    public function handle(FeedDesign $feedDesign): void
    {
        abort_if($feedDesign->is_seed, 422, 'Seed Row tidak dapat dihapus.');
        Storage::disk('local')->delete(array_filter([$feedDesign->photo_path, $feedDesign->thumbnail_path, ...($feedDesign->exported_assets ?? [])]));
        $feedDesign->delete();
    }
}
