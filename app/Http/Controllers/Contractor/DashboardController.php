<?php

namespace App\Http\Controllers\Contractor;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Tender;
use App\Models\TenderBid;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $contractor = $user->contractor;

        if (! $contractor || ! $contractor->isApproved()) {
            $pendingBids = TenderBid::whereHas('contractor', fn ($q) => $q->where('user_id', $user->id))->count();

            return view('contractor.dashboard', [
                'contractor' => $contractor,
                'stats' => null,
                'openTenders' => collect(),
                'recentBids' => collect(),
                'pendingCancellations' => collect(),
                'pendingBids' => $pendingBids,
            ]);
        }

        $stats = [
            'open_tenders' => Tender::published()->where('deadline', '>', now())->count(),
            'my_bids' => $contractor->bids()->count(),
            'awarded' => $contractor->awardedTenders()->count(),
            'unread_notifications' => AppNotification::forUser($user->id)->unread()->count(),
        ];

        $categoryIds = is_array($contractor->work_categories) ? $contractor->work_categories : [];

        $openTenders = Tender::with('type')
            ->published()
            ->where('deadline', '>', now())
            ->orderBy('deadline')
            ->take(5)
            ->get();

        $recentBids = $contractor->bids()
            ->with('tender.type')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        $pendingCancellations = Tender::whereIn('status', Tender::CANCELLABLE_STATUSES)
            ->whereHas('cancellationRequests', fn ($q) => $q->where('status', 'pending'))
            ->where(function ($q) use ($contractor) {
                $q->where('awarded_contractor_id', $contractor->id)
                    ->orWhereHas('bids', fn ($b) => $b->where('contractor_id', $contractor->id));
            })
            ->get();

        return view('contractor.dashboard', compact('contractor', 'stats', 'openTenders', 'recentBids', 'pendingCancellations') + [
            'categoryIds' => $categoryIds,
        ]);
    }
}