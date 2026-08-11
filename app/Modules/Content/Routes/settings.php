<?php

use App\Modules\Content\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1/admin')->group(function () {
    Route::get('settings', [SettingController::class, 'index']);
    Route::post('settings', [SettingController::class, 'store']);
    Route::get('settings/{setting}', [SettingController::class, 'show']);
    Route::patch('settings/{setting}', [SettingController::class, 'update']);
    Route::delete('settings/{setting}', [SettingController::class, 'destroy']);
});
