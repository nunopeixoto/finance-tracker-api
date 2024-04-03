<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $rand = rand(0, 1);
        return [
            'description' => $this->faker->name(),
            'date' => $this->faker->dateTime(),
            'note' => $this->faker->name(),
            'debit' => $rand ?  $this->faker->randomFloat(2, 0, 10) : null,
            'credit' =>  $rand ? null : $this->faker->randomFloat(2, 0, 10),
            'expense_category_id' => function () {
                return ExpenseCategory::factory()->create()->id;
            }
        ];
    }
}
