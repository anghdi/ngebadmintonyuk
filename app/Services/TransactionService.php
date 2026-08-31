<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function save(string $type, array $data, ?Model $transaction = null): Model
    {
        return DB::transaction(function () use ($type, $data, $transaction) {
            $class = $type === 'income' ? Income::class : Expense::class;
            $transaction ??= new $class;
            $transaction->fill([
                'user_id' => auth()->id(), 'category_id' => $data['category_id'],
                'date' => $data['date'], 'description' => $data['description'] ?? null,
            ])->save();
            $transaction->details()->delete();
            $transaction->details()->createMany($data['details']);

            return $transaction->load('category', 'details');
        });
    }

    public function delete(Model $transaction): void
    {
        DB::transaction(fn () => $transaction->delete());
    }
}
