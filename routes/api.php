<?php

use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\EmployeeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are intended for stateless API access using JWT.
| Tokens are stored in HttpOnly cookies for enhanced security.
|
*/

// Versioned API v1
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        Route::middleware('jwt.cookie')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('jwt.cookie')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
    });
});
