<?php

namespace App\Modules\Evenement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreEventTestimonialRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'message' => ['required', 'string'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => "Le nom est obligatoire.",
            'name.min' => "Le nom doit contenir au moins :min caractères.",
            'name.max' => "Le nom ne peut pas dépasser :max caractères.",

            'message.required' => "Le message est obligatoire.",
        ];
    }
}
