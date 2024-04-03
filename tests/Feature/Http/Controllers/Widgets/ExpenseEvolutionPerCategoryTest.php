<?php

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\DashboardService;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;

class ExpenseEvolutionPerCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_expense_evolution_per_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get('/api/widgets/' . DashboardService::WIDGET_EXPENSE_EVOLUTION_PER_CATEGORY . '?expenseCategoryId=asd');
        $response->assertStatus(422);

        $response = $this->get('/api/widgets/' . DashboardService::WIDGET_EXPENSE_EVOLUTION_PER_CATEGORY . '?expenseCategoryId=1');
        $response->assertStatus(422);

        $category = ExpenseCategory::factory()->for($user)->create();
        $expenses = Expense::factory(5)
            ->for($user)
            ->create([
                'expense_category_id' => $category->id,
                'date' => new Carbon('2020-01-01'),
                'debit' => 10
            ]);
        $expenses = Expense::factory(5)
            ->for($user)
            ->create([
                'expense_category_id' => $category->id,
                'date' => new Carbon('2020-02-01'),
                'debit' => 5
            ]);
        $response = $this->get('/api/widgets/' . DashboardService::WIDGET_EXPENSE_EVOLUTION_PER_CATEGORY . '?expenseCategoryId=' . $category->id);
        $response->assertStatus(200);

        // Expects following format: [$month => $amountSpent]
        $response->assertJsonFragment([
            'Jan 2020' => 50,
            'Feb 2020' => 25
        ]);
    }
}
