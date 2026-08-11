<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'content' => ['required', 'string'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('project_comments', 'id')->where('project_id', $project->id),
            ],
        ];
    }
}
