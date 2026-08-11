<?php

namespace App\Modules\RelationExterne\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdatePartnerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type_partner_id' => ['sometimes', 'required', 'integer', 'exists:type_partners,id'],
            'company' => ['sometimes', 'required', 'string', 'min:2', 'max:160'],
            'short_description' => ['sometimes', 'required', 'string'],
            'logo' => ['sometimes', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'website_link' => ['sometimes', 'required', 'string', 'url'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'type_partner_id.required' => "Le type de partenaire est obligatoire.",
            'type_partner_id.exists' => "Le type de partenaire sélectionné est invalide.",

            'company.required' => "Le nom de l'entreprise est obligatoire.",
            'company.min' => "Le nom de l'entreprise doit contenir au moins :min caractères.",
            'company.max' => "Le nom de l'entreprise ne peut pas dépasser :max caractères.",

            'short_description.required' => "La description courte est obligatoire.",

            'logo.image' => "Le logo n'est pas une image valide.",
            'logo.mimes' => "Le logo doit être au format PNG, JPG ou JPEG.",
            'logo.max' => "Le logo ne peut pas dépasser :max Ko.",

            'website_link.required' => "Le lien du site web est obligatoire.",
            'website_link.url' => "Le lien du site web n'est pas une URL valide.",

            'is_active.boolean' => "Le statut d'activation doit être vrai ou faux.",
        ];
    }
}
