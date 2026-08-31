<?php

namespace App\Http\Controllers;

use App\Actions\RecordStockMovementAction;
use App\Http\Requests\StoreStockMovementRequest;
use Illuminate\Http\RedirectResponse;

class StockMovementController extends Controller
{
    public function store(StoreStockMovementRequest $request, RecordStockMovementAction $recordStockMovement): RedirectResponse
    {
        $recordStockMovement->handle($request->validated(), $request->user());

        return back()->with('success', 'Mutasi stok shuttlecock berhasil dicatat.');
    }
}
