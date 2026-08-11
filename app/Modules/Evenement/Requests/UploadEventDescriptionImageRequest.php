<?php

namespace App\Modules\Evenement\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UploadEventDescriptionImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'upload' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'upload.required' => "L'image est obligatoire.",
            'upload.image' => "Le fichier envoyé n'est pas une image valide.",
            'upload.mimes' => "L'image doit être au format PNG, JPG ou JPEG.",
            'upload.max' => "L'image ne peut pas dépasser :max Ko.",
        ];
    }
}
