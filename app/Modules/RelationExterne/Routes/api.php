<?php

use App\Modules\RelationExterne\Controllers\PartnerController;
use App\Modules\RelationExterne\Controllers\TypePartnerController;
use Illuminate\Support\Facades\Route;

// Define API routes for RelationExterne module here
Route::prefix('v1')->group(function () {
    Route::get('/type-partners', [TypePartnerController::class, 'index']);
    Route::get('/type-partners/{typePartner}', [TypePartnerController::class, 'show']);

    Route::get('/partners', [PartnerController::class, 'index']);
    Route::get('/partners/{partner}', [PartnerController::class, 'show']);
});

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('/type-partners', [TypePartnerController::class, 'store']);
    Route::put('/type-partners/{typePartner}', [TypePartnerController::class, 'update']);
    Route::delete('/type-partners/{typePartner}', [TypePartnerController::class, 'destroy']);

    Route::post('/partners', [PartnerController::class, 'store']);
    Route::put('/partners/{partner}', [PartnerController::class, 'update']);
    Route::delete('/partners/{partner}', [PartnerController::class, 'destroy']);
});
