<?php

use App\Modules\Content\Controllers\ServiceController;
use App\Modules\Content\Controllers\ServiceImageController;
use Illuminate\Support\Facades\Route;

Route::get('v1/services', [ServiceController::class, 'publicIndex']);
Route::get('v1/services/{service}', [ServiceController::class, 'publicShow']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('services', [ServiceController::class, 'index']);
    Route::post('services', [ServiceController::class, 'store']);
    Route::post('services/images', [ServiceImageController::class, 'uploadEditorImage']);
    Route::get('services/{service}', [ServiceController::class, 'show']);
    Route::patch('services/{service}', [ServiceController::class, 'update']);
    Route::delete('services/{service}', [ServiceController::class, 'destroy']);
    Route::delete('services/{service}/images/{serviceImage}', [ServiceImageController::class, 'destroy']);
});
