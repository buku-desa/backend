<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ArsipController;

// Auth (punyamu)
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Publik (tanpa auth)
Route::get('/documents', [DocumentController::class, 'index']); #bisa
Route::get('/documents/{document}', [DocumentController::class, 'show']); #bisa
Route::get('/public/documents/{document}', [DocumentController::class, 'showPublic']);
Route::get('/public/documents/{document}/download', [DocumentController::class, 'downloadPublic']);

// Kepdes-only
Route::middleware(['auth:sanctum', 'role:kepdes'])->group(function () {
    Route::put('/documents/{document}/approve', [DocumentController::class, 'approve']); #bisa
    Route::post('/documents/{document}/reject',  [DocumentController::class, 'reject']); #bisa
});

// Sekdes & Kepdes
Route::middleware(['auth:sanctum', 'role:sekdes,kepdes'])->group(function () {

    Route::post('/documents', [DocumentController::class, 'store']); #bisa
    Route::put('/documents/{id}', [DocumentController::class, 'update']); #bisa
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']); #bisa
    Route::put('/documents/{document}/publish', [DocumentController::class, 'publish']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']); #bisa

    Route::post('/archives', [ArsipController::class, 'store']); #bisa 
    Route::get('/archives', [ArsipController::class, 'index']); #bisa
    Route::get('/archives/{archive}', [ArsipController::class, 'show']); #bisa

    Route::get('/laporan', [DocumentController::class, 'laporan']);

    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
});
