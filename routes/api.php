<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClientController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('clients', ClientController::class);
    Route::post('/clients', [ClientController::class, 'store'])
        ->middleware('throttle:create-client');
    Route::delete('/clients', [ClientController::class, 'destroyMany']);
});
