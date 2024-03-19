<?php
namespace App\Services;

use Exception;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class DashboardService {

    const WIDGET_LAST_12_MONTHS_MONTLY_BALANCE = 'last-12-months-monthly-balance';
    const WIDGET_TOP5_EXPENSE_CATEGORIES_ALL_TIME = 'top5-expense-categories-all-time';

    public function loadWidget(string $name) : array
    {
        switch ($name) {
            case self::WIDGET_LAST_12_MONTHS_MONTLY_BALANCE:
                return $this->loadLast12MonthsMonthlyBalance();
            case self::WIDGET_TOP5_EXPENSE_CATEGORIES_ALL_TIME:
                    return $this->loadTop5ExpenseCategoriesAllTime();
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
        $lastDay = Carbon::now()->endOfMonth();
        $query = Expense::queryUser(auth()->user()->id)
            ->where('date', '>=', $firstDay)
            ->where('date', '<', $lastDay)
        ;

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
            Carbon::now()->format('M Y'),
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

    private function loadTop5ExpenseCategoriesAllTime() : array
    {
        $investmentsCategory = ExpenseCategory::queryUser(auth()->user()->id)
            ->where('description', 'Investimentos')
            ->first();

        $query = Expense::queryUser(auth()->user()->id)
            ->whereNull('credit');

        if ($investmentsCategory !== null) {
            $query->where('expense_category_id', '<>', $investmentsCategory->id);
        }

        $hashMap = [];
        $query->chunk(100, function (Collection $expenses) use (&$hashMap) {
            foreach ($expenses as $expense) {
                if (!isset($hashMap[$expense->expense_category_id])) {
                    $hashMap[$expense->expense_category_id] = $expense->debit;
                    continue;
                }
                $hashMap[$expense->expense_category_id] += $expense->debit;
            }
        });

        arsort($hashMap);
        $top5 = array_slice($hashMap, 0, 5, true);
        $categories = ExpenseCategory::queryUser(auth()->user()->id)
            ->whereIn('id', array_keys($top5))
            ->limit(5)
            ->get();

        $formatted = [];
        foreach ($top5 as $categoryId => $total) {
            $description = $categories->firstWhere('id', $categoryId)->description;
            $formatted[$description] = number_format($total, 2, '.', '');
        }

        return $formatted;
    }
}
