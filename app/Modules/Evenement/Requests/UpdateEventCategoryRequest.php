<?php

namespace App\Modules\Evenement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateEventCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:160'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => "Le nom de la catégorie est obligatoire.",
            'name.string' => "Le nom de la catégorie doit être une chaîne de caractères.",
            'name.min' => "Le nom de la catégorie doit contenir au moins :min caractères.",
            'name.max' => "Le nom de la catégorie ne peut pas dépasser :max caractères.",

            'is_active.required' => "Le statut d'activation est obligatoire.",
            'is_active.boolean' => "Le statut d'activation doit être vrai ou faux.",
        ];
    }
}
