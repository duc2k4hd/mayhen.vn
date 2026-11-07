@extends('partner.layouts.master')

@section('title', 'Quản Lý Thiệp Cưới Khách Hàng | ' . config('app.name'))

@section('content')
<div class="mayhen_partner_weddings_wrap">
    {{-- Breadcrumbs --}}
    <div class="mayhen_partner_breadcrumbs">
        <a href="{{ route('partner.dashboard') }}">Cổng Đối Tác</a>
        <span>/</span>
        <span>Thiệp Cưới Khách Hàng</span>
    </div>

    @if(session('success'))
        <div class="mayhen_partner_alert mayhen_partner_alert--success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mayhen_partner_sec_head" style="margin-bottom: 2rem;">
        <div>
            <h1 class="dash_head_title" style="font-size: 1.85rem;">Danh Sách Thiệp Cưới Của Khách Hàng</h1>
            <p class="dash_head_desc">Quản lý tập trung toàn bộ các hôn lễ do Studio khởi tạo và đồng hành.</p>
        </div>
        <div>
            <a href="{{ route('partner.weddings.create') }}" class="mayhen_partner_btn mayhen_partner_btn--gold">
                + Khởi Tạo Thiệp Mới Cho Khách
            </a>
        </div>
    </div>

    {{-- Filter & Search Toolbar --}}
    <div style="background: #FFFFFF; border: 1px solid #EBE5DC; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <form action="{{ route('partner.weddings.index') }}" method="GET" style="display: flex; gap: 0.5rem; flex-grow: 1; max-width: 450px;">
            <input 
                type="text" 
                name="q" 
                class="form_input" 
                placeholder="Tìm theo tên cô dâu, chú rể, sảnh tiệc..." 
                value="{{ $search }}"
                style="padding: 0.55rem 0.95rem; font-size: 0.88rem;"
            >
            <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--primary" style="padding: 0.55rem 1rem;">
                🔍 Tìm
            </button>
            @if($search)
                <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_btn mayhen_partner_btn--outline" style="padding: 0.55rem 0.85rem;">
                    Xóa lọc
                </a>
            @endif
        </form>

        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_btn mayhen_partner_btn--sm {{ empty($status) ? 'mayhen_partner_btn--primary' : 'mayhen_partner_btn--outline' }}">
                Tất cả
            </a>
            <a href="{{ route('partner.weddings.index', ['status' => 'published', 'q' => $search]) }}" class="mayhen_partner_btn mayhen_partner_btn--sm {{ $status === 'published' ? 'mayhen_partner_btn--primary' : 'mayhen_partner_btn--outline' }}">
                Đã xuất bản
            </a>
            <a href="{{ route('partner.weddings.index', ['status' => 'draft', 'q' => $search]) }}" class="mayhen_partner_btn mayhen_partner_btn--sm {{ $status === 'draft' ? 'mayhen_partner_btn--primary' : 'mayhen_partner_btn--outline' }}">
                Bản nháp
            </a>
        </div>
    </div>

    {{-- Weddings Table Card --}}
    <div class="mayhen_partner_table_card">
        @if($weddings->isNotEmpty())
            <div style="overflow-x: auto;">
                <table class="mayhen_partner_table">
                    <thead>
                        <tr>
                            <th>Đôi Uyên Ương</th>
                            <th>Ngày Cưới</th>
                            <th>Mẫu Thiết Kế</th>
                            <th>Trạng Thái</th>
                            <th>Mã Bàn Giao</th>
                            <th>Ghi Chú Studio</th>
                            <th style="text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weddings as $w)
                            @php
                                $inv = $w->invitations->first();
                            @endphp
                            <tr>
                                <td>
                                    <strong style="display: block; font-size: 0.95rem; color: #1F1D1A;">
                                        {{ $w->groom_name }} & {{ $w->bride_name }}
                                    </strong>
                                    @if($w->venue_name)
                                        <span style="font-size: 0.78rem; color: #8C8479;">📍 {{ $w->venue_name }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt' }}
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #544E47;">
                                        {{ $inv?->template?->name ?? 'Mặc định' }}
                                    </span>
                                </td>
                                <td>
                                    @if($inv && $inv->is_published)
                                        <span class="badge_status badge_published">Đã Xuất Bản</span>
                                    @else
                                        <span class="badge_status badge_draft">Bản Nháp</span>
                                    @endif
                                </td>
                                <td>
                                    @if($w->handover_code)
                                        <span style="font-family: monospace; font-weight: 700; color: #8C6D3F; background: #FAF8F5; padding: 2px 6px; border-radius: 4px; border: 1px solid #EBE5DC;">
                                            {{ $w->handover_code }}
                                        </span>
                                    @else
                                        <span style="font-size: 0.78rem; color: #2E7D32;">✓ Đã bàn giao</span>
                                    @endif
                                </td>
                                <td>
                                    @if($w->client_notes)
                                        <span style="font-size: 0.8rem; color: #6E685F; font-style: italic;" title="{{ $w->client_notes }}">
                                            {{ Str::limit($w->client_notes, 35) }}
                                        </span>
                                    @else
                                        <span style="color: #A39B8F; font-size: 0.8rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table_actions" style="justify-content: flex-end;">
                                        @if($inv)
                                            <a href="{{ route('invitation.create', ['edit' => $inv->slug]) }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--primary" title="Vào Studio">
                                                ✏️ Sửa
                                            </a>
                                            <a href="{{ route('invitation.show', $inv->slug) }}" target="_blank" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" title="Xem thiệp">
                                                👁️
                                            </a>
                                        @endif
                                        <a href="{{ route('partner.weddings.handover', $w->id) }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" title="Lấy mã bàn giao">
                                            🔑 Bàn Giao
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($weddings->hasPages())
                <div style="padding: 1.25rem; display: flex; justify-content: center; border-top: 1px solid #EBE5DC;">
                    {{ $weddings->links() }}
                </div>
            @endif
        @else
            <div class="mayhen_partner_empty">
                <div class="empty_icon">🔍</div>
                <h3 class="empty_title">Không Tìm Thấy Thiệp Cưới Nào</h3>
                <p class="empty_desc">
                    Không có đám cưới nào khớp với từ khóa tìm kiếm hoặc Studio chưa khởi tạo thiệp nào.
                </p>
                <a href="{{ route('partner.weddings.create') }}" class="mayhen_partner_btn mayhen_partner_btn--gold">
                    + Tạo Thiệp Cưới Cho Khách Hàng
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

