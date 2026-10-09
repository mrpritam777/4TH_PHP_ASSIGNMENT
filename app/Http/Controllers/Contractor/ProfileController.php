<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InfrastructureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $contractor = $request->user()->contractor;

        return view('contractor.profile.show', [
            'contractor' => $contractor,
            'categories' => InfrastructureType::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $contractor = $request->user()->contractor;
        abort_unless($contractor, 404);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'min:2', 'max:150'],
            'phone' => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'address' => ['required', 'string', 'min:10', 'max:300'],
            'business_registration_no' => ['nullable', 'string', 'max:100'],
            'work_categories' => ['required', 'array', 'min:1'],
            'work_categories.*' => ['integer', 'exists:infrastructure_types,id'],
            'experience' => ['required', 'string', 'min:20', 'max:3000'],
            'licenses' => ['required', 'string', 'min:5', 'max:2000'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'phone.regex' => 'The mobile number may only contain digits, spaces and + - ( ) characters.',
            'work_categories.required' => 'Please select at least one infrastructure work category.',
        ], [
            'company_name' => 'company or business name',
            'phone' => 'mobile number',
            'address' => 'address',
            'work_categories' => 'infrastructure work categories',
            'experience' => 'previous work experience',
            'licenses' => 'licenses and certifications',
        ]);

        $contractor->update([
            'company_name' => $data['company_name'],
            'address' => $data['address'],
            'business_registration_no' => $data['business_registration_no'] ?? null,
            'work_categories' => array_map('intval', $data['work_categories']),
            'experience' => $data['experience'],
            'licenses' => $data['licenses'],
        ]);

        $request->user()->update(['phone' => $data['phone']]);

        if (! empty($data['password'])) {
            $request->user()->update(['password' => $data['password']]);
        }

        ActivityLog::record('updated', 'Contractor', $contractor->id, "Contractor updated own profile: {$contractor->company_name}");

        return redirect()->route('contractor.profile.show')->with('success', 'Your profile has been updated.');
    }

    /**
     * Stream the contractor's own verification documents (private disk).
     */
    public function document(Request $request, string $field)
    {
        abort_unless(in_array($field, ['id_document', 'business_document'], true), 404);

        $contractor = $request->user()->contractor;
        abort_unless($contractor, 404);

        $path = $contractor->{$field};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
    }
}