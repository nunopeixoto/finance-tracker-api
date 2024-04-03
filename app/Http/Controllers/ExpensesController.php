<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpensesController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['expenseCategory', 'expenseSubCategory'])
            ->queryUser(auth()->user()->id)
            ->get();

        $resources = ExpenseResource::collection($expenses);
        return response()->json($resources);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:200',
            'expenseCategoryId' => 'required|exists:expense_categories,id',
            'expenseSubCategoryId' => 'required|exists:expense_sub_categories,id',
            'note' => 'required|string|max:200',
            'type' => 'required|string|in:debit,credit',
            'amount' => 'required|numeric'
        ]);

        $expense = Expense::create([
            'user_id' => $user->id,
            'description' => $validated['description'],
            'date' => $validated['date'],
            'expense_category_id' => $validated['expenseCategoryId'],
            'expense_sub_category_id' => $validated['expenseSubCategoryId'],
            'note' => $validated['note'],
            'debit' => $validated['type'] === 'debit' ? $validated['amount'] : null,
            'credit' => $validated['type'] === 'credit' ? $validated['amount'] : null
        ]);

        return response(new ExpenseResource($expense), 201);
    }

    public function show($id)
    {
        $expense = Expense::queryUser(auth()->user()->id)
            ->find($id);

        if ($expense === null) {
            abort(404);
        }

        return response()->json(new ExpenseResource($expense));
    }

    public function update(Request $request, $id)
    {
        $userId = auth()->user()->id;
        $expense = Expense::queryUser($userId)
            ->find($id);

        if ($expense === null) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => 'date',
            'description' => 'string|max:200',
            'expenseCategoryId' => "exists:expense_categories,id,user_id,$userId",
            'expenseSubCategoryId' => "exists:expense_sub_categories,id,user_id,$userId",
            'note' => 'string|max:200',
            'type' => 'string|in:debit,credit',
            'amount' => 'numeric'
        ]);

        $expense->update($validated);
        $expense->save();

        return response()->json(new ExpenseResource($expense));
    }

    public function destroy($id)
    {
        $expense = Expense::queryUser(auth()->user()->id)
            ->find($id);

        if ($expense === null) {
            abort(404);
        }

        $expense->delete();

        return response('');
    }
}
