<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Full notification centre for every authenticated role.
     */
    public function index(Request $request): View
    {
        $notifications = AppNotification::forUser($request->user()->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unreadCount = AppNotification::forUser($request->user()->id)->unread()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a notification as read and continue to its target — used by the
     * topbar bell dropdown so links behave like normal anchors.
     */
    public function read(Request $request, AppNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);

        if (! $notification->read) {
            $notification->markAsRead();
        }

        return redirect($notification->link ?? route('notifications.index'));
    }

    public function update(Request $request, AppNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAll(Request $request): RedirectResponse
    {
        AppNotification::forUser($request->user()->id)->unread()->update(['read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    private function authorizeNotification(Request $request, AppNotification $notification): void
    {
        abort_unless(
            $notification->user_id === null || (int) $notification->user_id === (int) $request->user()->id,
            403
        );
    }
}