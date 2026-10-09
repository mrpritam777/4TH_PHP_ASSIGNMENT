<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceStaffController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');

        $staff = User::where('role', 'staff')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->withCount('assignedRequests')
            ->withCount(['activeTasks' => fn ($q) => $q->whereIn('status', ['assigned', 'in_progress'])])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.staff.index', compact('staff', 'search'));
    }

    public function create(): View
    {
        return view('admin.staff.form', ['user' => null]);
    }

    public function store(\App\Http\Requests\UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $staff = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => 'staff',
            'status' => $data['status'],
            'profile_image' => $request->hasFile('profile_image') ? $request->file('profile_image')->store('avatars', 'public') : null,
        ]);

        ActivityLog::record('created', 'Staff', $staff->id, "Admin added maintenance staff: {$staff->name}");

        return redirect()->route('admin.staff.index')->with('success', "Staff \"{$staff->name}\" was added successfully.");
    }

    public function edit(User $staff): View
    {
        abort_unless($staff->role === 'staff', 404);

        return view('admin.staff.form', ['user' => $staff]);
    }

    public function update(\App\Http\Requests\UserRequest $request, User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $data = $request->validated();

        $staff->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'profile_image' => $request->hasFile('profile_image') ? $request->file('profile_image')->store('avatars', 'public') : $staff->profile_image,
        ]);

        if (! empty($data['password'])) {
            $staff->update(['password' => $data['password']]);
        }

        ActivityLog::record('updated', 'Staff', $staff->id, "Admin updated staff: {$staff->name}");

        return redirect()->route('admin.staff.index')->with('success', "Staff \"{$staff->name}\" was updated successfully.");
    }

    public function toggleStatus(User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);

        $staff->update(['status' => $staff->isActive() ? 'inactive' : 'active']);

        $verb = $staff->status === 'active' ? 'activated' : 'deactivated';
        ActivityLog::record('updated', 'Staff', $staff->id, "Admin {$verb} staff: {$staff->name}");

        return back()->with('success', "Staff status updated.");
    }

    public function destroy(User $staff): RedirectResponse
    {
        abort_unless($staff->role === 'staff', 404);
        abort_if($staff->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = $staff->name;
        $staff->delete();

        ActivityLog::record('deleted', 'Staff', $staff->id, "Admin deleted staff: {$name}");

        return redirect()->route('admin.staff.index')->with('success', "Staff \"{$name}\" was deleted.");
    }
}
