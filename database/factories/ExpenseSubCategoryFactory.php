<?php

namespace Database\Factories;

use App\Models\ExpenseSubCategory;

class ExpenseSubCategoryFactory extends ExpenseCategoryFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExpenseSubCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'description' => $this->faker->name()
        ];
    }
}
