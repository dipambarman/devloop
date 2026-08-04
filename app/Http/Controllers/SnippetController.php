<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSnippetRequest;
use App\Http\Requests\UpdateSnippetRequest;
use App\Models\Snippet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SnippetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = auth()->user()->snippets()->with('project');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $snippets = $query->latest()->paginate(15);
        $projects = auth()->user()->allProjects()->get();

        return view('snippets.index', compact('snippets', 'projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $projects = auth()->user()->allProjects()->get();
        $selectedProject = $request->project_id;
        
        return view('snippets.create', compact('projects', 'selectedProject'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSnippetRequest $request)
    {
        auth()->user()->snippets()->create($request->validated());

        return redirect()->route('snippets.index')->with('success', 'Snippet saved successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Snippet $snippet)
    {
        Gate::authorize('view', $snippet);

        return view('snippets.show', compact('snippet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Snippet $snippet)
    {
        Gate::authorize('update', $snippet);

        $projects = auth()->user()->allProjects()->get();

        return view('snippets.edit', compact('snippet', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSnippetRequest $request, Snippet $snippet)
    {
        Gate::authorize('update', $snippet);

        $snippet->update($request->validated());

        return redirect()->route('snippets.index')->with('success', 'Snippet updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Snippet $snippet)
    {
        Gate::authorize('delete', $snippet);

        $snippet->delete();

        return redirect()->route('snippets.index')->with('success', 'Snippet deleted successfully!');
    }
}
