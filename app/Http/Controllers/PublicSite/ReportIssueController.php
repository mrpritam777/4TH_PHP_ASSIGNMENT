<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportIssueRequest;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Infrastructure;
use App\Models\MaintenanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportIssueController extends Controller
{
    public function create(): View
    {
        $infrastructures = Infrastructure::with('type')->orderBy('name')->get()->groupBy(fn ($i) => $i->type->name);

        return view('public.report-issue', compact('infrastructures'));
    }

    public function store(ReportIssueRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $infrastructure = Infrastructure::find($data['infrastructure_id']);

        $requestNumber = $this->generateRequestNumber($infrastructure->type->slug);

        MaintenanceRequest::create([
            'request_number' => $requestNumber,
            'infrastructure_id' => $infrastructure->id,
            'title' => $data['title'],
            'description' => $data['description'] . ($data['location'] ? "\n\nReported location: {$data['location']}" : ''),
            'priority' => 'medium',
            'status' => 'pending',
            'before_image' => $request->hasFile('image') ? $request->file('image')->store('maintenance/before', 'public') : null,
        ]);

        ActivityLog::record('created', 'Maintenance', null, "Public user reported an issue on {$infrastructure->name} ({$requestNumber})");

        AppNotification::create([
            'title' => 'New maintenance request',
            'message' => "Public report {$requestNumber} received for {$infrastructure->name}: {$data['title']}",
            'type' => 'maintenance_request',
            'link' => route('admin.maintenance.index'),
        ]);

        return redirect()
            ->route('public.track', $requestNumber)
            ->with('success', 'Your issue has been submitted successfully.');
    }

    protected function generateRequestNumber(string $slug): string
    {
        $prefix = strtoupper(substr($slug, 0, 3));
        $year = now()->year;

        $last = MaintenanceRequest::where('request_number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('id')
            ->value('request_number');

        $sequence = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }
}
