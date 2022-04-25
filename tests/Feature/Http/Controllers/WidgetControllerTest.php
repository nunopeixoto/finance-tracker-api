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
use Carbon\Carbon;

class WidgetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();
        $this->withHeaders([
            'Accept' => 'application/json'
        ]);
    }

    public function test_requires_auth()
    {
        $response = $this->get('/api/widgets/' . DashboardService::WIDGET_LAST_12_MONTHS_MONTLY_BALANCE);
        $response->assertStatus(401);
    }

    public function test_widget_name_validation()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->get('/api/widgets/random-name');
        $response->assertStatus(404);

        $response = $this->get('/api/widgets/');
        $response->assertStatus(404);
    }

    public function test_widget_current_year_monthly_balance()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Not eligible
        Expense::factory()
            ->count(5)
            ->for($user)
            ->create([
                'date' => Carbon::now()->subMonths(13)->startOfMonth(),
                'expense_category_id' => ExpenseCategory::factory()->for($user),
                'expense_sub_category_id' =>  ExpenseSubCategory::factory()->for($user)->create()
            ])
        ;

        $expenses = Expense::factory()
            ->count(5)
            ->for($user)
            ->create([
                'date' => Carbon::now()->subMonths(11)->startOfMonth(),
                'expense_category_id' => ExpenseCategory::factory()->for($user),
                'expense_sub_category_id' =>  ExpenseSubCategory::factory()->for($user)->create()
            ])
        ;

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($expenses as $expense) {
            $totalDebit += $expense->debit ?? 0;
            $totalCredit += $expense->debit ?? 0;
        }

        $response = $this->get('/api/widgets/' . DashboardService::WIDGET_LAST_12_MONTHS_MONTLY_BALANCE);
        $response->assertStatus(200);
        $response->assertJsonCount(12);

        // Expects following format: [$month => ['expenses' => X, 'earnings' => Y]]
        $response->assertJsonFragment([
            (new Carbon($expenses[0]->date))->format('M') => [
                'expenses' => $totalDebit,
                'earnings' => $totalCredit,
            ]
        ]);
    }
}
