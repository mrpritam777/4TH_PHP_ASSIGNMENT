@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
    <x-page-header title="Notifications" subtitle="{{ $unreadCount }} unread notification(s).">
        @if ($unreadCount > 0)
            <form action="{{ route('notifications.mark-all') }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-check-all"></i> Mark all as read</button>
            </form>
        @endif
    </x-page-header>

    @php
        $typeIcons = [
            'info' => 'bi-info-circle',
            'tender_published' => 'bi-megaphone',
            'bid_submitted' => 'bi-cash-coin',
            'tender_awarded' => 'bi-trophy',
            'contractor_applied' => 'bi-person-plus',
            'contractor_approved' => 'bi-person-check',
            'contractor_rejected' => 'bi-person-x',
            'cancellation_requested' => 'bi-x-octagon',
            'cancellation_approved' => 'bi-check-circle',
            'cancellation_rejected' => 'bi-arrow-counterclockwise',
        ];
    @endphp

    <div class="bc-card mb-4">
        <div class="bc-card-body flush">
            @forelse ($notifications as $note)
                <div class="d-flex align-items-start gap-3" style="padding:16px 22px; border-bottom:1px solid var(--bc-border); {{ $note->read ? '' : 'background:#f0f7ff;' }}">
                    <div style="width:40px;height:40px;border-radius:12px;flex-shrink:0;display:inline-flex;align-items:center;justify-content:center; background:{{ $note->read ? 'var(--bc-gray-bg)' : 'var(--bc-blue-light)' }}; color:{{ $note->read ? '#64748b' : 'var(--bc-blue)' }};">
                        <i class="bi {{ $typeIcons[$note->type] ?? 'bi-bell' }}"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <strong style="font-size:13.5px;">{{ $note->title }}</strong>
                            @unless ($note->read)
                                <span class="bc-badge badge-blue">New</span>
                            @endunless
                        </div>
                        @if ($note->message)
                            <p style="font-size:13px;color:#475569;margin:4px 0 0;line-height:1.6;">{{ $note->message }}</p>
                        @endif
                        <small style="color:#94a3b8;font-size:12px;">{{ $note->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        @if ($note->link)
                            <a href="{{ route('notifications.read', $note) }}" class="btn-bc btn-bc-outline btn-bc-sm"><i class="bi bi-box-arrow-up-right"></i> Open</a>
                        @elseif (! $note->read)
                            <form action="{{ route('notifications.update', $note) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-bc btn-bc-ghost btn-bc-sm" title="Mark as read"><i class="bi bi-check-lg"></i></button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state icon="bi-bell-slash" title="No notifications"
                               text="Updates about tenders, bids, contractor applications and cancellation requests will appear here." />
            @endforelse
        </div>
    </div>

    <x-pagination :paginator="$notifications" />
@endsection
