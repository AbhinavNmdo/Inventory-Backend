<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::post('me', [AuthController::class, 'me']);
    Route::delete('logout', [AuthController::class, 'logout']);

    Route::post('category/index', [CategoryController::class, 'index']);
    Route::resource('category', CategoryController::class)->except([
        'index'
    ]);
    Route::resource('sub-category', SubCategoryController::class)->except([
        'index'
    ]);
});