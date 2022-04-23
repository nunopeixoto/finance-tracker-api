<?php

namespace Tests\Feature\Http;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();
        $this->withHeaders([
            'Accept' => 'application/json'
        ]);
    }

    public function test_index()
    {
        // No auth
        $response = $this->get('/api/expenses');
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Empty list
        $response = $this->get('/api/expenses');
        $response->assertStatus(200);
        $response->assertJson([]);

        // OK
        $category = ExpenseCategory::factory()->for($user)->create();
        $subCategory = ExpenseSubCategory::factory()->for($user)->create();
        $expense = Expense::factory()
            ->for($user)
            ->create([
                'expense_category_id' => $category->id,
                'expense_sub_category_id' => $subCategory->id
            ])
        ;
        $anotherUser = User::factory()->create();
        $anotherExpense = Expense::factory()
            ->for($anotherUser)
            ->create([
                'expense_category_id' => ExpenseCategory::factory()->for($anotherUser),
                'expense_sub_category_id' => ExpenseSubCategory::factory()->for($anotherUser)
            ])
        ;

        $response = $this->get('/api/expenses');
        $response->assertStatus(200);

        $response->assertExactJson([
            [
                'id' => $expense->id,
                'date' => $expense->date->format('Y-m-d H:i:s'),
                'description' => $expense->description,
                'category' => [
                    'id' => $category->id,
                    'user_id' => $category->user_id,
                    'description' => $category->description
                ],
                'subCategory' => [
                    'id' => $subCategory->id,
                    'user_id' => $subCategory->user_id,
                    'description' => $subCategory->description
                ],
                'note' => $expense->note,
                'amount' => (float) number_format($expense->amount, 2),
            ]
        ]);
    }

    public function test_store()
    {
        // No auth
        $response = $this->post('/api/expenses', []);
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $expenseCategory = ExpenseCategory::factory()->for($user)->create();
        $expenseSubCategory = ExpenseSubCategory::factory()->for($user)->create();

        // OK
        $response = $this->post('/api/expenses', [
            'description' => 'Padel',
            'date' => '2021-01-01',
            'expenseCategoryId' => $expenseCategory->id,
            'expenseSubCategoryId' => $expenseSubCategory->id,
            'note' => 'A note',
            'amount' => 30
        ]);
        $response->assertStatus(201);
        $this->assertEquals(1, Expense::count()); // we only have one category

        $expense = Expense::first();
        $this->assertInstanceOf(Expense::class, $expense);
        $this->assertEquals('Padel', $expense->description);
        $this->assertEquals('2021-01-01 00:00:00', $expense->date);
        $this->assertEquals($expenseCategory->id, $expense->expense_category_id);
        $this->assertEquals($expenseSubCategory->id, $expense->expense_sub_category_id);
        $this->assertEquals('A note', $expense->note);
        $this->assertEquals('30.00', $expense->amount);

        // Invalid expense category
        $response = $this->post('/api/expenses', [
            'description' => 'Padel',
            'date' => '2021-01-01',
            'expenseCategoryId' => 2,
            'expenseSub_categoryId' => $expenseSubCategory->id,
            'note' => 'A note',
            'amount' => 30
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expenseCategoryId']);
    }

    public function test_show()
    {
        // No auth
        $response = $this->get('/api/expenses/1');
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $category = ExpenseCategory::factory()->for($user)->create();
        $subCategory = ExpenseSubCategory::factory()->for($user)->create();
        $expense = Expense::factory()
            ->for($user)
            ->create([
                'expense_category_id' => $category->id,
                'expense_sub_category_id' => $subCategory->id
            ])
        ;

        // Not found
        $response = $this->get("/api/expenses/++$expense->id", [
            'description' => 'mycategory'
        ]);
        $response->assertStatus(404);

        // Incorrect user
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $response = $this->get("/api/expenses/$expense->id");
        $response->assertStatus(404);

        // OK
        Sanctum::actingAs($user);
        $response = $this->get("/api/expenses/$expense->id");
        $response->assertStatus(200);
        $response->assertExactJson([
            'id' => $expense->id,
            'date' => $expense->date->format('Y-m-d H:i:s'),
            'description' => $expense->description,
            'category' => [
                'id' => $category->id,
                'user_id' => $category->user_id,
                'description' => $category->description
            ],
            'subCategory' => [
                'id' => $subCategory->id,
                'user_id' => $subCategory->user_id,
                'description' => $subCategory->description
            ],
            'note' => $expense->note,
            'amount' => (float) number_format($expense->amount, 2),
        ]);
    }

    public function test_update()
    {
        // No auth
        $response = $this->patch('/api/expenses/1', [
            'description' => 'mycategory'
        ]);;
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $expense = Expense::factory()
            ->for($user)
            ->create([
                'expense_category_id' => ExpenseCategory::factory()->for($user),
                'expense_sub_category_id' => ExpenseSubCategory::factory()->for($user)
            ])
        ;

        // Not found
        $response = $this->patch("/api/expenses/2", [
            'note' => 'update note'
        ]);
        $response->assertStatus(404);


        // Incorrect user
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $response = $this->patch("/api/expenses/$expense->id", [
            'note' => 'update note'
        ]);
        $response->assertStatus(404);

        // OK
        Sanctum::actingAs($user);
        $response = $this->patch("/api/expenses/$expense->id", [
            'note' => 'my note updated'
        ]);
        $response->assertStatus(200);
        $expense->refresh();
        $this->assertEquals('my note updated', $expense->note);

        // // Validation errors
        $response = $this->patch("/api/expenses/$expense->id", [
            'amount' => 'not a number'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);

        $response = $this->patch("/api/expenses/$expense->id", [
            'expenseCategoryId' => ExpenseCategory::factory()->for($anotherUser)->create()->id
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expenseCategoryId']);
    }

    public function test_destroy()
    {
        // No auth
        $response = $this->delete('/api/expenses/1');
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $expense = Expense::factory()
            ->for($user)
            ->create([
                'expense_category_id' => ExpenseCategory::factory()->for($user),
                'expense_sub_category_id' => ExpenseSubCategory::factory()->for($user)
            ])
        ;

        // Not found
        $response = $this->delete('/api/expenses/2');
        $response->assertStatus(404);

        // Incorrect user
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $response = $this->delete("/api/expenses/$expense->id");
        $response->assertStatus(404);

        // OK
        Sanctum::actingAs($user);
        $response = $this->delete("/api/expenses/$expense->id");
        $response->assertStatus(200);
    }
}
