<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ArsipController;

// Auth (punyamu)
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//activity log
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
  
// Publik (tanpa auth)
Route::get('/public/documents/{document}', [DocumentController::class, 'showPublic']);
Route::get('/public/documents/{document}/download', [DocumentController::class, 'downloadPublic']);

// Sekdes-only
// Route::middleware(['auth:sanctum','role:sekdes'])->group(function () {
//     Route::post('/documents', [DocumentController::class, 'store']);
//     Route::put('/documents/{document}', [DocumentController::class, 'update']);
//     Route::post('/documents/{document}/publish', [DocumentController::class, 'publish']);
//     Route::get('/documents/{document}/download', [DocumentController::class, 'download']);

//     Route::post('/archives', [ArsipController::class, 'store']);
// });

// Kepdes-only
Route::middleware(['auth:sanctum','role:kepdes'])->group(function () {
    Route::put('/documents/{document}/approve', [DocumentController::class, 'approve']); #bisa
    Route::post('/documents/{document}/reject',  [DocumentController::class, 'reject']); #bisa
});

// Sekdes & Kepdes
Route::middleware(['auth:sanctum','role:sekdes,kepdes'])->group(function () {
    Route::get('/documents', [DocumentController::class, 'index']); #bisa
    Route::get('/documents/{document}', [DocumentController::class, 'show']); #bisa
    
    Route::post('/documents', [DocumentController::class, 'store']); #bisa
    Route::put('/documents/{document}', [DocumentController::class, 'update']); #bisa
    Route::post('/documents/{document}/publish', [DocumentController::class, 'publish']); 
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']); #bisa

    Route::post('/archives', [ArsipController::class, 'store']); #bisa 
    Route::get('/archives', [ArsipController::class, 'index']); #bisa
    Route::get('/archives/{archive}', [ArsipController::class, 'show']); #bisa

    
    Route::get('/laporan', [DocumentController::class, 'laporan']); 
});
