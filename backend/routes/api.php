<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendantAvailabilityController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('users')->group(function () {
        Route::get('/', [UsersController::class, 'index'])->middleware('can:user.list');
        Route::post('/', [UsersController::class, 'store'])->middleware('can:user.create');
        Route::put('/{user}', [UsersController::class, 'update'])->middleware('can:user.update');
        Route::delete('/{user}', [UsersController::class, 'destroy'])->middleware('can:user.delete');
        Route::patch('/{user}/password', [UsersController::class, 'updatePassword'])->middleware('can:user.update');
        Route::patch('/{user}/status', [UsersController::class, 'updateStatus'])->middleware('can:user.update');
    });

    Route::prefix('attendant-availabilities')->group(function () {
        Route::get('/', [AttendantAvailabilityController::class, 'index'])->middleware('can:attendant-availability.list');
        Route::get('/{availability}', [AttendantAvailabilityController::class, 'show'])->middleware('can:attendant-availability.view');
        Route::post('/', [AttendantAvailabilityController::class, 'store'])->middleware('can:attendant-availability.create');
        Route::put('/{availability}', [AttendantAvailabilityController::class, 'update'])->middleware('can:attendant-availability.update');
        Route::patch('/{availability}/status', [AttendantAvailabilityController::class, 'updateStatus'])->middleware('can:attendant-availability.update');
        Route::delete('/{availability}', [AttendantAvailabilityController::class, 'destroy'])->middleware('can:attendant-availability.delete');
    });
});
