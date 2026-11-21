{{-- ==========================================================================
     MÂY HẸN — REUSABLE ATELIER WAX-SEAL & GOLDEN CLOUD PRELOADER COMPONENT
     Usage: @include('client.components.atelier-loader', [
         'loaderId' => 'my_loader_id',
         'loaderNameId' => 'my_loader_name_id',
         'initialTitle' => 'Tiêu đề hiển thị',
         'initialStatus' => 'Đang mở phong thư cưới...',
         'visible' => true,
         'extraClass' => '',
     ])
     ========================================================================== --}}
@php
    $loaderBrandName = \App\Models\PlatformSetting::getBrandName();
    $loaderBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $loaderFaviconRaw = trim((string) \App\Models\PlatformSetting::getValue(
        \App\Models\PlatformSetting::GROUP_BRAND_SEO,
        'brand_favicon',
        '/storage/images/client/business/mayhen-favicon.png'
    ));
    $loaderFaviconUrl = '';
    if ($loaderFaviconRaw !== '') {
        $loaderFaviconUrl = (str_starts_with($loaderFaviconRaw, 'http://') || str_starts_with($loaderFaviconRaw, 'https://'))
            ? $loaderFaviconRaw
            : asset($loaderFaviconRaw);
    }

    $resolvedLoaderId = $loaderId ?? 'mayhen_client_atelier_loader';
    $resolvedNameId = $loaderNameId ?? ($resolvedLoaderId . '_name');
    $resolvedStatusId = $loaderStatusId ?? ($resolvedLoaderId . '_status');
    $resolvedTitle = $initialTitle ?? $loaderBrandName;
    $resolvedStatus = $initialStatus ?? 'Đang mở phong thư cưới...';
    $resolvedFallback = $monogramFallback ?? 'MH';
    $isVisible = (bool) ($visible ?? false);
    $resolvedExtraClass = trim((string) ($extraClass ?? ''));
@endphp

<div
    class="mayhen_client_loader {{ $isVisible ? 'is-visible' : '' }} {{ $resolvedExtraClass }}"
    id="{{ $resolvedLoaderId }}"
    data-mayhen-atelier-loader
    role="status"
    aria-live="polite"
    aria-hidden="{{ $isVisible ? 'false' : 'true' }}"
>
    <span class="mayhen_client_loader_cloud mayhen_client_loader_cloud_a" aria-hidden="true"></span>
    <span class="mayhen_client_loader_cloud mayhen_client_loader_cloud_b" aria-hidden="true"></span>
    <div class="mayhen_client_loader_frame" aria-hidden="true"></div>

    <div class="mayhen_client_loader_inner">
        <div class="mayhen_client_loader_crest" aria-hidden="true">
            <span class="mayhen_client_loader_ring_outer"></span>
            <span class="mayhen_client_loader_ring_inner"></span>
            <div class="mayhen_client_loader_seal {{ $loaderFaviconUrl === '' ? 'is-fallback' : '' }}" data-mayhen-loader-seal>
                @if ($loaderFaviconUrl !== '')
                    <img
                        class="mayhen_client_loader_favicon"
                        src="{{ $loaderFaviconUrl }}"
                        alt="{{ $loaderBrandName }}"
                        width="38"
                        height="38"
                        decoding="async"
                        data-mayhen-loader-favicon
                    >
                @endif
                <span class="mayhen_client_loader_monogram">{{ $resolvedFallback }}</span>
            </div>
        </div>

        <span class="mayhen_client_loader_brand">{{ $loaderBrandNameUpper }}</span>
        <p class="mayhen_client_loader_name" id="{{ $resolvedNameId }}" data-mayhen-loader-title>{{ $resolvedTitle }}</p>
        <span class="mayhen_client_loader_status" id="{{ $resolvedStatusId }}" data-mayhen-loader-status>{{ $resolvedStatus }}</span>

        <div class="mayhen_client_loader_progress" aria-hidden="true">
            <span class="mayhen_client_loader_progress_bar"></span>
        </div>
    </div>
</div>
