@props(['title', 'subtitle' => null, 'breadcrumbs' => []])
<div class="bc-page-header">
    <div>
        @if (!empty($breadcrumbs))
            <div class="bc-breadcrumbs">
                @foreach ($breadcrumbs as $crumb)
                    @if (!empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}">{{ $crumb['label'] }}</a>
                        <i class="bi bi-chevron-right" style="font-size:9px;"></i>
                    @endif
                @endforeach
                <span class="current">{{ $title }}</span>
            </div>
        @endif
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <p>{{ $subtitle }}</p>
        @endif
    </div>
    <div @class(['d-flex', 'gap-2', 'flex-wrap', 'd-none' => trim((string) $slot) === ''])>
        {{ $slot }}
    </div>
</div>
