<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SlackWebhook;
use App\Services\SlackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SlackWebhookController extends Controller
{
    public function __construct(
        protected SlackWebhookService $slackService
    ) {}

    /**
     * Store a new Slack webhook for the project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'Unauthorized to configure webhooks.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'webhook_url' => ['required', 'url', 'max:500'],
            'channel' => ['nullable', 'string', 'max:100'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:*,bug.created,bug.regression,bug.critical,bug.status_changed,task.assigned,task.completed'],
        ]);

        $project->slackWebhooks()->create([
            'name' => $validated['name'],
            'webhook_url' => $validated['webhook_url'],
            'channel' => $validated['channel'] ?? null,
            'events' => $validated['events'],
            'is_active' => true,
        ]);

        return back()->with('success', "Slack webhook '{$validated['name']}' added successfully.");
    }

    /**
     * Update an existing webhook (events, status, channel).
     */
    public function update(Request $request, Project $project, SlackWebhook $slackWebhook): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'Unauthorized to configure webhooks.');
        abort_if($slackWebhook->project_id !== $project->id, 404);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'webhook_url' => ['sometimes', 'required', 'url', 'max:500'],
            'channel' => ['nullable', 'string', 'max:100'],
            'events' => ['sometimes', 'required', 'array', 'min:1'],
            'events.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $slackWebhook->update($validated);

        return back()->with('success', "Slack webhook updated.");
    }

    /**
     * Send a live test notification to the Slack webhook.
     */
    public function test(Request $request, Project $project, SlackWebhook $slackWebhook): JsonResponse|RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'Unauthorized to configure webhooks.');
        abort_if($slackWebhook->project_id !== $project->id, 404);

        $result = $this->slackService->sendTestNotification($slackWebhook);

        if ($request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['slack' => $result['message']]);
    }

    /**
     * Remove the Slack webhook.
     */
    public function destroy(Project $project, SlackWebhook $slackWebhook): RedirectResponse
    {
        Gate::authorize('view', $project);
        abort_unless($project->canEdit(auth()->user()), 403, 'Unauthorized to configure webhooks.');
        abort_if($slackWebhook->project_id !== $project->id, 404);

        $slackWebhook->delete();

        return back()->with('success', "Slack webhook removed.");
    }
}
