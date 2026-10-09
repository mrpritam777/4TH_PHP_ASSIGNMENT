<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Models\ActivityLog;
use App\Models\Contact;
use App\Models\Infrastructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $contacts = Contact::with('infrastructure.type')
            ->search($search)
            ->when($type, fn ($q) => $q->where('contact_type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.contacts.index', compact('contacts', 'search', 'type'));
    }

    public function create(): View
    {
        $infrastructures = Infrastructure::with('type')->orderBy('name')->get();

        return view('admin.contacts.form', ['infrastructures' => $infrastructures, 'contact' => null]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $contact = Contact::create($data);

        ActivityLog::record('created', 'Contact', $contact->id, "Admin added contact: {$contact->name}");

        return redirect()->route('admin.contacts.index')->with('success', "Contact \"{$contact->name}\" was added successfully.");
    }

    public function edit(Contact $contact): View
    {
        $infrastructures = Infrastructure::with('type')->orderBy('name')->get();

        return view('admin.contacts.form', compact('infrastructures', 'contact'));
    }

    public function update(ContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        ActivityLog::record('updated', 'Contact', $contact->id, "Admin updated contact: {$contact->name}");

        return redirect()->route('admin.contacts.index')->with('success', "Contact \"{$contact->name}\" was updated successfully.");
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $name = $contact->name;
        $contact->delete();

        ActivityLog::record('deleted', 'Contact', $contact->id, "Admin deleted contact: {$name}");

        return redirect()->route('admin.contacts.index')->with('success', "Contact \"{$name}\" was deleted.");
    }
}
