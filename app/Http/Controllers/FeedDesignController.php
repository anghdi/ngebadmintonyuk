<?php

namespace App\Http\Controllers;

use App\Actions\CreateFeedDesignAction;
use App\Actions\DeleteFeedDesignAction;
use App\Actions\SaveFeedDesignAssetsAction;
use App\Actions\UpdateFeedDesignAction;
use App\Http\Requests\SaveFeedDesignAssetsRequest;
use App\Http\Requests\StoreFeedDesignRequest;
use App\Http\Requests\UpdateFeedDesignRequest;
use App\Models\FeedDesign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FeedDesignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CreateFeedDesignAction $create): View
    {
        $create->ensureSeed(request()->user());
        $seed = FeedDesign::query()->where('is_seed', true)->sole();
        $designs = FeedDesign::query()->with('creator')->where('is_seed', false)->latest('row_number')->paginate(12);

        return view('feed-studio.index', compact('designs', 'seed'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(CreateFeedDesignAction $create): View
    {
        $create->ensureSeed(request()->user());

        return view('feed-studio.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeedDesignRequest $request, CreateFeedDesignAction $create): RedirectResponse
    {
        $create->ensureSeed($request->user());
        $design = $create->handle($request->validated(), $request->user(), $request->file('photo'));

        return redirect()->route('feed-studio.edit', $design)->with('success', 'Konten dibuat. Atur komposisi sebelum export.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FeedDesign $feedDesign): View
    {
        $history = FeedDesign::query()
            ->whereKeyNot($feedDesign->id)
            ->where(fn ($query) => $query->whereNotNull('thumbnail_path')->orWhere('is_seed', true))
            ->latest('row_number')
            ->limit(9)
            ->get();

        return view('feed-studio.export', compact('feedDesign', 'history'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeedDesign $feedDesign): View
    {
        return view('feed-studio.edit', compact('feedDesign'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeedDesignRequest $request, FeedDesign $feedDesign, UpdateFeedDesignAction $update): RedirectResponse
    {
        abort_if($feedDesign->is_seed, 422);
        $update->handle($feedDesign, $request->validated(), $request->file('photo'));

        return redirect()->route('feed-studio.edit', $feedDesign)->with('success', 'Perubahan disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeedDesign $feedDesign, DeleteFeedDesignAction $delete): RedirectResponse
    {
        $delete->handle($feedDesign);

        return redirect()->route('feed-studio.index')->with('success', 'Desain dihapus.');
    }

    public function photo(FeedDesign $feedDesign): Response
    {
        abort_if($feedDesign->photo_path === null || ! Storage::disk('local')->exists($feedDesign->photo_path), 404);

        return response(Storage::disk('local')->get($feedDesign->photo_path), 200, [
            'Content-Type' => Storage::disk('local')->mimeType($feedDesign->photo_path) ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function thumbnail(FeedDesign $feedDesign): Response
    {
        abort_if($feedDesign->thumbnail_path === null || ! Storage::disk('local')->exists($feedDesign->thumbnail_path), 404);

        return response(Storage::disk('local')->get($feedDesign->thumbnail_path), 200, ['Content-Type' => 'image/png', 'Cache-Control' => 'private, max-age=3600']);
    }

    public function saveAssets(SaveFeedDesignAssetsRequest $request, FeedDesign $feedDesign, SaveFeedDesignAssetsAction $save): Response
    {
        abort_if(count($request->file('assets')) !== $feedDesign->postCount(), 422);
        $save->handle($feedDesign, $request->file('assets'), $request->file('thumbnail'), $request->validated());

        return response()->noContent();
    }
}
