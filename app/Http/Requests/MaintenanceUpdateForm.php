<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceUpdateForm extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $request = $this->route('maintenance') ?? $this->route('task') ?? $this->route('request');

        if ($user->isAdmin()) {
            return true;
        }

        return $request && $request->assigned_to === $user->id
            && ! in_array($request->status, ['completed', 'cancelled']);
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['pending', 'under_review', 'assigned', 'in_progress', 'on_hold', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3048'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
