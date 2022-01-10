<?php

use App\Http\Controllers\PortfoliosController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ExpenseCategoriesController;
use App\Http\Controllers\ExpenseSubCategoriesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//Add namespace to avoid all the imports
// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::resource('/user', UserController::class);
    Route::resource('portfolio', PortfoliosController::class);
    Route::resource('expenses', ExpensesController::class);
    Route::resource('expense-categories', ExpenseCategoriesController::class);
    Route::resource('expense-sub-categories', ExpenseSubCategoriesController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
