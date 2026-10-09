<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoadRequest extends FormRequest
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
            'road_type' => ['required', 'string', 'max:100'],
            'road_number' => ['nullable', 'string', 'max:50'],
            'start_point' => ['nullable', 'string', 'max:150'],
            'end_point' => ['nullable', 'string', 'max:150'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'lanes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'location' => ['required', 'string', 'max:150'],
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'construction_year' => ['nullable', 'integer', 'between:1900,' . (date('Y') + 1)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This road code is already in use.',
            'image.max' => 'Road image must not exceed 3 MB.',
        ];
    }
}
