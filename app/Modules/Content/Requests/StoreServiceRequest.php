<?php

namespace App\Modules\Content\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'sort_order' => ['required', 'integer'],
            'thumbnail' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_active' => ['required', 'boolean'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'image_ids' => ['sometimes', 'array'],
            'image_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('service_images', 'id')
                    ->where(fn (Builder $query) => $query->whereNull('service_id')),
            ],
        ];
    }
}
