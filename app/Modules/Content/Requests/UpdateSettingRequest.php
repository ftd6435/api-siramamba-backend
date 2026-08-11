<?php

namespace App\Modules\Content\Requests;

use App\Modules\Content\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $setting = $this->route('setting');
        $currentType = $setting instanceof Setting ? $setting->type : null;
        $effectiveType = $this->input('type', $currentType);
        $typeChanged = $this->exists('type') && $effectiveType !== $currentType;

        return [
            'key' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', Rule::in(Setting::TYPES)],
            'value' => $this->valueRules($effectiveType, $typeChanged),
        ];
    }

    private function valueRules(?string $type, bool $typeChanged): array
    {
        $rules = $typeChanged ? ['required'] : ['sometimes', 'required'];

        return match ($type) {
            'text' => [...$rules, 'string'],
            'json' => [...$rules, 'string', 'json'],
            'boolean' => [...$rules, 'boolean'],
            'image' => [...$rules, 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            default => $rules,
        };
    }
}
