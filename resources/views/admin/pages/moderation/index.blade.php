@extends('admin.layouts.master')

@section('title', 'Trung Tâm Kiểm Duyệt Nội Dung & Báo Cáo Vi Phạm')
@section('page_title', 'Kiểm Duyệt Nội Dung & Báo Cáo Vi Phạm')

@push('styles')
<style>
    .mayhen_admin_stat_grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .mayhen_admin_stat_val {
        font-size: 26px;
        font-weight: 700;
        color: var(--mh-admin-charcoal, #111827);
    }
    .mayhen_admin_filter_tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        border-bottom: 1px solid var(--mh-admin-border, #E5E7EB);
        padding-bottom: 12px;
    }
    .mayhen_admin_tab_pill {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        color: #4B5563;
        background: #F3F4F6;
        transition: all 0.2s;
    }
    .mayhen_admin_tab_pill:hover {
        background: #E5E7EB;
        color: #111827;
    }
    .mayhen_admin_tab_pill.active {
        background: #111827;
        color: #FFFFFF;
        font-weight: 600;
    }
    .mayhen_safe_preview {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 10px;
        font-size: 13px;
        max-width: 320px;
        position: relative;
    }
    .mayhen_safe_blur {
        filter: blur(4px);
        user-select: none;
        transition: filter 0.2s ease;
    }
    .mayhen_safe_blur.revealed {
        filter: none;
        user-select: auto;
    }
    .mayhen_safe_overlay_btn {
        display: inline-block;
        margin-top: 6px;
        font-size: 11px;
        color: #9C7A4A;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
    }
    .mayhen_action_group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .mayhen_action_btn {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .mayhen_action_btn:hover { opacity: 0.85; }
    .mayhen_btn_hide { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
    .mayhen_btn_delete { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }
    .mayhen_btn_dismiss { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
</style>
@endpush

@section('content')
<div class="mayhen_admin_content_wrap">
    <!-- Stat Cards Header -->
    <div class="mayhen_admin_stat_grid">
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">
                <span>⚠️ Chờ Xem Xét</span>
            </div>
            <div class="mayhen_admin_stat_val" style="color: #D97706;">{{ $stats['pending_count'] }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">
                <span>👁️‍🗨️ Đã Ẩn Khỏi Thiệp</span>
            </div>
            <div class="mayhen_admin_stat_val" style="color: #4B5563;">{{ $stats['hidden_count'] }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">
                <span>🗑️ Đã Xóa Vĩnh Viễn</span>
            </div>
            <div class="mayhen_admin_stat_val" style="color: #DC2626;">{{ $stats['deleted_count'] }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">
                <span>✓ Đã Bác Bỏ (An Toàn)</span>
            </div>
            <div class="mayhen_admin_stat_val" style="color: #059669;">{{ $stats['dismissed_count'] }}</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mayhen_admin_filter_tabs">
        <a href="{{ route('admin.moderation.index', ['status' => 'pending']) }}" class="mayhen_admin_tab_pill {{ $status === 'pending' ? 'active' : '' }}">
            Chờ Xử Lý ({{ $stats['pending_count'] }})
        </a>
        <a href="{{ route('admin.moderation.index', ['status' => 'hidden']) }}" class="mayhen_admin_tab_pill {{ $status === 'hidden' ? 'active' : '' }}">
            Đã Ẩn ({{ $stats['hidden_count'] }})
        </a>
        <a href="{{ route('admin.moderation.index', ['status' => 'deleted']) }}" class="mayhen_admin_tab_pill {{ $status === 'deleted' ? 'active' : '' }}">
            Đã Xóa ({{ $stats['deleted_count'] }})
        </a>
        <a href="{{ route('admin.moderation.index', ['status' => 'dismissed']) }}" class="mayhen_admin_tab_pill {{ $status === 'dismissed' ? 'active' : '' }}">
            Đã Bác Bỏ ({{ $stats['dismissed_count'] }})
        </a>
        <a href="{{ route('admin.moderation.index', ['status' => 'all']) }}" class="mayhen_admin_tab_pill {{ $status === 'all' ? 'active' : '' }}">
            Tất Cả ({{ $stats['total_count'] }})
        </a>
    </div>

    <!-- Main Reports Table -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #F9FAFB; border-bottom: 1px solid #E5E7EB; color: #4B5563;">
                        <th style="padding: 14px 16px;">Mã Case</th>
                        <th style="padding: 14px 16px;">Đám Cưới & Loại</th>
                        <th style="padding: 14px 16px;">Nội Dung Bị Báo Cáo (Xem An Toàn)</th>
                        <th style="padding: 14px 16px;">Lý Do & Chi Tiết</th>
                        <th style="padding: 14px 16px;">Người Báo Cáo</th>
                        <th style="padding: 14px 16px;">Trạng Thái</th>
                        <th style="padding: 14px 16px; text-align: right;">Hành Động Xử Lý</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                        @php
                            $target = $report->reportable;
                            $wedding = $report->wedding;
                            $targetTypeName = str_contains($report->reportable_type, 'WeddingWish') ? 'Lời Chúc Phúc' : (str_contains($report->reportable_type, 'GuestPhoto') ? 'Ảnh Khách Tải Lên' : 'Nội Dung Khác');
                        @endphp
                        <tr style="border-bottom: 1px solid #F3F4F6;">
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <strong>#{{ $report->id }}</strong>
                                <div style="font-size: 11px; color: #9CA3AF; margin-top: 4px;">
                                    {{ $report->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #111827;">
                                    {{ $wedding ? "{$wedding->groom_name} & {$wedding->bride_name}" : "Đám cưới #{$report->wedding_id}" }}
                                </div>
                                <span style="display: inline-block; margin-top: 4px; font-size: 11px; background: #EEF2F6; color: #374151; padding: 2px 6px; border-radius: 4px;">
                                    {{ $targetTypeName }}
                                </span>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div class="mayhen_safe_preview">
                                    <div id="content_blur_{{ $report->id }}" class="mayhen_safe_blur">
                                        @if($target && str_contains($report->reportable_type, 'WeddingWish'))
                                            <div style="font-weight: 600; color: #1F2937; margin-bottom: 2px;">{{ $target->name }}:</div>
                                            <div style="color: #4B5563; font-style: italic;">"{{ $target->message }}"</div>
                                        @elseif($target && str_contains($report->reportable_type, 'GuestPhoto'))
                                            <div style="font-weight: 600; color: #1F2937; margin-bottom: 4px;">Người đăng: {{ $target->uploader_name }}</div>
                                            @if(!empty($target->photo_url))
                                                <img src="{{ $target->photo_url }}" alt="Báo cáo" style="max-width: 140px; max-height: 90px; border-radius: 4px; object-fit: cover;">
                                            @endif
                                            @if(!empty($target->caption))
                                                <div style="font-size: 12px; color: #6B7280; margin-top: 2px;">{{ $target->caption }}</div>
                                            @endif
                                        @else
                                            <span style="color: #9CA3AF;">[Nội dung gốc đã bị gỡ bỏ hoặc không tìm thấy]</span>
                                        @endif
                                    </div>
                                    <button type="button" class="mayhen_safe_overlay_btn" onclick="var el = document.getElementById('content_blur_{{ $report->id }}'); el.classList.toggle('revealed'); this.innerText = el.classList.contains('revealed') ? 'Ẩn lại' : 'Nhấp để xem';">
                                        Nhấp để xem
                                    </button>
                                </div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span style="background: #FEE2E2; color: #991B1B; font-weight: 600; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                                    {{ $report->reason_label }}
                                </span>
                                @if(!empty($report->details))
                                    <div style="font-size: 12px; color: #4B5563; margin-top: 6px; line-height: 1.4;">
                                        {{ $report->details }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div>{{ $report->reporter_name ?: 'Khách ẩn danh' }}</div>
                                @if(!empty($report->reporter_phone))
                                    <div style="font-size: 11px; color: #6B7280;">SĐT: {{ $report->reporter_phone }}</div>
                                @endif
                                <div style="font-size: 11px; color: #9CA3AF;">IP: {{ $report->reporter_ip }}</div>
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <span class="mayhen_admin_badge {{ $report->status_badge_class }}">
                                    {{ $report->status_label }}
                                </span>
                                @if($report->resolved_at)
                                    <div style="font-size: 11px; color: #9CA3AF; margin-top: 4px;">
                                        Bởi {{ $report->resolver?->name ?? 'Admin' }} lúc {{ $report->resolved_at->format('d/m H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td style="padding: 14px 16px; vertical-align: top; text-align: right;">
                                @if($report->isPending())
                                    <div class="mayhen_action_group" style="justify-content: flex-end;">
                                        <!-- Form Ẩn -->
                                        <form action="{{ route('admin.moderation.resolve', $report->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="action" value="hide">
                                            <button type="submit" class="mayhen_action_btn mayhen_btn_hide" title="Ẩn khỏi thiệp cưới">
                                                Ẩn Đi
                                            </button>
                                        </form>

                                        <!-- Form Xóa -->
                                        <form action="{{ route('admin.moderation.resolve', $report->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn nội dung này khỏi cơ sở dữ liệu?');">
                                            @csrf
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="mayhen_action_btn mayhen_btn_delete" title="Xóa vĩnh viễn">
                                                Xóa
                                            </button>
                                        </form>

                                        <!-- Form Bác bỏ (An toàn) -->
                                        <form action="{{ route('admin.moderation.resolve', $report->id) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <input type="hidden" name="action" value="dismiss">
                                            <button type="submit" class="mayhen_action_btn mayhen_btn_dismiss" title="Bác bỏ báo cáo và giữ hiển thị">
                                                An Toàn
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span style="font-size: 12px; color: #9CA3AF;">Đã xử lý</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: #6B7280;">
                                <div style="font-size: 28px; margin-bottom: 8px;">✨</div>
                                <div>Không có hồ sơ vi phạm nào trong danh mục này.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #E5E7EB;">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

