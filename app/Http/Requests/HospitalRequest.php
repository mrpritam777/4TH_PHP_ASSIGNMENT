<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HospitalRequest extends FormRequest
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
            'hospital_type' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'emergency_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'website' => ['nullable', 'url', 'max:150'],
            'beds' => ['nullable', 'integer', 'min:0'],
            'emergency_facility' => ['boolean'],
            'departments' => ['nullable', 'string', 'max:255'],
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'construction_year' => ['nullable', 'integer', 'between:1900,' . (date('Y') + 1)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This hospital code is already in use.',
            'emergency_phone.regex' => 'Emergency phone must be a valid phone number.',
            'image.max' => 'Hospital image must not exceed 3 MB.',
        ];
    }
}
