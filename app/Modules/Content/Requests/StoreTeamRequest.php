<?php

namespace App\Modules\Content\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'post' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'avatar' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
