<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($projectId = $this->input('project_id')) {
            $project = \App\Models\Project::find($projectId);
            return $project && $project->isAccessibleBy($this->user());
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:100000',
            'language' => 'required|string|max:50',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
