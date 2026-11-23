@php
    $gtmContainerId = \App\Models\PlatformSetting::getGtmContainerId();
@endphp
@if (!empty($gtmContainerId))
<!-- Google Tag Manager (GTM — Trì hoãn tải sau First Paint / tương tác đầu tiên để không làm chậm tốc độ mở trang) -->
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  (function(id){
    var loaded = false;
    function loadGtm(){
      if (loaded) return;
      loaded = true;
      var s = document.createElement('script');
      s.async = true;
      s.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(id);
      document.head.appendChild(s);
    }
    ['pointerdown','keydown','scroll','touchstart'].forEach(function(evt){
      window.addEventListener(evt, loadGtm, { once: true, passive: true });
    });
    if (document.readyState === 'complete') {
      setTimeout(loadGtm, 2000);
    } else {
      window.addEventListener('load', function(){ setTimeout(loadGtm, 2000); }, { once: true });
    }
  })(@json($gtmContainerId));
</script>
<!-- End Google Tag Manager -->
@endif

<meta name="google-site-verification" content="2BT_96GHQF0hh78-ApoFC-Af-EegbIHAEYbCcqJpabk" />