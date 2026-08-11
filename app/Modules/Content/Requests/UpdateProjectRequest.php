<?php

namespace App\Modules\Content\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'short_description' => ['sometimes', 'required', 'string'],
            'description' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['encours', 'terminer', 'planifier'])],
            'is_featured' => ['sometimes', 'required', 'boolean'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:255'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'progess_percentage' => ['sometimes', 'required', 'integer', 'between:0,100'],
            'list_details' => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'thumbnail' => ['sometimes', 'required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['start_date', 'end_date'])) {
                return;
            }

            $project = $this->route('project');
            $startDate = $this->exists('start_date')
                ? $this->input('start_date')
                : $project->start_date;
            $endDate = $this->exists('end_date')
                ? $this->input('end_date')
                : $project->end_date;

            if ($endDate !== null && Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                $field = $this->exists('end_date') ? 'end_date' : 'start_date';
                $validator->errors()->add(
                    $field,
                    'La date de fin doit être postérieure ou égale à la date de début.'
                );
            }
        });
    }
}
