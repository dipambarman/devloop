<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectKeyController extends Controller
{
    /**
     * Generate a new Ingest API key for the project.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $plainKey = $project->generateIngestKey();

        return back()->with('success', 'New Ingest API key generated!')
                     ->with('plaintext_key', $plainKey);
    }

    /**
     * Revoke the existing Ingest API key.
     */
    public function destroy(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $project->revokeIngestKey();

        return back()->with('success', 'Ingest API key revoked successfully.');
    }
}
