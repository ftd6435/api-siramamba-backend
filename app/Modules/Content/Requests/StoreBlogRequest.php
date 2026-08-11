<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereIn('type', ['blog', 'mix'])),
            ],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'thumbnail' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'image_ids' => ['nullable', 'array'],
            'image_ids.*' => ['integer', 'exists:blog_images,id'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }
}
