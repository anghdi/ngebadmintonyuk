<?php

namespace App\Http\Controllers;

use App\Actions\DeleteShuttlecockItemAction;
use App\Http\Requests\StoreShuttlecockItemRequest;
use App\Http\Requests\UpdateShuttlecockItemRequest;
use App\Models\PlaySession;
use App\Models\ShuttlecockItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShuttlecockInventoryController extends Controller
{
    public function index(): View
    {
        $items = ShuttlecockItem::query()->withCount('movements')->withSum('movements as stock', 'quantity')->orderBy('name')->get();
        $movements = StockMovement::query()->with(['item', 'playSession', 'creator'])->latest()->paginate(15);
        $playSessions = PlaySession::query()->latest('scheduled_at')->limit(30)->get();

        return view('inventory.index', compact('items', 'movements', 'playSessions'));
    }

    public function store(StoreShuttlecockItemRequest $request): RedirectResponse
    {
        ShuttlecockItem::create($request->validated() + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Jenis shuttlecock berhasil ditambahkan.');
    }

    public function update(UpdateShuttlecockItemRequest $request, ShuttlecockItem $shuttlecockItem): RedirectResponse
    {
        $shuttlecockItem->update($request->validated());

        return back()->with('success', 'Data shuttlecock berhasil diperbarui.');
    }

    public function destroy(ShuttlecockItem $shuttlecockItem, DeleteShuttlecockItemAction $deleteShuttlecockItem): RedirectResponse
    {
        $deleteShuttlecockItem->handle($shuttlecockItem);

        return back()->with('success', 'Jenis shuttlecock berhasil dihapus.');
    }
}
