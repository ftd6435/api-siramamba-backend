<?php

use App\Modules\Content\Controllers\NewsletterController;
use Illuminate\Support\Facades\Route;

Route::post('v1/newsletters', [NewsletterController::class, 'store']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('newsletters', [NewsletterController::class, 'index']);
    Route::get('newsletters/{newsletter}', [NewsletterController::class, 'show']);
    Route::patch('newsletters/{newsletter}', [NewsletterController::class, 'update']);
    Route::delete('newsletters/{newsletter}', [NewsletterController::class, 'destroy']);
});
