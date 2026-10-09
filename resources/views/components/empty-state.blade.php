@props(['icon' => 'bi-inbox', 'title' => 'Nothing here yet', 'text' => 'There is no data to display in this section right now.', 'actionLabel' => null, 'actionUrl' => null])
<div class="bc-empty">
    <i class="bi {{ $icon }}"></i>
    <h4>{{ $title }}</h4>
    <p>{{ $text }}</p>
    @if ($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn-bc btn-bc-primary btn-bc-sm mt-3"><i class="bi bi-plus-lg"></i> {{ $actionLabel }}</a>
    @endif
</div>
