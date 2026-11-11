@extends('admin.layouts.master')

@section('title', 'Lưu Trữ Kỷ Niệm & Hậu Sự Kiện')

@section('content')
<div class="mayhen_admin_keepsakes_container" style="padding: 24px; max-width: 1400px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px;">
                    Lưu Trữ Kỷ Niệm & Hậu Sự Kiện
                </h1>
                <span style="font-size: 11px; font-weight: 700; background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Post-Wedding Archive
                </span>
            </div>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Quản trị trang kỷ niệm hậu hôn lễ, hộp thư thời gian (Time Capsule) và xuất gói dữ liệu lưu trữ trọn đời theo Mục 36 PRD V3.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.wishes.guestbook.index') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path><path d="M6 6h10"></path><path d="M6 10h10"></path></svg>
                Sổ Lưu Bút Album
            </a>
            <a href="{{ route('admin.gifts.index') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                Sổ Mừng Cưới
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tiệc Cưới Đã Diễn Ra
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #1e293b;">
                    {{ number_format($stats['total_past_weddings']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">tiệc cưới</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đang Mở Trang Kỷ Niệm
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #059669;">
                    {{ number_format($stats['active_keepsakes']) }}
                </div>
                <span style="font-size: 12px; color: #059669; font-weight: 600;">Active Mode</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Hộp Thư Thời Gian Niêm Phong
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #d97706;">
                    {{ number_format($stats['sealed_capsules']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">
                    / {{ number_format($stats['sealed_capsules'] + $stats['unlocked_capsules']) }} tổng thư
                </span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đã Đóng Gói Lưu Trữ
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #6366f1;">
                    {{ number_format($stats['exported_archives']) }}
                </div>
                <span style="font-size: 12px; color: #6366f1; font-weight: 600;">Archive Exported</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.keepsakes.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex: 1; min-width: 280px;">
                <div style="position: relative; flex: 1;">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên cô dâu, chú rể, tên tiệc hoặc email chủ tiệc..." style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box; outline: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 12px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
                </div>
                <select name="status" style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; background: #ffffff; color: #334155; outline: none;">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="enabled" {{ $filters['status'] === 'enabled' ? 'selected' : '' }}>Đang mở Chế độ Kỷ niệm</option>
                    <option value="disabled" {{ $filters['status'] === 'disabled' ? 'selected' : '' }}>Chưa mở Chế độ Kỷ niệm</option>
                    <option value="has_capsules" {{ $filters['status'] === 'has_capsules' ? 'selected' : '' }}>Có Hộp thư Thời gian</option>
                    <option value="exported" {{ $filters['status'] === 'exported' ? 'selected' : '' }}>Đã xuất gói lưu trữ</option>
                </select>
                <button type="submit" style="padding: 10px 20px; background: #1e293b; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                    Lọc dữ liệu
                </button>
                @if($filters['q'] || $filters['status'] !== 'all')
                    <a href="{{ route('admin.keepsakes.index') }}" style="display: inline-flex; align-items: center; padding: 10px 14px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 13px; text-decoration: none;">
                        Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Table -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;">
                        <th style="padding: 14px 20px;">Tiệc Cưới & Cặp Đôi</th>
                        <th style="padding: 14px 20px;">Ngày Cưới & Cột Mốc</th>
                        <th style="padding: 14px 20px; text-align: center;">Chế Độ Kỷ Niệm</th>
                        <th style="padding: 14px 20px; text-align: center;">Hộp Thư Thời Gian</th>
                        <th style="padding: 14px 20px; text-align: center;">Dữ Liệu Hậu Sự Kiện</th>
                        <th style="padding: 14px 20px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody style="divide-y: 1px solid #e2e8f0;">
                    @forelse($weddings as $w)
                        @php
                            $ks = $w->keepsake;
                            $isEnabled = $ks ? $ks->is_enabled : false;
                            $milestone = $ks ? $ks->milestone_label : ($w->wedding_date ? (new \App\Models\WeddingKeepsake(['anniversary_date' => $w->wedding_date]))->milestone_label : 'Chưa xác định');
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fafaf9'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 4px;">
                                    {{ $w->title ?: ($w->groom_name . ' & ' . $w->bride_name) }}
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b;">
                                    <span>Chủ tiệc: <strong>{{ $w->user?->name ?? 'Vô danh' }}</strong></span>
                                    <span>•</span>
                                    <span>{{ $w->user?->email }}</span>
                                </div>
                            </td>

                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: #334155; margin-bottom: 4px;">
                                    {{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt ngày' }}
                                </div>
                                <span style="display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 10px; background: #eff6ff; color: #1d4ed8;">
                                    {{ $milestone }}
                                </span>
                            </td>

                            <td style="padding: 16px 20px; text-align: center;">
                                <button type="button" onclick="toggleKeepsakeMode({{ $w->id }})" id="btn-toggle-{{ $w->id }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; {{ $isEnabled ? 'background: #ecfdf5; color: #047857; border-color: #a7f3d0;' : 'background: #f1f5f9; color: #64748b; border-color: #cbd5e1;' }}">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $isEnabled ? '#10b981' : '#94a3b8' }};"></span>
                                    <span id="text-toggle-{{ $w->id }}">{{ $isEnabled ? 'Đang Mở' : 'Tạm Tắt' }}</span>
                                </button>
                            </td>

                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="font-weight: 700; color: #1e293b;">
                                    {{ $w->time_capsules_count }}
                                </div>
                                <span style="font-size: 11px; color: #64748b;">bức thư tương lai</span>
                            </td>

                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 8px; font-size: 11px; color: #64748b;">
                                    <span>Khách: <strong>{{ $w->guests_count }}</strong></span>
                                    <span>•</span>
                                    <span>Quà: <strong>{{ $w->gifts_count }}</strong></span>
                                    <span>•</span>
                                    <span>Lời chúc: <strong>{{ $w->wishes_count }}</strong></span>
                                </div>
                                @if($ks && $ks->last_exported_at)
                                    <div style="font-size: 10px; color: #059669; margin-top: 4px;">
                                        Đã xuất: {{ $ks->last_exported_at->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>

                            <td style="padding: 16px 20px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('admin.keepsakes.show', $w->id) }}" style="padding: 6px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; color: #334155; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        Chi tiết &amp; Cấu hình
                                    </a>
                                    <a href="{{ route('admin.keepsakes.export_archive', $w->id) }}" title="Tải gói lưu trữ sự kiện (JSON)" style="padding: 6px 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; color: #1d4ed8; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #64748b;">
                                Không tìm thấy tiệc cưới nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($weddings->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #ffffff;">
                {{ $weddings->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function toggleKeepsakeMode(weddingId) {
    const btn = document.getElementById('btn-toggle-' + weddingId);
    const txt = document.getElementById('text-toggle-' + weddingId);
    if (!btn || !txt) return;

    btn.disabled = true;
    btn.style.opacity = '0.6';

    fetch('/admin/keepsakes/' + weddingId + '/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.style.opacity = '1';
        if (data.success) {
            if (data.is_enabled) {
                btn.style.background = '#ecfdf5';
                btn.style.color = '#047857';
                btn.style.borderColor = '#a7f3d0';
                btn.querySelector('span:first-child').style.background = '#10b981';
                txt.textContent = 'Đang Mở';
            } else {
                btn.style.background = '#f1f5f9';
                btn.style.color = '#64748b';
                btn.style.borderColor = '#cbd5e1';
                btn.querySelector('span:first-child').style.background = '#94a3b8';
                txt.textContent = 'Tạm Tắt';
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.style.opacity = '1';
        console.error(err);
        alert('Lỗi kết nối máy chủ.');
    });
}
</script>
@endsection

