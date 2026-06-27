<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notes();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $notes = $query->orderByDesc('is_pinned')->latest()->get();
        $projects = auth()->user()->projects;

        return view('notes.index', compact('notes', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = auth()->user()->projects;
        $selectedProject = $request->project_id;
        
        return view('notes.create', compact('projects', 'selectedProject'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_pinned' => 'boolean',
        ]);

        $note = auth()->user()->notes()->create($request->all());

        return redirect()->route('notes.index')->with('success', 'Note created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $projects = auth()->user()->projects;

        return view('notes.edit', compact('note', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'is_pinned' => 'boolean',
        ]);

        // Uncheck checkbox sends nothing, so default to false if missing
        $data = $request->all();
        $data['is_pinned'] = $request->has('is_pinned');

        $note->update($data);

        return redirect()->route('notes.index')->with('success', 'Note updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully!');
    }

    /**
     * Toggle the pinned status via AJAX.
     */
    public function togglePin(Note $note)
    {
        if ($note->user_id !== auth()->id()) {
            abort(403);
        }

        $note->update(['is_pinned' => !$note->is_pinned]);

        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }
}
