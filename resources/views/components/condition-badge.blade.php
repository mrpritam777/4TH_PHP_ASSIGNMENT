@props(['condition'])
@php
    $class = match ($condition) {
        'excellent', 'good' => 'badge-green',
        'fair' => 'badge-yellow',
        'poor' => 'badge-orange',
        'critical' => 'badge-red',
        default => 'badge-gray',
    };
@endphp
<span class="bc-badge {{ $class }}"><span class="dot"></span>{{ ucfirst($condition) }}</span>
