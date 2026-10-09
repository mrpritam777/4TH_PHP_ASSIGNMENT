<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceRequestForm extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'infrastructure_id' => ['required', 'exists:infrastructures,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('role', 'staff')->where('status', 'active')],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'expected_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'before_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'after_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
        ];
    }

    public function messages(): array
    {
        return [
            'infrastructure_id.exists' => 'Please select a valid infrastructure.',
            'expected_completion_date.after_or_equal' => 'Expected completion must be on or after the start date.',
            'before_image.max' => 'Before image must not exceed 3 MB.',
            'after_image.max' => 'After image must not exceed 3 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'infrastructure_id' => 'infrastructure',
            'title' => 'problem title',
            'priority' => 'priority',
        ];
    }
}
