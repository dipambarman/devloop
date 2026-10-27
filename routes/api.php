<?php

use App\Http\Controllers\Api\BugIngestController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'check'])->name('api.health');

// Headless Error Ingestion Route (DevLoop Mini Engine)
Route::post('/v1/ingest', [BugIngestController::class, 'ingest'])->name('api.v1.ingest');
Route::post('/ingest', [BugIngestController::class, 'ingest'])->name('api.ingest');
