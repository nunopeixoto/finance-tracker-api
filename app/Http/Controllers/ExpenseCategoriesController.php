<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExpenseCategoriesController extends Controller
{

    protected $model = 'App\Models\ExpenseCategory';

    public function index()
    {
        $categories = $this->model::queryUser(auth()->user()->id)
            ->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'description' => 'required|max:200|unique:expense_categories,description'
        ]);

        $category = $this->model::create([
            'user_id' => $user->id,
            'description' => $validated['description']
        ]);

        return response($category, 201);
    }

    public function show($id)
    {
        $category = $this->model::queryUser(auth()->user()->id)
            ->find($id);

        if ($category === null) {
            abort(404);
        }

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = $this->model::queryUser(auth()->user()->id)
            ->find($id);

        if ($category === null) {
            abort(404);
        }

        $validated = $request->validate([
            'description' => 'required|max:200|unique:expense_categories,description'
        ]);

        $category->description = $validated['description'];
        $category->save();

        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = $this->model::queryUser(auth()->user()->id)
            ->find($id);

        if ($category === null) {
            abort(404);
        }

        $category->delete();

        return response('');
    }
}
