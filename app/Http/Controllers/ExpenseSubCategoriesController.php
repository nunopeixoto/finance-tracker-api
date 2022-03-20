<?php

namespace App\Http\Controllers;

class ExpenseSubCategoriesController extends ExpenseCategoriesController
{

    public function __construct()
    {
        $this->model = 'App\Models\ExpenseSubCategory';
    }
}
