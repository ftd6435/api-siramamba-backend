<?php

use App\Modules\Content\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::get('v1/testimonials', [TestimonialController::class, 'publicIndex']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('testimonials', [TestimonialController::class, 'index']);
    Route::post('testimonials', [TestimonialController::class, 'store']);
    Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show']);
    Route::patch('testimonials/{testimonial}', [TestimonialController::class, 'update']);
    Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy']);
});
