<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->notes()->with('project');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $notes = $query->orderByDesc('is_pinned')->latest()->paginate(15);
        $projects = auth()->user()->allProjects()->get();

        return view('notes.index', compact('notes', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = auth()->user()->allProjects()->get();
        $selectedProject = $request->project_id;
        
        return view('notes.create', compact('projects', 'selectedProject'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request)
    {
        auth()->user()->notes()->create($request->validated());

        return redirect()->route('notes.index')->with('success', 'Note created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        Gate::authorize('view', $note);

        return view('notes.show', compact('note'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        Gate::authorize('update', $note);

        $projects = auth()->user()->allProjects()->get();

        return view('notes.edit', compact('note', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note)
    {
        Gate::authorize('update', $note);

        $data = $request->validated();
        $data['is_pinned'] = $request->boolean('is_pinned');

        $note->update($data);

        return redirect()->route('notes.index')->with('success', 'Note updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        Gate::authorize('delete', $note);

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully!');
    }

    /**
     * Toggle the pinned status via AJAX.
     */
    public function togglePin(Note $note)
    {
        Gate::authorize('update', $note);

        $note->update(['is_pinned' => !$note->is_pinned]);

        return response()->json(['success' => true, 'is_pinned' => $note->is_pinned]);
    }
}
