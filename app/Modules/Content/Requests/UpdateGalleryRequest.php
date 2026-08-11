<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:category_galleries,id'],
            'image' => ['sometimes', 'required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'short_description' => ['sometimes', 'required', 'string'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
