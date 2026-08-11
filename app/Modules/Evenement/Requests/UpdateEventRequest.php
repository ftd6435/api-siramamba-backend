<?php

namespace App\Modules\Evenement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:event_categories,id'],
            'title' => ['sometimes', 'required', 'string', 'min:2', 'max:200'],
            'short_description' => ['sometimes', 'required', 'string'],
            'description' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:encours,terminer,planifier'],
            'is_featured' => ['sometimes', 'required', 'boolean'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'list_details' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'thumbnail' => ['sometimes', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'video_url_link' => ['sometimes', 'required', 'string', 'url'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'category_id.required' => "La catégorie est obligatoire.",
            'category_id.exists' => "La catégorie sélectionnée est invalide.",

            'title.required' => "Le titre est obligatoire.",
            'title.min' => "Le titre doit contenir au moins :min caractères.",
            'title.max' => "Le titre ne peut pas dépasser :max caractères.",

            'short_description.required' => "La description courte est obligatoire.",
            'description.required' => "La description est obligatoire.",

            'status.required' => "Le statut est obligatoire.",
            'status.in' => "Le statut doit être : en cours, terminé ou planifié.",

            'is_featured.boolean' => "L'indicateur \"mis en avant\" doit être vrai ou faux.",

            'address.required' => "L'adresse est obligatoire.",

            'start_date.date' => "La date de début n'est pas valide.",
            'end_date.date' => "La date de fin n'est pas valide.",
            'end_date.after_or_equal' => "La date de fin doit être postérieure ou égale à la date de début.",

            'list_details.array' => "Les détails doivent être une liste.",

            'is_active.boolean' => "Le statut d'activation doit être vrai ou faux.",

            'thumbnail.image' => "L'image de couverture n'est pas une image valide.",
            'thumbnail.mimes' => "L'image de couverture doit être au format PNG, JPG ou JPEG.",
            'thumbnail.max' => "L'image de couverture ne peut pas dépasser :max Ko.",

            'video_url_link.url' => "Le lien vidéo n'est pas une URL valide.",

            'images.array' => "Les images doivent être une liste.",
            'images.*.image' => "Chaque fichier doit être une image valide.",
            'images.*.mimes' => "Chaque image doit être au format PNG, JPG ou JPEG.",
            'images.*.max' => "Chaque image ne peut pas dépasser :max Ko.",
        ];
    }
}
