<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceUpdateForm;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\MaintenanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $search = $request->input('search');

        $tasks = MaintenanceRequest::with('infrastructure.type')
            ->where('assigned_to', auth()->id())
            ->when($search, fn ($q) => $q->search($search))
            ->status($status)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $counts = MaintenanceRequest::where('assigned_to', auth()->id())
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('staff.tasks', compact('tasks', 'status', 'counts', 'search'));
    }

    public function show(MaintenanceRequest $task): View
    {
        $this->authorizeTask($task);

        $task->load(['infrastructure.type', 'assignedStaff', 'updates.updater']);

        $timeline = $task->updates()->with('updater')->latest()->get();

        return view('staff.task-show', compact('task', 'timeline'));
    }

    public function update(MaintenanceUpdateForm $request, MaintenanceRequest $task): RedirectResponse
    {
        $this->authorizeTask($task);

        $data = $request->validated();

        $task->update([
            'status' => $data['status'] ?? $task->status,
            'actual_cost' => $data['actual_cost'] ?? $task->actual_cost,
            'completion_date' => ($data['status'] ?? null) === 'completed' ? now()->toDateString() : $task->completion_date,
        ]);

        $task->updates()->create([
            'updated_by' => auth()->id(),
            'status' => $task->status,
            'notes' => $data['notes'] ?? null,
            'image' => $request->hasFile('image') ? $request->file('image')->store('maintenance/updates', 'public') : null,
        ]);

        ActivityLog::record('updated', 'Maintenance', $task->id, "Staff updated status of {$task->request_number} to {$task->status}");

        if ($task->status === 'completed') {
            AppNotification::create([
                'title' => 'Maintenance completed',
                'message' => "Task {$task->request_number} ({$task->title}) has been completed.",
                'type' => 'maintenance_completed',
                'link' => route('admin.maintenance.show', $task),
            ]);
        }

        return back()->with('success', 'Task progress was updated successfully.');
    }

    protected function authorizeTask(MaintenanceRequest $task): void
    {
        $user = auth()->user();

        if (! $user || ! ($user->isAdmin() || $task->assigned_to === $user->id)) {
            abort(403, 'You are not authorized to view this task.');
        }
    }
}
