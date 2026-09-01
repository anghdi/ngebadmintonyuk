<?php

namespace App\Actions;

use App\Models\ShuttlecockItem;
use Illuminate\Validation\ValidationException;

class DeleteShuttlecockItemAction
{
    public function handle(ShuttlecockItem $shuttlecockItem): void
    {
        if ($shuttlecockItem->movements()->exists()) {
            throw ValidationException::withMessages([
                'shuttlecock' => 'Shuttlecock dengan riwayat stok tidak dapat dihapus. Nonaktifkan sebagai gantinya.',
            ]);
        }

        $shuttlecockItem->delete();
    }
}
