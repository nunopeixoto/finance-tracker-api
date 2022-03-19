<?php

namespace Tests\Feature\Http;

use App\Models\ExpenseSubCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ExpenseSubCategoriesControllerTest extends TestCase
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
        $response = $this->get('/api/expense-sub-categories');
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Empty list
        $response = $this->get('/api/expense-sub-categories');
        $response->assertStatus(200);
        $response->assertJson([]);

        // OK
        $subCategory = ExpenseSubCategory::factory()->for($user)->create();
        $anotherUser = User::factory()->create();
        ExpenseSubCategory::factory()->for($anotherUser)->create();

        $response = $this->get('/api/expense-sub-categories');
        $response->assertStatus(200);
        $response->assertExactJson([
            [
                'id' => $subCategory->id,
                'user_id' => $user->id,
                'description' => $subCategory->description
            ]
        ]);
    }

    public function test_store()
    {
        // No auth
        $response = $this->post('/api/expense-sub-categories', [
            'description' => 'mycategory'
        ]);
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // OK
        $response = $this->post('/api/expense-sub-categories', [
            'description' => 'mycategory'
        ]);
        $response->assertStatus(201);
        $this->assertEquals(1, ExpenseSubCategory::count()); // we only have one category

        $category = ExpenseSubCategory::first();
        $this->assertInstanceOf(ExpenseSubCategory::class, $category);
        $this->assertEquals('mycategory', $category->description);

        // Duplicate description
        $response = $this->post('/api/expense-sub-categories', [
            'description' => 'mycategory'
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_show()
    {
        // No auth
        $response = $this->get('/api/expense-sub-categories/1', [
            'description' => 'mycategory'
        ]);;
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $subCategory = ExpenseSubCategory::factory()->for($user)->create();

        // Not found
        $response = $this->get("/api/expense-sub-categories/++$subCategory->id", [
            'description' => 'mycategory'
        ]);
        $response->assertStatus(404);

        // Incorrect user
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $response = $this->get("/api/expense-sub-categories/$subCategory->id");
        $response->assertStatus(404);

        // OK
        Sanctum::actingAs($user);
        $response = $this->get("/api/expense-sub-categories/$subCategory->id");
        $response->assertStatus(200);
        $response->assertExactJson([
            'id' => $subCategory->id,
            'user_id' => $user->id,
            'description' => $subCategory->description
        ]);
    }

    public function test_update()
    {
        // No auth
        $response = $this->patch('/api/expense-sub-categories/1', [
            'description' => 'mycategory'
        ]);;
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $subCategory = ExpenseSubCategory::factory()->for($user)->create();

        // Not found
        $response = $this->patch("/api/expense-sub-categories/2", [
            'description' => 'mycategoryupdated'
        ]);
        $response->assertStatus(404);

        // Duplicate description
        $response = $this->patch("/api/expense-sub-categories/$subCategory->id", [
            'description' => $subCategory->description
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['description']);

        // Incorrect user
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $response = $this->patch("/api/expense-sub-categories/$subCategory->id", [
            'description' => 'mycategoryupdated'
        ]);
        $response->assertStatus(404);

        // OK
        Sanctum::actingAs($user);
        $response = $this->patch("/api/expense-sub-categories/$subCategory->id", [
            'description' => 'mycategoryupdated'
        ]);
        $response->assertStatus(200);
        $subCategory->refresh();
        $this->assertEquals('mycategoryupdated', $subCategory->description);
    }

    public function test_destroy()
    {
        // No auth
        $response = $this->delete('/api/expense-sub-categories/1');
        $response->assertStatus(401);

        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $subCategory = ExpenseSubCategory::factory()->for($user)->create();

        // Not found
        $response = $this->delete('/api/expense-sub-categories/2');
        $response->assertStatus(404);

        // Incorrect user
        $anotherUser = User::factory()->create();
        Sanctum::actingAs($anotherUser);
        $response = $this->delete("/api/expense-sub-categories/$subCategory->id");
        $response->assertStatus(404);

        // OK
        Sanctum::actingAs($user);
        $response = $this->delete("/api/expense-sub-categories/$subCategory->id");
        $response->assertStatus(200);

    }
}
