@php
    $gtmContainerId = \App\Models\PlatformSetting::getGtmContainerId();
@endphp
@if (!empty($gtmContainerId))
<!-- Google Tag Manager (GTM) -->
<script async src="https://www.googletagmanager.com/gtm.js?id={{ $gtmContainerId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
</script>
<!-- End Google Tag Manager -->
@endif

<meta name="google-site-verification" content="2BT_96GHQF0hh78-ApoFC-Af-EegbIHAEYbCcqJpabk" />