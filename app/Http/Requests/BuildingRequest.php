<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $infrastructureId = (int) $this->input('infrastructure_id', 0);

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('infrastructures', 'code')->ignore($infrastructureId)],
            'building_type' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'floors' => ['nullable', 'integer', 'min:1', 'max:200'],
            'construction_year' => ['nullable', 'integer', 'between:1900,' . (date('Y') + 1)],
            'area' => ['nullable', 'numeric', 'min:0'],
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'department' => ['nullable', 'string', 'max:150'],
            'contact' => ['nullable', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This building code is already in use.',
            'image.max' => 'Building image must not exceed 3 MB.',
        ];
    }
}
