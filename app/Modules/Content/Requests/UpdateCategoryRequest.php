<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['mix', 'projet', 'blog'])],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
