@extends('client.layouts.master')

@section('title', 'Link Gia Phả Đã Hết Hạn | ' . \App\Models\PlatformSetting::getBrandName())
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/family-tree.css') }}">
@endpush

@section('content')
<div class="mayhen_family_tree_gate_page">
    <div class="mayhen_family_tree_gate_card">
        <span class="mayhen_family_tree_kicker">Hết hạn lưu trữ</span>
        <h1 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:24px; margin:8px 0 10px;">
            {{ $tree->title }}
        </h1>
        <p style="font-size:14px; color:#5C5349; line-height:1.65; margin:0 0 20px;">
            Đường dẫn gia phả này đã hết thời hạn lưu trữ của gói hiện tại. Chủ gia phả có thể đăng nhập để gia hạn hoặc tạo bản mới.
        </p>
        <a href="{{ route('family_tree.index') }}" class="mayhen_family_tree_btn_primary">
            Về Trang Gia Phả Online
        </a>
    </div>
</div>
@endsection
