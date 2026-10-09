<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Contractor;
use App\Models\InfrastructureType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContractorRegistrationController extends Controller
{
    public function create(): View
    {
        return view('public.contractor-register', [
            'categories' => InfrastructureType::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Register a contractor with a Pending Approval application. Documents are
     * stored on the private disk — never reachable through a public URL.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'phone' => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'address' => ['required', 'string', 'min:10', 'max:300'],
            'company_name' => ['required', 'string', 'min:2', 'max:150'],
            'business_registration_no' => ['nullable', 'string', 'max:100'],
            'work_categories' => ['required', 'array', 'min:1'],
            'work_categories.*' => ['integer', Rule::exists('infrastructure_types', 'id')->where('status', 'active')],
            'experience' => ['required', 'string', 'min:20', 'max:3000'],
            'licenses' => ['required', 'string', 'min:5', 'max:2000'],
            'id_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'business_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'work_categories.required' => 'Please select at least one infrastructure work category.',
            'phone.regex' => 'The mobile number may only contain digits, spaces and + - ( ) characters.',
            'id_document.mimes' => 'The identity document must be a PDF, JPG or PNG file.',
            'business_document.mimes' => 'The business verification document must be a PDF, JPG or PNG file.',
            'id_document.max' => 'The identity document may not be larger than 5 MB.',
            'business_document.max' => 'The business verification document may not be larger than 5 MB.',
        ], [
            'name' => 'full name',
            'phone' => 'mobile number',
            'address' => 'address',
            'company_name' => 'company or business name',
            'business_registration_no' => 'business registration details',
            'work_categories' => 'infrastructure work categories',
            'experience' => 'previous work experience',
            'licenses' => 'licenses and certifications',
            'id_document' => 'identity verification document',
            'business_document' => 'business verification document',
            'password' => 'password',
        ]);

        $user = null;
        $contractor = null;

        DB::transaction(function () use ($request, $data, &$user, &$contractor) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => 'contractor',
                'status' => 'inactive', // activated only after admin approval
            ]);

            $contractor = Contractor::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'address' => $data['address'],
                'business_registration_no' => $data['business_registration_no'] ?? null,
                'work_categories' => array_map('intval', $data['work_categories']),
                'experience' => $data['experience'],
                'licenses' => $data['licenses'],
                'id_document' => $request->file('id_document')->store("contractors/{$user->id}", 'local'),
                'business_document' => $request->file('business_document')->store("contractors/{$user->id}", 'local'),
                'status' => Contractor::STATUS_PENDING,
            ]);
        });

        AppNotification::sendToUsers(
            AppNotification::adminIds(),
            'New contractor application',
            "{$contractor->company_name} ({$user->name}) submitted a contractor application and is awaiting review.",
            'contractor_applied',
            route('admin.contractors.show', $contractor)
        );

        ActivityLog::record('created', 'Contractor', $contractor->id, "New contractor application: {$contractor->company_name}", $request->ip());

        return redirect()->route('login')->with('success', 'Your contractor application has been submitted! Your account will be activated once an administrator approves it — you can sign in to check the status at any time.');
    }
}