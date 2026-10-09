@props(['priority'])
@php
    $class = match ($priority) {
        'low' => 'badge-gray',
        'medium' => 'badge-blue',
        'high' => 'badge-orange',
        'critical' => 'badge-red',
        default => 'badge-gray',
    };
    $icon = match ($priority) {
        'critical' => 'bi-exclamation-octagon-fill',
        'high' => 'bi-arrow-up-circle-fill',
        'medium' => 'bi-circle-fill',
        default => 'bi-circle-fill',
    };
@endphp
<span class="bc-badge {{ $class }}"><i class="bi {{ $icon }}" style="font-size:8px;"></i> {{ ucfirst($priority) }}</span>
