<?php

use App\Modules\Content\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('v1/categories', [CategoryController::class, 'publicIndex']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
});
