<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ExpenseCategoryResource;

class ExpenseCategoriesController extends Controller
{

    protected $model = 'App\Models\ExpenseCategory';

    public function index()
    {
        $categories = $this->model::queryUser(auth()->user()->id)
            ->get();

        $resources = ExpenseCategoryResource::collection($categories);
        return response()->json($resources);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $tableToCheck = (new $this->model)->getTable();
        $validated = $request->validate([
            'description' => "required|max:200|unique:$tableToCheck,description"
        ]);

        $category = $this->model::create([
            'user_id' => $user->id,
            'description' => $validated['description']
        ]);

        return response(new ExpenseCategoryResource($category), 201);
    }

    public function show($id)
    {
        $category = $this->model::queryUser(auth()->user()->id)
            ->find($id);

        if ($category === null) {
            abort(404);
        }

        return response()->json(new ExpenseCategoryResource($category));
    }

    public function update(Request $request, $id)
    {
        $category = $this->model::queryUser(auth()->user()->id)
            ->find($id);

        if ($category === null) {
            abort(404);
        }

        $tableToCheck = (new $this->model)->getTable();
        $validated = $request->validate([
            'description' => "required|max:200|unique:$tableToCheck,description"
        ]);

        $category->description = $validated['description'];
        $category->save();

        return response()->json(new ExpenseCategoryResource($category));
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
