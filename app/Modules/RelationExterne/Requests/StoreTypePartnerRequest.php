<?php

namespace App\Modules\RelationExterne\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreTypePartnerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => "Le nom du type de partenaire est obligatoire.",
            'name.min' => "Le nom du type de partenaire doit contenir au moins :min caractères.",
            'name.max' => "Le nom du type de partenaire ne peut pas dépasser :max caractères.",

            'is_active.required' => "Le statut d'activation est obligatoire.",
            'is_active.boolean' => "Le statut d'activation doit être vrai ou faux.",
        ];
    }
}
