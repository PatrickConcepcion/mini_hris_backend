<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are intended for stateless API access using JWT.
| The `auth:api` middleware uses the `api` guard configured to `jwt`.
|
*/

// Back-compat: simple protected /me endpoint
Route::middleware('auth:api')->get('/me', [AuthController::class, 'me']);

// Versioned API v1
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
        Route::post('refresh', [AuthController::class, 'refresh'])->middleware('throttle:10,1');

        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:api')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
    });
});
