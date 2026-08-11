<?php

namespace App\Modules\Content\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $serviceId = $this->route('service')?->getKey();

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'short_description' => ['sometimes', 'required', 'string'],
            'description' => ['sometimes', 'required', 'string'],
            'sort_order' => ['sometimes', 'required', 'integer'],
            'thumbnail' => ['sometimes', 'required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'image_ids' => ['sometimes', 'array'],
            'image_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('service_images', 'id')->where(
                    fn (Builder $query) => $query
                        ->whereNull('service_id')
                        ->orWhere('service_id', $serviceId)
                ),
            ],
        ];
    }
}
