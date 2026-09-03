<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function save(string $type, array $data, ?Model $transaction = null): Model
    {
        $this->ensureManualTransaction($transaction);

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
        $this->ensureManualTransaction($transaction);

        DB::transaction(fn () => $transaction->delete());
    }

    private function ensureManualTransaction(?Model $transaction): void
    {
        if ($transaction instanceof Income && $transaction->sessionRegistration()->exists()) {
            throw ValidationException::withMessages([
                'transaction' => 'Pemasukan iuran lapangan dikelola dari daftar pemain.',
            ]);
        }
    }
}
