<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($projectId = $this->input('project_id')) {
            $project = Project::find($projectId);

            return $project && $project->isAccessibleBy($this->user());
        }

        return true;
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
