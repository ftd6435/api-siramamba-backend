<?php

use App\Modules\Content\Controllers\CategoryController;
use App\Modules\Content\Controllers\ProjectCommentController;
use App\Modules\Content\Controllers\ProjectController;
use App\Modules\Content\Controllers\ProjectImageController;
use Illuminate\Support\Facades\Route;

Route::get('v1/categories', [CategoryController::class, 'publicIndex']);
Route::get('v1/projects', [ProjectController::class, 'publicIndex']);
Route::get('v1/projects/{project}', [ProjectController::class, 'publicShow']);
Route::get('v1/projects/{project}/comments', [ProjectCommentController::class, 'index']);
Route::post('v1/projects/{project}/comments', [ProjectCommentController::class, 'store']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::patch('categories/{category}', [CategoryController::class, 'update']);
    Route::delete('categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('projects', [ProjectController::class, 'index']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::patch('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    Route::get('projects/{project}/images', [ProjectImageController::class, 'index']);
    Route::post('projects/{project}/images', [ProjectImageController::class, 'store']);
    Route::delete('projects/{project}/images/{projectImage}', [ProjectImageController::class, 'destroy']);
});
