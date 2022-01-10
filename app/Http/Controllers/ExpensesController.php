<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;

class ExpensesController extends Controller
{
    public function index()
    {
        $expenses = Expense::queryUser(auth()->user()->id)
            ->get();
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:200',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'expense_sub_category_id' => 'required|exists:expense_sub_categories,id',
            'note' => 'required|string|max:200',
            'amount' => 'required|numeric'
        ]);

        $expense = Expense::create([
            'user_id' => $user->id,
            'description' => $validated['description'],
            'date' => $validated['date'],
            'expense_category_id' => $validated['expense_category_id'],
            'expense_sub_category_id' => $validated['expense_sub_category_id'],
            'note' => $validated['note'],
            'amount' => $validated['amount']
        ]);

        return response()->json($expense);
    }

    public function show($id)
    {
        $expense = Expense::queryUser(auth()->user()->id)
            ->find($id);
        
        if ($expense === null) {
            abort(404);
        }

        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::queryUser(auth()->user()->id)
            ->find($id);
    
        if ($expense === null) {
            abort(404);
        }

        $validated = $request->validate([
            'date' => 'date',
            'description' => 'string|max:200',
            'expense_category_id' => 'exists:expense_category',
            'expense_sub_category_id' => 'exists:expense_sub_category',
            'note' => 'string|max:200',
            'amount' => 'number'
        ]);

        $expense->update($validated);
        $expense->save();

        return response()->json($expense);
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
