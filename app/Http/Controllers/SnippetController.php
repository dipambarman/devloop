<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use Illuminate\Http\Request;

class SnippetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->snippets();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $snippets = $query->latest()->get();
        $projects = auth()->user()->projects;

        return view('snippets.index', compact('snippets', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = auth()->user()->projects;
        $selectedProject = $request->project_id;
        
        return view('snippets.create', compact('projects', 'selectedProject'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'language' => 'required|string|max:50',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        auth()->user()->snippets()->create($request->all());

        return redirect()->route('snippets.index')->with('success', 'Snippet saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Snippet $snippet)
    {
        if ($snippet->user_id !== auth()->id()) {
            abort(403);
        }

        return view('snippets.show', compact('snippet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Snippet $snippet)
    {
        if ($snippet->user_id !== auth()->id()) {
            abort(403);
        }

        $projects = auth()->user()->projects;

        return view('snippets.edit', compact('snippet', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Snippet $snippet)
    {
        if ($snippet->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'language' => 'required|string|max:50',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $snippet->update($request->all());

        return redirect()->route('snippets.index')->with('success', 'Snippet updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Snippet $snippet)
    {
        if ($snippet->user_id !== auth()->id()) {
            abort(403);
        }

        $snippet->delete();

        return redirect()->route('snippets.index')->with('success', 'Snippet deleted successfully!');
    }
}
