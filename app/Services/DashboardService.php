<?php
namespace App\Services;

use Exception;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;

class DashboardService {

    const WIDGET_LAST_12_MONTHS_MONTLY_BALANCE = 'last-12-months-monthly-balance';

    public function loadWidget(string $name) : array
    {
        switch ($name) {
            case self::WIDGET_LAST_12_MONTHS_MONTLY_BALANCE:
                return $this->loadLast12MonthsMonthlyBalance();
            default:
                throw new Exception('Trying to load unexpected widget: "' . $name) . '".';
        }
    }

    private function loadLast12MonthsMonthlyBalance() : array
    {
        $investmentsCategory = ExpenseCategory::queryUser(auth()->user()->id)
            ->where('description', 'Investimentos')
            ->first();
        $firstDay = Carbon::now()->subMonths(12)->startOfMonth();
        $query = Expense::queryUser(auth()->user()->id)
            ->where('date', '>=', $firstDay);

        if ($investmentsCategory !== null) {
            $query->where('expense_category_id', '<>', $investmentsCategory->id);
        }

        $expenses = $query->get();
        $monthsOrdered = [
            Carbon::now()->subMonths(12)->format('M Y'),
            Carbon::now()->subMonths(11)->format('M Y'),
            Carbon::now()->subMonths(10)->format('M Y'),
            Carbon::now()->subMonths(9)->format('M Y'),
            Carbon::now()->subMonths(8)->format('M Y'),
            Carbon::now()->subMonths(7)->format('M Y'),
            Carbon::now()->subMonths(6)->format('M Y'),
            Carbon::now()->subMonths(5)->format('M Y'),
            Carbon::now()->subMonths(4)->format('M Y'),
            Carbon::now()->subMonths(3)->format('M Y'),
            Carbon::now()->subMonths(2)->format('M Y'),
            Carbon::now()->subMonths(1)->format('M Y'),
        ];

        $data = [];
        foreach ($monthsOrdered as $month) {
            $data[$month] = [
                'expenses' => 0,
                'earnings' => 0
            ];
        }

        foreach ($expenses as $expense) {
            $month = (new Carbon($expense->date))->format('M Y');
            $data[$month]['expenses'] += $expense->debit ?? 0;
            $data[$month]['earnings'] += $expense->credit ?? 0;
        }

        return $data;
    }
}
