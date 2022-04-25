<?php
namespace App\Services;

use Exception;
use App\Models\Expense;
use Carbon\Carbon;

class DashboardService {

    const WIDGET_LAST_12_MONTHS_MONTLY_BALANCE = 'last-12-months-balance';

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
        $firstDay = Carbon::now()->subMonths(12)->startOfMonth();
        $expenses = Expense::queryUser(auth()->user()->id)
            ->where('date', '>', $firstDay)
            ->get();

        $monthsOrdered = [
            Carbon::now()->subMonths(12)->format('M'),
            Carbon::now()->subMonths(11)->format('M'),
            Carbon::now()->subMonths(10)->format('M'),
            Carbon::now()->subMonths(9)->format('M'),
            Carbon::now()->subMonths(8)->format('M'),
            Carbon::now()->subMonths(7)->format('M'),
            Carbon::now()->subMonths(6)->format('M'),
            Carbon::now()->subMonths(5)->format('M'),
            Carbon::now()->subMonths(4)->format('M'),
            Carbon::now()->subMonths(3)->format('M'),
            Carbon::now()->subMonths(2)->format('M'),
            Carbon::now()->subMonths(1)->format('M'),
            Carbon::now()->format('M'),
        ];

        $data = [];
        foreach ($monthsOrdered as $month) {
            $data[$month] = [
                'expenses' => 0,
                'earnings' => 0
            ];
        }

        foreach ($expenses as $expense) {
            $month = (new Carbon($expense->date))->format('M');
            $data[$month]['expenses'] += $expense->debit ?? 0;
            $data[$month]['earnings'] += $expense->debit ?? 0;
        }

        return $data;
    }
}
