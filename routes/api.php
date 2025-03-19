<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CefrController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

Route::post('/language-test/{userId}', [CefrController::class, 'languageTest']);
Route::post('/test-summary', [CefrController::class, 'storeSummary']);
