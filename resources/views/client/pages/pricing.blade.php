@extends('client.layouts.master')

@section('title', 'Bảng Giá Gói Dịch Vụ Thiệp Cưới Online — Mây Hẹn')
@section('meta_description', 'Bảng giá minh bạch các gói thiệp cưới online tại Mây Hẹn: Dùng thử miễn phí, Gói Premium 149.000đ, Gói Wedding Pro 299.000đ.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/pricing.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container">
        <!-- Header -->
        <header class="mayhen_pricing_header">
            <h1 class="mayhen_pricing_title">Bảng Giá Minh Bạch & Trọn Gói</h1>
            <p class="mayhen_pricing_desc">
                Thanh toán một lần duy nhất cho toàn bộ đám cưới của bạn. Không phát sinh chi phí ẩn, không giới hạn số lượng khách truy cập.
            </p>
        </header>

        <!-- Pricing Cards Grid -->
        <div class="mayhen_pricing_grid">
            @foreach ($plans as $plan)
                <div class="mayhen_pricing_card {{ $plan['is_featured'] ? 'mayhen_pricing_card_featured' : '' }}">
                    @if ($plan['is_featured'])
                        <div class="mayhen_pricing_card_badge">Phổ Biến Nhất</div>
                    @endif

                    <h2 class="mayhen_pricing_name">{{ $plan['name'] }}</h2>
                    <p class="mayhen_pricing_card_desc">{{ $plan['description'] }}</p>

                    <div class="mayhen_pricing_price_wrap">
                        <span class="mayhen_pricing_amount">{{ $plan['price'] }}</span>
                        @if (!empty($plan['period']))
                            <span class="mayhen_pricing_period">{{ $plan['period'] }}</span>
                        @endif
                    </div>

                    <div class="mayhen_pricing_feature_list">
                        @foreach ($plan['features'] as $feature)
                            <div class="mayhen_pricing_feature_item">
                                <svg class="mayhen_pricing_feature_check" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('templates.index') }}" class="mayhen_client_button_base {{ $plan['is_featured'] ? 'mayhen_client_button_primary' : 'mayhen_client_button_secondary' }} mayhen_client_button_lg" style="width: 100%;">
                        {{ $plan['cta'] }}
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endsection
