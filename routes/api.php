<?php

use App\Http\Controllers\Api\BugIngestController;
use App\Http\Controllers\Api\GitHubWebhookController;
use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthCheckController::class, 'check'])->name('api.health');

// Headless Error Ingestion Route (DevLoop Mini Engine)
Route::middleware('throttle:ingest')->group(function () {
    Route::post('/v1/ingest', [BugIngestController::class, 'ingest'])->name('api.v1.ingest');
    Route::post('/ingest', [BugIngestController::class, 'ingest'])->name('api.ingest');
});

// GitHub Webhook Ingest (Auto-resolve tasks on commit / PR merge)
Route::post('/webhooks/github', [GitHubWebhookController::class, 'handle'])->name('api.webhooks.github');
Route::post('/projects/{project}/webhooks/github', [GitHubWebhookController::class, 'handle'])->name('api.projects.webhooks.github');
