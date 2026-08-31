<?php

namespace App\Services;

use App\Repositories\ReportRepository;

class ReportService
{
    public function __construct(private ReportRepository $reports) {}

    public function make(string $start, string $end): array
    {
        $income = $this->reports->totalBetween('income', $start, $end);
        $expense = $this->reports->totalBetween('expense', $start, $end);

        return [
            'totalIncome' => $income, 'totalExpense' => $expense, 'difference' => $income - $expense,
            'balance' => $this->reports->totalUntil('income', $end) - $this->reports->totalUntil('expense', $end),
            'incomes' => $this->reports->between('income', $start, $end),
            'expenses' => $this->reports->between('expense', $start, $end),
            'incomeByCategory' => $this->reports->byCategory('income', $start, $end),
            'expenseByCategory' => $this->reports->byCategory('expense', $start, $end),
        ];
    }
}
