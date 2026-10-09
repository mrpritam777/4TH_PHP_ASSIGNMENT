<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequestForm;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status') ?? (request()->routeIs('admin.maintenance.history') ? 'completed' : null);
        $priority = $request->input('priority');

        $requests = MaintenanceRequest::with(['infrastructure.type', 'reporter', 'assignedStaff'])
            ->search($search)
            ->status($status)
            ->priority($priority)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'all' => MaintenanceRequest::count(),
            'pending' => MaintenanceRequest::whereIn('status', ['pending', 'under_review'])->count(),
            'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
            'completed' => MaintenanceRequest::where('status', 'completed')->count(),
        ];

        return view('admin.maintenance.index', compact('requests', 'search', 'status', 'priority', 'counts'));
    }

    public function create(Request $request): View
    {
        $infrastructures = Infrastructure::with('type')->orderBy('name')->get()->groupBy(fn ($i) => $i->type->name);
        $staff = User::where('role', 'staff')->where('status', 'active')->orderBy('name')->get();

        $preselected = $request->integer('infrastructure_id') ?: null;

        return view('admin.maintenance.form', compact('infrastructures', 'staff', 'preselected'));
    }

    public function store(MaintenanceRequestForm $request): RedirectResponse
    {
        $data = $request->validated();

        $requestNumber = $this->generateRequestNumber($data['infrastructure_id']);

        $maintenance = MaintenanceRequest::create([
            ...$data,
            'request_number' => $requestNumber,
            'reported_by' => auth()->id(),
            'status' => $data['assigned_to'] ? 'assigned' : 'pending',
            'before_image' => $request->hasFile('before_image') ? $request->file('before_image')->store('maintenance/before', 'public') : null,
            'after_image' => $request->hasFile('after_image') ? $request->file('after_image')->store('maintenance/after', 'public') : null,
        ]);

        ActivityLog::record('created', 'Maintenance', $maintenance->id, "Admin created maintenance request {$requestNumber} for {$maintenance->infrastructure->name}");

        AppNotification::create([
            'title' => 'New maintenance request',
            'message' => "Request {$requestNumber} ({$maintenance->title}) was created for {$maintenance->infrastructure->name}.",
            'type' => 'maintenance_request',
            'link' => route('admin.maintenance.show', $maintenance),
        ]);

        if ($maintenance->assigned_to) {
            AppNotification::create([
                'user_id' => $maintenance->assigned_to,
                'title' => 'New task assigned',
                'message' => "You have been assigned task {$requestNumber}: {$maintenance->title}.",
                'type' => 'task_assigned',
                'link' => route('staff.tasks.show', $maintenance),
            ]);
        }

        return redirect()->route('admin.maintenance.show', $maintenance)->with('success', "Maintenance request {$requestNumber} was created successfully.");
    }

    public function show(MaintenanceRequest $maintenance): View
    {
        $maintenance->load(['infrastructure.type', 'reporter', 'assignedStaff', 'updates.updater']);

        $timeline = $maintenance->updates()->with('updater')->latest()->get();

        return view('admin.maintenance.show', compact('maintenance', 'timeline'));
    }

    public function edit(MaintenanceRequest $maintenance): View
    {
        $infrastructures = Infrastructure::with('type')->orderBy('name')->get()->groupBy(fn ($i) => $i->type->name);
        $staff = User::where('role', 'staff')->where('status', 'active')->orderBy('name')->get();

        return view('admin.maintenance.form', compact('infrastructures', 'staff', 'maintenance'));
    }

    public function update(MaintenanceRequestForm $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $data = $request->validated();

        $maintenance->update([
            ...$data,
            'before_image' => $request->hasFile('before_image') ? $request->file('before_image')->store('maintenance/before', 'public') : $maintenance->before_image,
            'after_image' => $request->hasFile('after_image') ? $request->file('after_image')->store('maintenance/after', 'public') : $maintenance->after_image,
        ]);

        ActivityLog::record('updated', 'Maintenance', $maintenance->id, "Admin updated maintenance request {$maintenance->request_number}");

        return redirect()->route('admin.maintenance.show', $maintenance)->with('success', 'Maintenance request was updated successfully.');
    }

    public function assign(Request $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'exists:users,id'],
        ]);

        $staff = User::findOrFail($data['assigned_to']);

        $maintenance->update([
            'assigned_to' => $staff->id,
            'status' => $maintenance->status === 'pending' ? 'assigned' : $maintenance->status,
        ]);

        ActivityLog::record('assigned', 'Maintenance', $maintenance->id, "Admin assigned {$maintenance->request_number} to {$staff->name}");

        AppNotification::create([
            'user_id' => $staff->id,
            'title' => 'New task assigned',
            'message' => "You have been assigned task {$maintenance->request_number}: {$maintenance->title}.",
            'type' => 'task_assigned',
            'link' => route('staff.tasks.show', $maintenance),
        ]);

        return back()->with('success', "Task assigned to {$staff->name}.");
    }

    public function addUpdate(\App\Http\Requests\MaintenanceUpdateForm $request, MaintenanceRequest $maintenance): RedirectResponse
    {
        $data = $request->validated();

        $maintenance->update([
            'status' => $data['status'] ?? $maintenance->status,
            'actual_cost' => $data['actual_cost'] ?? $maintenance->actual_cost,
            'completion_date' => ($data['status'] ?? null) === 'completed' ? now()->toDateString() : $maintenance->completion_date,
        ]);

        $maintenance->updates()->create([
            'updated_by' => auth()->id(),
            'status' => $maintenance->status,
            'notes' => $data['notes'] ?? null,
            'image' => $request->hasFile('image') ? $request->file('image')->store('maintenance/updates', 'public') : null,
        ]);

        ActivityLog::record('updated', 'Maintenance', $maintenance->id, "Admin updated {$maintenance->request_number} to {$maintenance->status}");

        if ($maintenance->status === 'completed') {
            AppNotification::create([
                'title' => 'Maintenance completed',
                'message' => "Task {$maintenance->request_number} ({$maintenance->title}) has been completed.",
                'type' => 'maintenance_completed',
                'link' => route('admin.maintenance.show', $maintenance),
            ]);
        }

        return back()->with('success', 'Progress update was added successfully.');
    }

    public function destroy(MaintenanceRequest $maintenance): RedirectResponse
    {
        $number = $maintenance->request_number;
        $maintenance->delete();

        ActivityLog::record('deleted', 'Maintenance', $maintenance->id, "Admin deleted maintenance request {$number}");

        return redirect()->route('admin.maintenance.index')->with('success', "Maintenance request {$number} was deleted.");
    }

    protected function generateRequestNumber(int $infrastructureId): string
    {
        $type = Infrastructure::find($infrastructureId)->type;
        $prefix = strtoupper(substr($type->slug, 0, 3));
        $year = now()->year;

        $last = MaintenanceRequest::where('request_number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('id')
            ->value('request_number');

        $sequence = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
