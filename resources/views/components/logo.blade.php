@props(['icon' => 'bi-signpost-split-fill', 'imgClass' => ''])

@if (file_exists(public_path('assets/images/logo.png')))
    <img src="{{ asset('assets/images/logo.png') }}" alt="BridgeCare — Smart Infrastructure Management System"
        class="bc-logo-img {{ $imgClass }}" />
@else
    <div class="bc-brand-icon {{ $imgClass }}"><i class="bi {{ $icon }}"></i></div>
@endif
