{{-- Favicon chuẩn Mây Hẹn (Đồng bộ cấu hình Admin) --}}
@php
    $appFaviconUrl = \App\Models\PlatformSetting::getValue(\App\Models\PlatformSetting::GROUP_BRAND_SEO, 'brand_favicon', '/storage/images/client/business/mayhen-favicon.png');
@endphp
<link rel="apple-touch-icon" sizes="180x180"
    href="{{ asset($appFaviconUrl) }}">
<link rel="icon" type="image/png" sizes="32x32"
    href="{{ asset($appFaviconUrl) }}">
<link rel="icon" type="image/png" sizes="16x16"
    href="{{ asset($appFaviconUrl) }}">
<link rel="mask-icon"
    href="{{ asset($appFaviconUrl) }}"
    color="#C5A880">
<link rel="icon"
    href="{{ asset($appFaviconUrl) }}"
    type="image/x-icon">
<meta name="theme-color" content="#C5A880">
