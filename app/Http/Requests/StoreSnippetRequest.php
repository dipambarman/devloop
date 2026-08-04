<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'code' => 'required|string',
            'language' => 'required|string|max:50',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
