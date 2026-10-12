@extends('client.layouts.master')

@section('title', $template['name'] . ' — Chi Tiết Mẫu Thiệp Mây Hẹn')
@section('meta_description', $template['description'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/template-detail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/home.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_template_detail_wrapper">
        <div class="mayhen_template_detail_grid">
            <!-- Left: Sticky Mockup Preview -->
            <div class="mayhen_template_detail_preview_box">
                <img src="{{ $template['image'] }}" alt="{{ $template['name'] }}" class="mayhen_template_detail_preview_img">
            </div>

            <!-- Right: Details & Action -->
            <div class="mayhen_template_detail_info">
                <span class="mayhen_template_detail_badge">{{ $template['category_label'] }}</span>
                <h1 class="mayhen_template_detail_title">{{ $template['name'] }}</h1>
                <p class="mayhen_template_detail_desc">{{ $template['description'] }}</p>

                <!-- Features Included -->
                <div>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem; color: var(--mayhen-color-text-main);">Đặc Điểm & Tiện Ích Tích Hợp:</h3>
                    <div class="mayhen_template_detail_features_list">
                        @foreach ($template['features'] as $feat)
                            <div class="mayhen_template_detail_feature_item">
                                <svg class="mayhen_template_detail_feature_icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $feat }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_template_detail_actions">
                    <a href="{{ route('templates.preview', $template['slug']) }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg">
                        Mở Trải Nghiệm Xem Thử Live
                    </a>
                    <a href="{{ route('templates.preview', $template['slug']) }}#create" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                        Tạo Thiệp Với Mẫu Này
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
