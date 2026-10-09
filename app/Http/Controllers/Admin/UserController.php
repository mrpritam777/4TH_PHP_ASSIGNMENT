<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        return $this->list($request, 'all');
    }

    public function admins(Request $request): View
    {
        return $this->list($request, 'admin');
    }

    public function staff(Request $request): View
    {
        return $this->list($request, 'staff');
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
            'status' => $data['status'],
            'profile_image' => $request->hasFile('profile_image') ? $request->file('profile_image')->store('avatars', 'public') : null,
        ]);

        ActivityLog::record('created', 'User', $user->id, "Admin added user ({$user->role}): {$user->name}");

        return redirect()->route('admin.users.index')->with('success', "User \"{$user->name}\" was created successfully.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if ($user->id === auth()->id() && $data['role'] !== 'admin') {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'status' => $data['status'],
            'profile_image' => $request->hasFile('profile_image') ? $request->file('profile_image')->store('avatars', 'public') : $user->profile_image,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => $data['password']]);
        }

        ActivityLog::record('updated', 'User', $user->id, "Admin updated user: {$user->name}");

        return redirect()->route('admin.users.index')->with('success', "User \"{$user->name}\" was updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot delete your own account.');

        $name = $user->name;
        $user->delete();

        ActivityLog::record('deleted', 'User', $user->id, "Admin deleted user: {$name}");

        return redirect()->route('admin.users.index')->with('success', "User \"{$name}\" was deleted.");
    }

    protected function list(Request $request, string $role): View
    {
        $search = $request->input('search');

        $users = User::query()
            ->when($role !== 'all', fn ($q) => $q->where('role', $role))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->withCount('assignedRequests')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }
}
