@extends('admin.layouts.master')

@section('title', 'Quản Lý Thông Báo Công Khai & Banners')
@section('page_title', 'Thông Báo Công Khai & Banners')

@push('styles')
<style>
    .mayhen_admin_stat_grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .mayhen_admin_stat_card {
        background: #FFFFFF;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .mayhen_admin_stat_label {
        font-size: 13px;
        color: var(--mh-admin-muted, #6B7280);
        margin-bottom: 6px;
    }
    .mayhen_admin_stat_val {
        font-size: 26px;
        font-weight: 700;
        color: var(--mh-admin-charcoal, #111827);
    }
</style>
@endpush

@section('content')
<div class="mayhen_admin_content_wrap">
    <!-- Stat Cards Header -->
    <div class="mayhen_admin_stat_grid">
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">🟢 Đang Kích Hoạt</div>
            <div class="mayhen_admin_stat_val" style="color: #059669;">{{ $stats['active_count'] }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">⏳ Đã Lên Lịch Tương Lai</div>
            <div class="mayhen_admin_stat_val" style="color: #D97706;">{{ $stats['scheduled_count'] }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">🔒 Đã Hết Khung Giờ</div>
            <div class="mayhen_admin_stat_val" style="color: #6B7280;">{{ $stats['expired_count'] }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">🖱️ Tổng Lượt Nhấn CTA</div>
            <div class="mayhen_admin_stat_val" style="color: #2563EB;">{{ number_format($stats['total_clicks']) }}</div>
        </div>
    </div>

    <!-- Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="font-size: 14px; color: #4B5563;">
            Quản trị dải thông báo trên đỉnh trang, banner dashboard và popup thông báo (Mục 147 PRD V3).
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="mayhen_admin_btn mayhen_admin_btn_primary" style="display: inline-flex; align-items: center; gap: 6px;">
            <span>+ Tạo Thông Báo Mới</span>
        </a>
    </div>

    <!-- Announcements Table -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #F9FAFB; border-bottom: 1px solid #E5E7EB; color: #4B5563;">
                        <th style="padding: 14px 16px;">Tiêu Đề & Nội Dung</th>
                        <th style="padding: 14px 16px;">Vị Trí Hiển Thị</th>
                        <th style="padding: 14px 16px;">Phân Loại</th>
                        <th style="padding: 14px 16px;">Khung Giờ Hẹn Lịch</th>
                        <th style="padding: 14px 16px;">Views / Clicks</th>
                        <th style="padding: 14px 16px;">Trạng Thái</th>
                        <th style="padding: 14px 16px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $item)
                        @php
                            $isLive = $item->isCurrentlyActive();
                        @endphp
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 14px 16px; vertical-align: top; max-width: 320px;">
                                <div style="font-weight: 600; color: #111827; margin-bottom: 4px;">{{ $item->title }}</div>
                                <div style="color: #4B5563; font-size: 12px; line-height: 1.4;">{{ $item->content }}</div>
                                @if(!empty($item->cta_text))
                                    <div style="margin-top: 6px; font-size: 11px;">
                                        Nút CTA: <span style="background: #E5E7EB; padding: 2px 6px; border-radius: 4px; font-weight: 500;">{{ $item->cta_text }}</span> &rarr; <span style="color: #6B7280;">{{ $item->cta_url ?: 'Không có link' }}</span>
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span style="background: #F3F4F6; color: #374151; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 500;">
                                    {{ $item->placement_label }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                @php
                                    $badgeStyle = match($item->type) {
                                        'promotion' => 'background: #FAF5EA; color: #9C7A4A; border: 1px solid #EFE4CA;',
                                        'warning' => 'background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A;',
                                        'danger' => 'background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;',
                                        default => 'background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;',
                                    };
                                @endphp
                                <span style="{{ $badgeStyle }} font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 4px; display: inline-block;">
                                    {{ $item->type_label }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; font-size: 12px; color: #4B5563;">
                                @if($item->starts_at || $item->ends_at)
                                    <div>Từ: <strong>{{ $item->starts_at ? $item->starts_at->format('d/m/Y H:i') : 'Ngay lập tức' }}</strong></div>
                                    <div>Đến: <strong>{{ $item->ends_at ? $item->ends_at->format('d/m/Y H:i') : 'Vô thời hạn' }}</strong></div>
                                @else
                                    <span style="color: #9CA3AF;">Hiển thị liên tục</span>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #111827;">{{ number_format($item->views_count) }} xem</div>
                                <div style="font-size: 12px; color: #2563EB;">{{ number_format($item->clicks_count) }} click</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <form action="{{ route('admin.announcements.toggle', $item->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;">
                                        @if($isLive)
                                            <span class="mayhen_admin_badge mayhen_admin_badge_success" title="Bấm để tạm tắt">Đang Hiển Thị</span>
                                        @elseif($item->is_active)
                                            <span class="mayhen_admin_badge mayhen_admin_badge_warning" title="Bấm để tắt">Chờ Khung Giờ</span>
                                        @else
                                            <span class="mayhen_admin_badge mayhen_admin_badge_secondary" title="Bấm để bật">Đã Tắt</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('admin.announcements.edit', $item->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 10px; font-size: 12px;">
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.announcements.destroy', $item->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 10px; font-size: 12px; color: #DC2626; border-color: #FECACA;">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: #6B7280;">
                                <div style="font-size: 28px; margin-bottom: 8px;">📢</div>
                                <div>Chưa có thông báo công khai nào được thiết lập.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($announcements->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #E5E7EB;">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
