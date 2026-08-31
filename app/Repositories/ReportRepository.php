<?php

namespace App\Repositories;

use App\Models\Expense;
use App\Models\Income;
use Illuminate\Database\Eloquent\Builder;

class ReportRepository
{
    private function query(string $type): Builder
    {
        return ($type === 'income' ? Income::query() : Expense::query())
            ->with(['category', 'details'])->withSum('details', 'amount');
    }

    public function between(string $type, string $start, string $end)
    {
        return $this->query($type)->whereBetween('date', [$start, $end])->latest('date')->latest('id')->get();
    }

    public function totalBetween(string $type, string $start, string $end): int
    {
        return (int) $this->query($type)->whereBetween('date', [$start, $end])->get()->sum('details_sum_amount');
    }

    public function totalUntil(string $type, string $end): int
    {
        return (int) $this->query($type)->whereDate('date', '<=', $end)->get()->sum('details_sum_amount');
    }

    public function byCategory(string $type, string $start, string $end)
    {
        return $this->between($type, $start, $end)->groupBy('category.name')
            ->map(fn ($items) => $items->sum('details_sum_amount'))->sortDesc();
    }
}
