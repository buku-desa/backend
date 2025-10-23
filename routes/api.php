<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityLogController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//activity log
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
});
