<?php

namespace Tests\Feature\Http;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseSubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Carbon;

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
        $expense = Expense::factory()
            ->for($user)
            ->create([
                'expense_category_id' => ExpenseCategory::factory()->for($user),
                'expense_sub_category_id' => ExpenseSubCategory::factory()->for($user)
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
                'user_id' => $expense->user_id,
                'date' => $expense->date->format('Y-m-d H:i:s'),
                'description' => $expense->description,
                'expense_category_id' => $expense->expense_category_id,
                'expense_sub_category_id' => $expense->expense_sub_category_id,
                'note' => $expense->note,
                'amount' => (string) number_format($expense->amount, 2),
                'created_at' => $expense->created_at,
                'updated_at' => $expense->updated_at,
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
            'expense_category_id' => $expenseCategory->id,
            'expense_sub_category_id' => $expenseSubCategory->id,
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
            'expense_category_id' => 2,
            'expense_sub_category_id' => $expenseSubCategory->id,
            'note' => 'A note',
            'amount' => 30
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expense_category_id']);
    }

    public function test_show()
    {
        // No auth
        $response = $this->get('/api/expenses/1');
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
            'user_id' => $expense->user_id,
            'date' => $expense->date->format('Y-m-d H:i:s'),
            'description' => $expense->description,
            'expense_category_id' => $expense->expense_category_id,
            'expense_sub_category_id' => $expense->expense_sub_category_id,
            'note' => $expense->note,
            'amount' => (string) number_format($expense->amount, 2),
            'created_at' => $expense->created_at,
            'updated_at' => $expense->updated_at,
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
            'expense_category_id' => ExpenseCategory::factory()->for($anotherUser)->create()->id
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['expense_category_id']);
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
