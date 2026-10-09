@props(['date', 'title', 'text' => null, 'status' => null, 'icon' => 'bi-dot', 'dotClass' => ''])
<div class="bc-timeline-item">
    <div class="bc-timeline-dot {{ $dotClass }}"><i class="bi {{ $icon }}"></i></div>
    <div class="bc-timeline-card">
        <time>{{ $date }}</time>
        <h4 class="d-flex align-items-center gap-2 flex-wrap">
            {{ $title }}
            @if ($status)
                <x-status-badge :status="$status" />
            @endif
        </h4>
        @if ($text)
            <p>{{ $text }}</p>
        @endif
    </div>
</div>
