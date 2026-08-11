<?php

use App\Modules\Evenement\Controllers\EventCategoryController;
use App\Modules\Evenement\Controllers\EventController;
use App\Modules\Evenement\Controllers\EventTestimonialController;
use App\Modules\Evenement\Controllers\ParticipantController;
use Illuminate\Support\Facades\Route;

// Define API routes for Evenement module here
Route::prefix('v1')->group(function () {
    Route::get('/event-categories', [EventCategoryController::class, 'index']);
    Route::get('/event-categories/{eventCategory}', [EventCategoryController::class, 'show']);

    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{event}', [EventController::class, 'show']);

    Route::get('/event-testimonials', [EventTestimonialController::class, 'index']);
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('/event-categories', [EventCategoryController::class, 'store']);
    Route::put('/event-categories/{eventCategory}', [EventCategoryController::class, 'update']);
    Route::delete('/event-categories/{eventCategory}', [EventCategoryController::class, 'destroy']);

    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events/{event}', [EventController::class, 'destroy']);
    Route::delete('/events/images/{image}', [EventController::class, 'destroyImage']);
    Route::post('/events/description-image', [EventController::class, 'uploadDescriptionImage']);

    Route::get('/participants', [ParticipantController::class, 'index']);
    Route::get('/participants/{participant}', [ParticipantController::class, 'show']);
    Route::post('/participants', [ParticipantController::class, 'store']);
    Route::put('/participants/{participant}', [ParticipantController::class, 'update']);
    Route::delete('/participants/{participant}', [ParticipantController::class, 'destroy']);

    Route::post('/event-testimonials', [EventTestimonialController::class, 'store']);
    Route::put('/event-testimonials/{eventTestimonial}', [EventTestimonialController::class, 'update']);
    Route::delete('/event-testimonials/{eventTestimonial}', [EventTestimonialController::class, 'destroy']);
});
