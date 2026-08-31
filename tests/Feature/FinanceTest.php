<?php

use App\Models\Category;
use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot access financial pages', function () {
    $this->get('/dashboard')->assertRedirect('/login');
    $this->get('/incomes')->assertRedirect('/login');
    $this->get('/reports')->assertRedirect('/login');
});

test('admin can create income with details and calculated total', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Iuran Main', 'type' => 'income']);

    $response = $this->actingAs($user)->post('/incomes', [
        'date' => '2026-08-14', 'category_id' => $category->id, 'description' => 'Main Jumat',
        'details' => [
            ['name' => 'Angga', 'amount' => 50000, 'note' => null],
            ['name' => 'Budi', 'amount' => 50000, 'note' => null],
        ],
    ]);

    $income = Income::with('details')->firstOrFail();
    $response->assertRedirect(route('incomes.show', $income));
    expect($income->details)->toHaveCount(2)
        ->and($income->details->sum('amount'))->toBe(100000);
});

test('income rejects an expense category', function () {
    $user = User::factory()->create();
    $category = Category::create(['name' => 'Lapangan', 'type' => 'expense']);

    $this->actingAs($user)->post('/incomes', [
        'date' => '2026-08-14', 'category_id' => $category->id,
        'details' => [['name' => 'Angga', 'amount' => 50000]],
    ])->assertSessionHasErrors('category_id');
});
