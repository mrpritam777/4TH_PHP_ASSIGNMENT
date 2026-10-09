<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:150'],
            'designation' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{10,15}$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_type' => ['required', Rule::in(['emergency', 'government', 'maintenance', 'hospital', 'contractor', 'other'])],
            'infrastructure_id' => ['nullable', 'exists:infrastructures,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'Phone number must be valid (10-15 digits).',
            'contact_type.in' => 'Please select a valid contact type.',
        ];
    }
}
