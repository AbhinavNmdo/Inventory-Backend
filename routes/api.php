<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserController;
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

    Route::post('user/index', [UserController::class, 'index']);
    Route::resource('user', UserController::class)->except([
        'index'
    ]);

    Route::post('category/index', [CategoryController::class, 'index']);
    Route::resource('category', CategoryController::class)->except([
        'index'
    ]);

    Route::post('sub-category/index', [SubCategoryController::class, 'index']);
    Route::resource('sub-category', SubCategoryController::class)->except([
        'index'
    ]);

    Route::post('product/index', [ProductController::class, 'index']);
    Route::resource('product', ProductController::class)->except([
        'index'
    ]);
});