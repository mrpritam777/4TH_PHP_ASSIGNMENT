<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BridgeRequest extends FormRequest
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
            'bridge_type' => ['required', 'string', 'max:100'],
            'bridge_number' => ['nullable', 'string', 'max:50'],
            'state' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'construction_year' => ['nullable', 'integer', 'between:1900,' . (date('Y') + 1)],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'lanes' => ['nullable', 'integer', 'min:1', 'max:12'],
            'material' => ['nullable', 'string', 'max:100'],
            'load_capacity' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'structural_condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'surface_condition' => ['required', Rule::in(['excellent', 'good', 'fair', 'poor', 'critical'])],
            'safety_status' => ['required', Rule::in(['safe', 'caution', 'unsafe'])],
            'last_inspection_date' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This bridge code is already in use.',
            'image.max' => 'Bridge image must not exceed 3 MB.',
            'image.mimes' => 'Bridge image must be a JPG, PNG or WEBP file.',
            'gallery.*.max' => 'Each gallery image must not exceed 3 MB.',
            'construction_year.between' => 'Construction year must be between 1900 and next year.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'bridge name',
            'code' => 'bridge code',
            'bridge_type' => 'bridge type',
            'condition' => 'current condition',
        ];
    }
}
