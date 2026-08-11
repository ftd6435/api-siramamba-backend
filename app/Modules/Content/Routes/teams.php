<?php

use App\Modules\Content\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('v1/teams', [TeamController::class, 'publicIndex']);
Route::get('v1/teams/{team}', [TeamController::class, 'publicShow']);

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::get('teams/{team}', [TeamController::class, 'show']);
    Route::patch('teams/{team}', [TeamController::class, 'update']);
    Route::delete('teams/{team}', [TeamController::class, 'destroy']);
});
