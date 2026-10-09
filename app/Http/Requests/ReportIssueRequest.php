<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'infrastructure_type' => ['required', Rule::in(['bridge', 'road', 'building', 'hospital'])],
            'infrastructure_id' => ['required', 'exists:infrastructures,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:3000'],
            'location' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'reporter_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{10,15}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'infrastructure_id.exists' => 'Please select a valid infrastructure.',
            'phone.regex' => 'Phone number must be valid (10-15 digits).',
            'image.max' => 'Image must not exceed 3 MB.',
        ];
    }
}
