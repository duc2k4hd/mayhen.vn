@extends('admin.layouts.master')

@section('title', 'Quản Lý Thiệp Cưới — Mây Hẹn')
@section('page_title', 'Danh Sách Thiệp Cưới Khách Hàng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/invitations.css') }}">
@endpush

@section('content')
    <!-- Filter Bar -->
    <form action="{{ route('admin.invitations.index') }}" method="GET" class="mayhen_admin_filter_bar">
        <!-- Search Query Input -->
        <div class="mayhen_admin_search_box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #7A756E;">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm tên cô dâu, chú rể, slug, SĐT, email..." 
                class="mayhen_admin_search_input"
            >
        </div>

        <!-- Filter by Status -->
        <select name="status" class="mayhen_admin_select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="published" {{ $currentStatus === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
            <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Bản nháp</option>
            <option value="locked" {{ $currentStatus === 'locked' ? 'selected' : '' }}>Đang bị khóa</option>
            <option value="expired" {{ $currentStatus === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
        </select>

        <!-- Filter by Template -->
        <select name="template_id" class="mayhen_admin_select">
            <option value="">-- Tất cả mẫu thiệp --</option>
            @foreach ($templates as $tpl)
                <option value="{{ $tpl->id }}" {{ (string)$currentTemplateId === (string)$tpl->id ? 'selected' : '' }}>
                    {{ $tpl->name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <span>Tìm Kiếm & Lọc</span>
        </button>

        @if ($currentSearch || $currentStatus || $currentTemplateId)
            <a href="{{ route('admin.invitations.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt Lại</span>
            </a>
        @endif
    </form>

    <!-- Data Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                <span>Thiệp Cưới Khách Hàng ({{ $invitations->total() }} thiệp)</span>
            </div>
            <div>
                <a href="{{ route('templates.index') }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    <span>Thư Viện Mẫu &nearr;</span>
                </a>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Cặp Đôi & Ngày Cưới</th>
                        <th>Mẫu Áp Dụng</th>
                        <th>Khách Hàng (Tạo)</th>
                        <th>Đường Dẫn Công Khai</th>
                        <th>Tương Tác</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invitations as $inv)
                        @php
                            $wedding = $inv->wedding;
                            $user = $wedding?->user;
                            $template = $inv->template;
                            $isExpired = $inv->expires_at && $inv->expires_at->isPast();
                        @endphp
                        <tr>
                            <!-- Couple & Wedding Date -->
                            <td>
                                <div class="mayhen_admin_inv_couple">
                                    <span class="mayhen_admin_inv_couple_names">
                                        {{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }}
                                    </span>
                                    <span class="mayhen_admin_inv_date">
                                        Ngày cưới: {{ $wedding && $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Template -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    @if ($template && $template->thumbnail_url)
                                        <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}" class="mayhen_admin_inv_thumbnail">
                                    @endif
                                    <div>
                                        <strong style="font-size: 0.8125rem;">{{ $template->name ?? 'Mẫu Mặc Định' }}</strong><br>
                                        <span style="font-size: 0.6875rem; color: #7A756E;">{{ $inv->version_type ?? 'Standard' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Customer / Owner -->
                            <td>
                                @if ($user)
                                    <strong style="font-size: 0.8125rem;">{{ $user->name }}</strong><br>
                                    <span style="font-size: 0.75rem; color: #7A756E;">{{ $user->email }}</span>
                                    @if ($user->phone)
                                        <br><span style="font-size: 0.75rem; color: #7A756E;">{{ $user->phone }}</span>
                                    @endif
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_muted">Khách vãng lai</span>
                                @endif
                            </td>

                            <!-- Public URL with copy button -->
                            <td>
                                <div class="mayhen_admin_inv_link_wrap">
                                    <span class="mayhen_admin_inv_slug_pill" title="/thiep/{{ $inv->slug }}">
                                        /thiep/{{ $inv->slug }}
                                    </span>
                                    <button 
                                        type="button" 
                                        class="mayhen_admin_copy_btn" 
                                        data-copy-target="{{ url('/thiep/' . $inv->slug) }}" 
                                        title="Sao chép liên kết"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                        </svg>
                                    </button>
                                    <a 
                                        href="{{ url('/thiep/' . $inv->slug) }}" 
                                        target="_blank" 
                                        class="mayhen_admin_copy_btn" 
                                        title="Mở tab mới"
                                    >
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                            <polyline points="15 3 21 3 21 9"></polyline>
                                            <line x1="10" y1="14" x2="21" y2="3"></line>
                                        </svg>
                                    </a>
                                </div>
                            </td>

                            <!-- Interaction Stats -->
                            <td>
                                <div style="font-size: 0.75rem; color: #5C564E; line-height: 1.4;">
                                    <span>Khách: <strong>{{ $wedding ? $wedding->guests()->count() : 0 }}</strong></span><br>
                                    <span>Lời chúc: <strong>{{ $wedding ? $wedding->wishes()->count() : 0 }}</strong></span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                @php
                                    $isLocked = !empty($inv->custom_config['is_locked']);
                                @endphp
                                @if ($isLocked)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger" title="Lý do khóa: {{ $inv->custom_config['lock_reason'] ?? 'Vi phạm quy định' }}">Đang Khóa</span>
                                @elseif ($isExpired)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Hết Hạn</span>
                                @elseif ($inv->is_published)
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Xuất Bản</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Bản Nháp</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <a href="{{ route('admin.invitations.show', $inv->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" title="Xem chi tiết & Phone Mockup">
                                        Chi Tiết &nearr;
                                    </a>
                                    <form action="{{ route('admin.invitations.toggle_publish', $inv->id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" title="{{ $inv->is_published ? 'Thu hồi về Bản Nháp' : 'Kích hoạt Xuất Bản' }}">
                                            {{ $inv->is_published ? 'Ẩn' : 'Bật' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #7A756E; padding: 40px;">
                                Không tìm thấy thiệp cưới nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invitations->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $invitations->links() }}
            </div>
        @endif
    </div>
@endsection

