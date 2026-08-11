<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in(['encours', 'terminer', 'planifier'])],
            'is_featured' => ['required', 'boolean'],
            'country' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'progess_percentage' => ['required', 'integer', 'between:0,100'],
            'list_details' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
            'thumbnail' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}
