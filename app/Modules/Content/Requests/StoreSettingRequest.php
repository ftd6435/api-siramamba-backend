<?php

namespace App\Modules\Content\Requests;

use App\Modules\Content\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(Setting::TYPES)],
            'value' => $this->valueRules($this->input('type')),
        ];
    }

    private function valueRules(?string $type): array
    {
        return match ($type) {
            'text' => ['required', 'string'],
            'json' => ['required', 'string', 'json'],
            'boolean' => ['required', 'boolean'],
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            default => ['required'],
        };
    }
}
