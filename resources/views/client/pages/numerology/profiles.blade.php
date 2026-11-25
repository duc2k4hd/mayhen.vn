@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
@endphp

@section('title', 'Hồ Sơ Thần Số Học Riêng Tư')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@section('content')
<div class="mayhen_numerology_page">
    <section class="mayhen_numerology_hero" style="padding: 36px 0;">
        <div class="mayhen_numerology_container">
            <span class="mayhen_numerology_eyebrow">Không Gian Riêng Tư Của Bạn</span>
            <h1 class="mayhen_numerology_h1">Hồ Sơ <em>Thần Số Học</em> &amp; Lịch Sử Phiên Bản</h1>
            <p class="mayhen_numerology_lead">Toàn bộ hồ sơ cá nhân và báo cáo tình duyên được bảo mật riêng tư mặc định (chỉ tài khoản chủ sở hữu được xem).</p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('numerology.index') }}" class="mayhen_numerology_btn_primary">+ Tra Cứu Hồ Sơ Mới</a>
                <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_btn_crimson">Thần Số Học Tình Duyên</a>
            </div>
        </div>
    </section>

    <section class="mayhen_numerology_section mayhen_numerology_section--paper">
        <div class="mayhen_numerology_container">
            @if(session('success'))
                <div class="mayhen_numerology_note_box" style="margin-bottom: 18px; border-left-color: #1E4D3B;">{{ session('success') }}</div>
            @endif

            <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 16px;">Hồ Sơ Cá Nhân Đã Lưu ({{ $readings->total() }})</h2>
            @if($readings->isEmpty())
                <div class="mayhen_numerology_note_box">
                    Bạn chưa lưu hồ sơ Thần Số Học nào. <a href="{{ route('numerology.index') }}" style="color: #85182A; font-weight: 700;">Bắt đầu tra cứu miễn phí ngay &rarr;</a>
                </div>
            @else
                <div class="mayhen_numerology_grid_3">
                    @foreach($readings as $r)
                        <div class="mayhen_numerology_sheet" style="padding: 18px;">
                            <div style="display: flex; justify-content: space-between; font-size: 11.5px; color: #1E4D3B; font-weight: 700; margin-bottom: 6px;">
                                <span>v{{ $r->version_number }} • Năm {{ $r->viewed_year }}</span>
                                <span>{{ $r->isSharedActive() ? 'Đang bật Thẻ Chia Sẻ' : 'Riêng tư' }}</span>
                            </div>
                            <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 32px; color: #85182A; line-height: 1.1;">
                                {{ $r->calculation_payload_json['core_numbers']['life_path']['display'] ?? '—' }}
                            </div>
                            <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 4px 0 6px;">{{ $r->profile?->display_name ?: 'Hồ Sơ Con Số' }}</h3>
                            <p style="font-size: 12.5px; color: #5C5248; margin: 0 0 12px;">
                                {{ $r->followups_count }} lượt Hỏi Sâu • {{ $r->journals_count }} Nhật ký • {{ $r->created_at?->format('d/m/Y') }}
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <a href="{{ route('numerology.show', $r->slug) }}" class="mayhen_numerology_btn_primary mayhen_numerology_btn_sm">Mở Hồ Sơ &rarr;</a>
                                <form method="POST" action="{{ route('numerology.destroy', $r->slug) }}" onsubmit="return confirm('Xóa vĩnh viễn hồ sơ này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Xóa</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div style="margin-top: 20px;">{{ $readings->links() }}</div>
            @endif

            @if($loveReports->isNotEmpty())
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 36px 0 16px;">Báo Cáo Thần Số Học Tình Duyên ({{ $loveReports->count() }})</h2>
                <div class="mayhen_numerology_grid_2">
                    @foreach($loveReports as $rep)
                        <div class="mayhen_numerology_sheet" style="padding: 18px;">
                            <span class="mayhen_numerology_chapter_tag">Cặp đôi • {{ $rep->comparison_payload_json['pair_key'] ?? '' }}</span>
                            <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 8px;">
                                {{ $rep->comparison_payload_json['person_a']['display_name'] ?? 'Người 1' }} &amp; {{ $rep->comparison_payload_json['person_b']['display_name'] ?? 'Người 2' }}
                            </h3>
                            <p style="font-size: 13.5px; color: #5C5248; margin: 0 0 12px;">{{ $rep->interpretation_json['headline'] ?? '' }}</p>
                            <a href="{{ route('numerology.love.show', $rep->slug) }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Mở Câu Chuyện Hai Chúng Ta &rarr;</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
