<?php

namespace App\Modules\RelationExterne\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|string|email',
            'telephone' => 'required|string|max:30',
            'subject' => 'required|string',
            'message' => 'required|string',
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => 'Le nom est requis',
            'name.string' => 'Le nom doit être une chaine de caractères',
            'email.required' => 'L\'email est requis',
            'email.string' => 'L\'email doit être une chaine de caractères',
            'telephone.required' => 'Le téléphone est requis',
            'telephone.string' => 'Le téléphone doit être une chaine de caractères',
            'subject.required' => 'Le sujet est requis',
            'subject.string' => 'Le sujet doit être une chaine de caractères',
            'message.required' => 'Le message est requis',
            'message.string' => 'Le message doit être une chaine de caractères',
            'email.email' => 'L\'email doit être au format valide',
            'telephone.max' => 'Le téléphone doit être de 30 caractères',
        ];
    }
}
