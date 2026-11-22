@php
    $legalLinks = [
        ['route' => 'privacy', 'label' => '1. Chính Sách Bảo Mật'],
        ['route' => 'policy.complaints', 'label' => '2. Giải Quyết Khiếu Nại'],
        ['route' => 'policy.pricing', 'label' => '3. Chính Sách Giá'],
        ['route' => 'policy.payment', 'label' => '4. Chính Sách Thanh Toán'],
        ['route' => 'policy.service_conditions', 'label' => '5. Điều Kiện Cung Cấp Dịch Vụ'],
        ['route' => 'policy.delivery_refund', 'label' => '6. Giao Hàng & Hoàn Tiền'],
        ['route' => 'policy.support', 'label' => '7. Hỗ Trợ Trực Tuyến'],
        ['route' => 'terms', 'label' => 'Điều Khoản Dịch Vụ Chung'],
    ];
@endphp

<div class="mayhen_legal_switcher">
    <div class="mayhen_legal_toc_title">Hồ Sơ Chính Sách Công Bố</div>
    @foreach($legalLinks as $item)
        <a href="{{ route($item['route']) }}" class="mayhen_legal_switch_link {{ request()->routeIs($item['route']) ? 'is-current' : '' }}">
            <span>{{ $item['label'] }}</span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    @endforeach
</div>
