@props(['before', 'after', 'beforeLabel' => 'Before', 'afterLabel' => 'After'])
<div class="bc-before-after">
    <img src="{{ $before }}" alt="Before maintenance" class="ba-before">
    <img src="{{ $after }}" alt="After maintenance" class="ba-after">
    <span class="ba-label before">{{ $beforeLabel }}</span>
    <span class="ba-label after">{{ $afterLabel }}</span>
    <div class="ba-handle"></div>
</div>
