<?php

namespace App\Http\Controllers;

use App\Events\DiscussionCreated;
use App\Events\DiscussionReplyAdded;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the discussions for a project.
     */
    public function index(Project $project)
    {
        Gate::authorize('view', $project);

        $discussions = $project->discussions()
            ->with(['user', 'replies'])
            ->withCount('replies')
            ->orderBy('is_pinned', 'desc')
            ->latest('updated_at')
            ->paginate(15);

        return view('discussions.index', compact('project', 'discussions'));
    }

    /**
     * Store a newly created discussion in storage.
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $discussion = $project->discussions()->create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        DiscussionCreated::dispatch($project, auth()->user(), $discussion);

        return redirect()->route('projects.discussions.show', [$project, $discussion])
            ->with('success', 'Discussion started!');
    }

    /**
     * Display the specified discussion.
     */
    public function show(Project $project, Discussion $discussion)
    {
        Gate::authorize('view', $project);

        $discussion->load(['user', 'replies.user']);

        return view('discussions.show', compact('project', 'discussion'));
    }

    /**
     * Toggle the pinned status of a discussion.
     */
    public function togglePin(Project $project, Discussion $discussion)
    {
        Gate::authorize('update', $project);

        $discussion->update([
            'is_pinned' => !$discussion->is_pinned,
        ]);

        return redirect()->back()->with('success', $discussion->is_pinned ? 'Discussion pinned.' : 'Discussion unpinned.');
    }

    /**
     * Store a reply to a discussion.
     */
    public function storeReply(Request $request, Project $project, Discussion $discussion)
    {
        Gate::authorize('view', $project);

        $request->validate([
            'content' => 'required|string',
        ]);

        $reply = $discussion->replies()->create([
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        // Touch the discussion so it bumps to the top
        $discussion->touch();

        DiscussionReplyAdded::dispatch($project, auth()->user(), $discussion, $reply);

        return redirect()->back()->with('success', 'Reply added!');
    }

    /**
     * Remove the specified discussion.
     */
    public function destroy(Project $project, Discussion $discussion)
    {
        if ($discussion->user_id !== auth()->id() && $project->owner_id !== auth()->id()) {
            abort(403);
        }

        $discussion->delete();

        return redirect()->route('projects.discussions.index', $project)
            ->with('success', 'Discussion deleted.');
    }

    /**
     * Remove the specified reply.
     */
    public function destroyReply(Project $project, Discussion $discussion, DiscussionReply $reply)
    {
        if ($reply->user_id !== auth()->id() && $project->owner_id !== auth()->id()) {
            abort(403);
        }

        $reply->delete();

        return redirect()->back()->with('success', 'Reply deleted.');
    }
}
