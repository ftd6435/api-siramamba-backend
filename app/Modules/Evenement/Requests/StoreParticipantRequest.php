<?php

namespace App\Modules\Evenement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreParticipantRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'telephone' => ['required', 'string', 'min:9', 'max:14'],
            'address' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'event_id.required' => "L'événement est obligatoire.",
            'event_id.exists' => "L'événement sélectionné est invalide.",

            'name.required' => "Le nom du participant est obligatoire.",
            'name.min' => "Le nom du participant doit contenir au moins :min caractères.",
            'name.max' => "Le nom du participant ne peut pas dépasser :max caractères.",

            'telephone.required' => "Le numéro de téléphone est obligatoire.",
            'telephone.min' => "Le numéro de téléphone doit contenir au moins :min caractères.",
            'telephone.max' => "Le numéro de téléphone ne peut pas dépasser :max caractères.",

            'address.required' => "L'adresse est obligatoire.",

            'is_active.required' => "Le statut d'activation est obligatoire.",
            'is_active.boolean' => "Le statut d'activation doit être vrai ou faux.",
        ];
    }
}
