<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'post' => ['sometimes', 'required', 'string', 'max:255'],
            'short_description' => ['sometimes', 'required', 'string'],
            'avatar' => ['sometimes', 'required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
