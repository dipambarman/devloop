<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($projectId = $this->input('project_id')) {
            $project = \App\Models\Project::find($projectId);
            return $project && $project->isAccessibleBy($this->user());
        }

        return true; // project_id is nullable
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:50000',
            'project_id' => 'nullable|exists:projects,id',
            'is_pinned' => 'boolean',
        ];
    }
}
