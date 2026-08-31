<?php

use App\Models\ShuttlecockItem;
use App\Models\User;

test('administrator can create shuttlecock item and add stock', function () {
    $administrator = User::factory()->admin()->create();

    $this->actingAs($administrator)->post(route('inventory.items.store'), [
        'name' => 'Aerosensa 30',
        'brand' => 'Yonex',
        'pieces_per_tube' => 12,
        'minimum_stock' => 12,
    ])->assertRedirect();

    $item = ShuttlecockItem::firstOrFail();

    $this->actingAs($administrator)->post(route('inventory.movements.store'), [
        'shuttlecock_item_id' => $item->id,
        'type' => 'purchase',
        'quantity' => 24,
        'unit_cost' => 10000,
        'notes' => 'Dua tabung',
    ])->assertRedirect();

    expect($item->movements()->sum('quantity'))->toBe(24);
});

test('stock usage cannot make shuttlecock balance negative', function () {
    $administrator = User::factory()->admin()->create();
    $item = ShuttlecockItem::factory()->create(['created_by' => $administrator->id]);
    $item->movements()->create([
        'type' => 'purchase',
        'quantity' => 3,
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)->post(route('inventory.movements.store'), [
        'shuttlecock_item_id' => $item->id,
        'type' => 'usage',
        'quantity' => 4,
    ])->assertSessionHasErrors('quantity');

    expect($item->movements()->sum('quantity'))->toBe(3);
});
