@props(['status'])
@php
    $class = match ($status) {
        'pending' => 'badge-yellow',
        'under_review' => 'badge-purple',
        'assigned' => 'badge-blue',
        'in_progress' => 'badge-blue',
        'on_hold' => 'badge-orange',
        'completed' => 'badge-green',
        'cancelled' => 'badge-red',
        'draft' => 'badge-gray',
        'published' => 'badge-blue',
        'closed' => 'badge-orange',
        'awarded' => 'badge-green',
        'approved' => 'badge-green',
        'rejected' => 'badge-red',
        'submitted' => 'badge-blue',
        'active' => 'badge-green',
        'inactive' => 'badge-gray',
        default => 'badge-gray',
    };
    $label = str((string) $status)->replace('_', ' ')->title();
@endphp
<span class="bc-badge {{ $class }}"><span class="dot"></span>{{ $label }}</span>

