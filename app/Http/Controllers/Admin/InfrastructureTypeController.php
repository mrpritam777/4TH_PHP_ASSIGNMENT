<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InfrastructureType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfrastructureTypeController extends Controller
{
    /**
     * "All Infrastructure → Add More" management page. Categories are stored
     * in the database so new types never require a code change.
     */
    public function index(): View
    {
        $types = InfrastructureType::withCount(['infrastructures', 'tenders'])
            ->orderBy('name')
            ->get();

        $editing = request()->query('edit');
        $editType = $editing ? InfrastructureType::find($editing) : null;

        return view('admin.types.index', compact('types', 'editType'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateType($request);

        $slug = Str::slug($data['name']);

        if (InfrastructureType::where('slug', $slug)->exists()) {
            return back()->withInput()->with('error', 'A category with this name already exists.');
        }

        InfrastructureType::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        ActivityLog::record('created', 'Infrastructure Type', null, "Admin created category: {$data['name']}");

        return back()->with('success', "Category \"{$data['name']}\" was added — it now appears under All Infrastructure.");
    }

    public function update(Request $request, InfrastructureType $type): RedirectResponse
    {
        $data = $this->validateType($request);

        $slug = Str::slug($data['name']);

        if ($slug !== $type->slug && InfrastructureType::where('slug', $slug)->exists()) {
            return back()->withInput()->with('error', 'A category with this name already exists.');
        }

        $type->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        ActivityLog::record('updated', 'Infrastructure Type', $type->id, "Admin updated category: {$type->name}");

        return redirect()->route('admin.types.index')->with('success', "Category \"{$type->name}\" was updated.");
    }

    public function toggleStatus(InfrastructureType $type): RedirectResponse
    {
        $type->update(['status' => $type->isActive() ? 'inactive' : 'active']);

        ActivityLog::record('updated', 'Infrastructure Type', $type->id, "Admin ".($type->isActive() ? 'activated' : 'deactivated')." category: {$type->name}");

        return back()->with('success', "Category \"{$type->name}\" is now {$type->status}.");
    }

    /**
     * Categories referenced by existing assets or tenders are protected —
     * reassign those records first, then delete safely.
     */
    public function destroy(InfrastructureType $type): RedirectResponse
    {
        if (! $type->deletable()) {
            return back()->with('error', "\"{$type->name}\" is still used by {$type->blockingReferences()} record(s) (infrastructure assets or tenders). Reassign or remove them first, or deactivate the category instead.");
        }

        $name = $type->name;
        $type->delete();

        ActivityLog::record('deleted', 'Infrastructure Type', null, "Admin deleted category: {$name}");

        return back()->with('success', "Category \"{$name}\" was deleted.");
    }

    private function validateType(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ], [], ['name' => 'category name']);
    }
}