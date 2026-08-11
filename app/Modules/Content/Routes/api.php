<?php

use App\Modules\Content\Controllers\CategoryController;
use App\Modules\Content\Controllers\ProjectCommentController;
use App\Modules\Content\Controllers\ProjectController;
use App\Modules\Content\Controllers\ProjectImageController;
use App\Modules\Content\Controllers\BlogCommentController;
use App\Modules\Content\Controllers\BlogController;
use App\Modules\Content\Controllers\BlogImageController;
use App\Modules\Content\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;


Route::get('v1/testimonials', [TestimonialController::class, 'publicIndex']);
Route::get('v1/blogs', [BlogController::class, 'publicIndex']);
Route::get('v1/blogs/{blog}', [BlogController::class, 'publicShow']);
Route::get('v1/blogs/{blog}/comments', [BlogCommentController::class, 'index']);
Route::post('v1/blogs/{blog}/comments', [BlogCommentController::class, 'store']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('testimonials', [TestimonialController::class, 'index']);
    Route::post('testimonials', [TestimonialController::class, 'store']);
    Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show']);
    Route::patch('testimonials/{testimonial}', [TestimonialController::class, 'update']);
    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy']);

    Route::get('blogs', [BlogController::class, 'index']);
    Route::post('blogs', [BlogController::class, 'store']);
    Route::get('blogs/{blog}', [BlogController::class, 'show']);
    Route::patch('blogs/{blog}', [BlogController::class, 'update']);
    Route::delete('blogs/{blog}', [BlogController::class, 'destroy']);

    Route::post('blogs/images', [BlogImageController::class, 'storeUnattached']);
    Route::delete('blogs/images/{blogImage}', [BlogImageController::class, 'destroyUnattached']);

    Route::get('blogs/{blog}/images', [BlogImageController::class, 'index']);
    Route::post('blogs/{blog}/images', [BlogImageController::class, 'storeForBlog']);
    Route::delete('blogs/{blog}/images/{blogImage}', [BlogImageController::class, 'destroyForBlog']);
});


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
