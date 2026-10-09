<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AppNotification;
use App\Models\Contractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContractorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $contractors = Contractor::with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('company_name', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $statusCounts = Contractor::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.contractors.index', compact('contractors', 'search', 'status', 'statusCounts'));
    }

    public function show(Contractor $contractor): View
    {
        $contractor->load(['user', 'reviewer', 'bids.tender']);
        $categories = $contractor->categoryNames();

        return view('admin.contractors.show', compact('contractor', 'categories'));
    }

    /**
     * Approve an application and activate the contractor account.
     * Runs inside a transaction together with the account activation.
     */
    public function approve(Request $request, Contractor $contractor): RedirectResponse
    {
        abort_unless($contractor->status === Contractor::STATUS_PENDING, 403, 'This application has already been reviewed.');

        DB::transaction(function () use ($contractor, $request) {
            $contractor->update([
                'status' => Contractor::STATUS_APPROVED,
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $contractor->user()->update(['status' => 'active']);
        });

        if ($contractor->user_id) {
            AppNotification::send(
                $contractor->user_id,
                'Application approved',
                "Congratulations! Your contractor application for \"{$contractor->company_name}\" has been approved. You can now browse tenders and submit bids.",
                'contractor_approved',
                route('contractor.dashboard')
            );
        }

        ActivityLog::record('approved', 'Contractor', $contractor->id, "Admin approved contractor: {$contractor->company_name}");

        return back()->with('success', "Contractor \"{$contractor->company_name}\" approved — the account is now active.");
    }

    public function reject(Request $request, Contractor $contractor): RedirectResponse
    {
        abort_unless($contractor->status === Contractor::STATUS_PENDING, 403, 'This application has already been reviewed.');

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [], ['reason' => 'rejection reason']);

        DB::transaction(function () use ($contractor, $data, $request) {
            $contractor->update([
                'status' => Contractor::STATUS_REJECTED,
                'rejection_reason' => $data['reason'],
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $contractor->user()->update(['status' => 'inactive']);
        });

        if ($contractor->user_id) {
            AppNotification::send(
                $contractor->user_id,
                'Application rejected',
                "Your contractor application for \"{$contractor->company_name}\" was not approved. Reason: {$data['reason']}",
                'contractor_rejected',
                route('contractor.dashboard')
            );
        }

        ActivityLog::record('rejected', 'Contractor', $contractor->id, "Admin rejected contractor: {$contractor->company_name}");

        return back()->with('success', "Application rejected — \"{$contractor->company_name}\" has been notified with the reason.");
    }

    /**
     * Deactivate / reactivate an already approved contractor account.
     * Deactivation blocks all protected contractor features server-side.
     */
    public function toggleStatus(Contractor $contractor): RedirectResponse
    {
        abort_unless($contractor->status === Contractor::STATUS_APPROVED, 403, 'Only approved contractors can be activated or deactivated here.');

        $newStatus = $contractor->user->isActive() ? 'inactive' : 'active';
        $contractor->user()->update(['status' => $newStatus]);

        ActivityLog::record('updated', 'Contractor', $contractor->id, "Admin ".($newStatus === 'active' ? 'activated' : 'deactivated')." contractor: {$contractor->company_name}");

        $verb = $newStatus === 'active' ? 'activated' : 'deactivated';

        return back()->with('success', "Contractor account {$verb}.");
    }

    /**
     * Stream a private verification document — these files are stored outside
     * the public disk and are never reachable by direct URL.
     */
    public function document(Contractor $contractor, string $field)
    {
        abort_unless(in_array($field, ['id_document', 'business_document'], true), 404);

        $path = $contractor->{$field};

        abort_unless($path, 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
    }
}