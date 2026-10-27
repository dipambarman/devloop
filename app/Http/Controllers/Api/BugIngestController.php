<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\BugIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BugIngestController extends Controller
{
    public function __construct(
        protected BugIngestService $ingestService
    ) {}

    /**
     * Ingest an error payload via headless Ingest API Key.
     */
    public function ingest(Request $request): JsonResponse
    {
        $apiKey = $request->header('X-DevLoop-Key') ?: $request->header('x-api-key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'Missing Ingest API Key in X-DevLoop-Key header.',
                'code' => 'UNAUTHORIZED',
            ], 401);
        }

        $keyHash = hash('sha256', $apiKey);
        $project = Project::where('ingest_key_hash', $keyHash)->first();

        if (!$project) {
            return response()->json([
                'error' => 'Invalid or revoked Ingest API Key.',
                'code' => 'INVALID_API_KEY',
            ], 401);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'stack' => ['nullable', 'string', 'max:50000'],
            'severity' => ['nullable', 'string', 'in:low,medium,high,critical'],
        ]);

        $bug = $this->ingestService->ingest(
            project: $project,
            message: $validated['message'],
            stack: $validated['stack'] ?? null,
            severity: $validated['severity'] ?? 'medium'
        );

        return response()->json([
            'id' => $bug->id,
            'fingerprint' => $bug->fingerprint,
            'title' => $bug->title,
            'error_type' => $bug->error_type,
            'status' => $bug->status,
            'severity' => $bug->severity,
            'occurrence_count' => $bug->occurrence_count,
            'regressions' => $bug->regressions,
            'last_seen_at' => $bug->last_seen_at?->toIso8601String(),
        ], 200);
    }
}
