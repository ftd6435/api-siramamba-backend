<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:category_galleries,id'],
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'short_description' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
