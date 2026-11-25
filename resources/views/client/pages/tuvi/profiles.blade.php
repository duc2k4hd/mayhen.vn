@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
@endphp

@section('title', 'Hồ Sơ Thiên Mệnh Riêng Tư')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@section('content')
<div class="mayhen_tuvi_page">
    <section class="mayhen_tuvi_celestial_hero" style="padding: 36px 0 42px;">
        <div class="mayhen_tuvi_container">
            <span class="mayhen_tuvi_kicker">🔒 Không Gian Riêng Tư Của Bạn</span>
            <h1 class="mayhen_tuvi_hero_h1" style="font-size: clamp(26px, 3.4vw, 38px); text-align: left;">
                Hồ Sơ <span>Thiên Mệnh</span>
            </h1>
            <p class="mayhen_tuvi_hero_lead" style="margin: 0 0 18px; text-align: left;">
                Lưu trữ các lá số Tử Vi cá nhân, lịch sử phiên bản khi hiệu chỉnh giờ sinh và các báo cáo Nhân Duyên Song Mệnh chỉ riêng bạn có quyền xem.
            </p>
            <div class="mayhen_tuvi_btn_row">
                <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_gold">+ Lập Lá Số Mới</a>
                <a href="{{ route('tuvi.synastry.index') }}" class="mayhen_tuvi_btn_outline">✦ Nhân Duyên Song Mệnh</a>
            </div>
        </div>
    </section>

    <section class="mayhen_tuvi_section_light">
        <div class="mayhen_tuvi_container">
            @if(session('success'))
                <div class="mayhen_tuvi_alert_info" style="margin-bottom: 18px; border-color: #1E4D3B; color: #1E4D3B;">
                    {{ session('success') }}
                </div>
            @endif

            <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 16px;">Các Lá Số Tử Vi Đã Lưu ({{ $charts->total() }})</h2>
            @if($charts->isEmpty())
                <div class="mayhen_tuvi_card" style="text-align: center; padding: 36px 24px;">
                    <h3>Bạn chưa lưu lá số Tử Vi nào</h3>
                    <p style="margin-bottom: 16px;">Hãy lập lá số đầu tiên để mở trọn bộ 12 Thiên Mệnh Bài 3D và bản luận giải AI 6 tầng.</p>
                    <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_primary">Lập Lá Số Miễn Phí Ngay</a>
                </div>
            @else
                <div class="mayhen_tuvi_wiki_grid">
                    @foreach($charts as $chartItem)
                        <div class="mayhen_tuvi_card" style="display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 8px;">
                                    <span class="mayhen_tuvi_badge" style="margin: 0;">v{{ $chartItem->version_number }} • Lưu niên {{ $chartItem->viewed_year }}</span>
                                    <span style="font-size: 11.5px; color: #6B5F52;">
                                        {{ $chartItem->isSharedActive() ? '🔗 Đang bật Thẻ Chia Sẻ' : '🔒 Riêng tư' }}
                                    </span>
                                </div>
                                <h3>{{ $chartItem->profile?->nickname ?: 'Hồ Sơ Thiên Mệnh' }}</h3>
                                <p style="font-size: 13px; margin-bottom: 8px;">
                                    {{ $chartItem->interpretation_json['headline'] ?? 'Lá số chuẩn Nam Phái Việt Nam.' }}
                                </p>
                                <div style="font-size: 12px; color: #6B5F52;">
                                    {{ $chartItem->followups_count }} lượt Hỏi Sâu • {{ $chartItem->journals_count }} Nhật ký • {{ $chartItem->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <div class="mayhen_tuvi_btn_row" style="justify-content: space-between;">
                                <a href="{{ route('tuvi.show', $chartItem->slug) }}" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm">Mở Lá Số &rarr;</a>
                                <form method="POST" action="{{ route('tuvi.destroy', $chartItem->slug) }}" onsubmit="return confirm('Xóa vĩnh viễn lá số này khỏi Hồ Sơ Thiên Mệnh?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Xóa</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top: 22px;">
                    {{ $charts->links() }}
                </div>
            @endif

            @if($synastryReports->isNotEmpty())
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 36px 0 16px;">Báo Cáo Nhân Duyên Song Mệnh ({{ $synastryReports->count() }})</h2>
                <div class="mayhen_tuvi_layers_grid">
                    @foreach($synastryReports as $rep)
                        <div class="mayhen_tuvi_card">
                            <span class="mayhen_tuvi_badge">Nhật · Nguyệt · Duyên</span>
                            <h3>{{ $rep->chartA?->profile?->nickname ?? 'Người 1' }} &amp; {{ $rep->chartB?->profile?->nickname ?? 'Người 2' }}</h3>
                            <p style="margin-bottom: 12px;">{{ $rep->interpretation_json['headline'] ?? 'Đối chiếu 10 chủ đề hôn nhân & tổ ấm.' }}</p>
                            <a href="{{ route('tuvi.synastry.show', $rep->slug) }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Mở Báo Cáo Song Mệnh &rarr;</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
