<?php

namespace App\Modules\Evenement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => ['required', 'integer', 'exists:event_categories,id'],
            'title' => ['required', 'string', 'min:2', 'max:200'],
            'short_description' => ['required', 'string'],
            'description' => ['required', 'string'],
            'status' => ['required', 'string', 'in:encours,terminer,planifier'],
            'is_featured' => ['required', 'boolean'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'list_details' => ['nullable', 'array'],
            'is_active' => ['required', 'boolean'],
            'thumbnail' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'video_url_link' => ['required', 'string', 'url'],
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
            'title.string' => "Le titre doit être une chaîne de caractères.",
            'title.min' => "Le titre doit contenir au moins :min caractères.",
            'title.max' => "Le titre ne peut pas dépasser :max caractères.",

            'short_description.required' => "La description courte est obligatoire.",
            'description.required' => "La description est obligatoire.",

            'status.required' => "Le statut est obligatoire.",
            'status.in' => "Le statut doit être : en cours, terminé ou planifié.",

            'is_featured.required' => "L'indicateur \"mis en avant\" est obligatoire.",
            'is_featured.boolean' => "L'indicateur \"mis en avant\" doit être vrai ou faux.",

            'address.required' => "L'adresse est obligatoire.",

            'start_date.required' => "La date de début est obligatoire.",
            'start_date.date' => "La date de début n'est pas valide.",
            'end_date.date' => "La date de fin n'est pas valide.",
            'end_date.after_or_equal' => "La date de fin doit être postérieure ou égale à la date de début.",

            'list_details.array' => "Les détails doivent être une liste.",

            'is_active.required' => "Le statut d'activation est obligatoire.",
            'is_active.boolean' => "Le statut d'activation doit être vrai ou faux.",

            'thumbnail.required' => "L'image de couverture est obligatoire.",
            'thumbnail.image' => "L'image de couverture n'est pas une image valide.",
            'thumbnail.mimes' => "L'image de couverture doit être au format PNG, JPG ou JPEG.",
            'thumbnail.max' => "L'image de couverture ne peut pas dépasser :max Ko.",

            'video_url_link.required' => "Le lien vidéo est obligatoire.",
            'video_url_link.url' => "Le lien vidéo n'est pas une URL valide.",

            'images.array' => "Les images doivent être une liste.",
            'images.*.image' => "Chaque fichier doit être une image valide.",
            'images.*.mimes' => "Chaque image doit être au format PNG, JPG ou JPEG.",
            'images.*.max' => "Chaque image ne peut pas dépasser :max Ko.",
        ];
    }
}
