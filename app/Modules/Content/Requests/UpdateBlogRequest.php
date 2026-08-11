<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereIn('type', ['blog', 'mix'])),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'short_description' => ['sometimes', 'required', 'string'],
            'description' => ['sometimes', 'required', 'string'],
            'thumbnail' => ['sometimes', 'required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_featured' => ['sometimes', 'required', 'boolean'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['integer', 'exists:blog_images,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}
