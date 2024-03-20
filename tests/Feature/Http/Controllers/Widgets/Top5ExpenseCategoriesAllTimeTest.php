<?php

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use App\Services\DashboardService;
use Laravel\Sanctum\Sanctum;

class Top5ExpenseCategoriesAllTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_top5_expense_categories_all_time()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $categoryA = ExpenseCategory::factory()->for($user)->create();
        $categoryB = ExpenseCategory::factory()->for($user)->create();
        $expensesA = Expense::factory()
            ->count(5)
            ->for($user)
            ->create([
                'expense_category_id' => $categoryA,
                'expense_sub_category_id' =>  ExpenseSubCategory::factory()->for($user)->create()
            ])
        ;

        $expensesB = Expense::factory()
            ->count(5)
            ->for($user)
            ->create([
                'expense_category_id' => $categoryB,
                'expense_sub_category_id' =>  ExpenseSubCategory::factory()->for($user)->create()
            ])
        ;

        $totalDebitA = 0;
        foreach ($expensesA as $expense) {
            $totalDebitA += $expense->debit ?? 0;
        }

        $totalDebitB = 0;
        foreach ($expensesB as $expense) {
            $totalDebitB += (float) $expense->debit ?? 0;
        }

        $data = [
            $categoryA->description => number_format($totalDebitA, 2),
            $categoryB->description => number_format($totalDebitB, 2)
        ];
        arsort($data);

        $response = $this->get('/api/widgets/' . DashboardService::WIDGET_TOP5_EXPENSE_CATEGORIES_ALL_TIME);
        $response->assertStatus(200);
        $response->assertExactJson($data);

    }
}
