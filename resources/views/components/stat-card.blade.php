@props(['icon', 'iconClass' => 'ic-blue', 'value', 'label', 'trend' => null, 'trendClass' => 'trend-flat'])
<div class="bc-stat-card">
    <div class="bc-stat-icon {{ $iconClass }}"><i class="bi {{ $icon }}"></i></div>
    <div class="bc-stat-info">
        <div class="bc-stat-value">{{ $value }}</div>
        <div class="bc-stat-label">{{ $label }}</div>
        @if ($trend)
            <span class="bc-stat-trend {{ $trendClass }}">{{ $trend }}</span>
        @endif
    </div>
</div>
