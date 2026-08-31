<?php

namespace App\Actions;

use App\Models\ShuttlecockItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordStockMovementAction
{
    /** @param array<string, mixed> $data */
    public function handle(array $data, User $administrator): StockMovement
    {
        return DB::transaction(function () use ($data, $administrator): StockMovement {
            $item = ShuttlecockItem::query()->lockForUpdate()->findOrFail((int) $data['shuttlecock_item_id']);
            $quantity = $data['type'] === 'usage' ? -$data['quantity'] : $data['quantity'];
            $currentStock = (int) $item->movements()->sum('quantity');

            if ($currentStock + $quantity < 0) {
                throw ValidationException::withMessages(['quantity' => 'Stok shuttlecock tidak mencukupi.']);
            }

            return StockMovement::create(array_merge($data, [
                'quantity' => $quantity,
                'created_by' => $administrator->id,
            ]));
        }, attempts: 3);
    }
}
