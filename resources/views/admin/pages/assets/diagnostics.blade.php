@extends('admin.layouts.master')

@section('title', 'Chẩn Đoán Lỗi Ảnh Thiệp & Phục Hồi Tài Nguyên')

@section('content')
<div class="mayhen_admin_asset_diagnostics" style="padding: 24px; max-width: 1400px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px;">
                    Chẩn Đoán Lỗi Ảnh Thiệp &amp; Phục Hồi Tài Nguyên
                </h1>
                <span style="font-size: 11px; font-weight: 700; background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Luồng F • Mục 60 PRD V3
                </span>
            </div>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Quét phát hiện ảnh mất kết nối (404/Missing file), URL ngoại vi vi phạm chuẩn 100% Local Assets và trạm phục hồi ảnh mẫu fallback 1-chạm.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.media.index') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>
                Thư Viện Media
            </a>
            <a href="{{ route('admin.assets.diagnostics.export') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                Xuất Báo Cáo CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #991b1b; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            {{ session('error') }}
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tổng Số Thiệp Toàn Hệ Thống
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #1e293b;">
                    {{ number_format($stats['total_scanned_invitations']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">thiệp cưới</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Lỗi Asset Trên Trang
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: {{ $stats['page_issues_count'] > 0 ? '#b91c1c' : '#059669' }};">
                    {{ number_format($stats['page_issues_count']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">vấn đề</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Mất Ảnh Bìa / Cặp Đôi (Critical)
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #b91c1c;">
                    {{ number_format($stats['page_critical_count']) }}
                </div>
                <span style="font-size: 12px; color: #b91c1c; font-weight: 600;">Cần phục hồi ngay</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Link Ngoại Vi Cần Nội Địa Hóa
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #d97706;">
                    {{ number_format($stats['page_external_count']) }}
                </div>
                <span style="font-size: 12px; color: #d97706; font-weight: 600;">Untrusted CDN</span>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.assets.diagnostics.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex: 1; min-width: 280px; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 220px;">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên thiệp, đường dẫn slug hoặc tên cặp đôi..." style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box; outline: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 12px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
                </div>

                <select name="template_id" style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; background: #ffffff; color: #334155; outline: none;">
                    <option value="">Tất cả mẫu thiết kế</option>
                    @foreach($templates as $tmpl)
                        <option value="{{ $tmpl->id }}" {{ $filters['template_id'] == $tmpl->id ? 'selected' : '' }}>
                            {{ $tmpl->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" style="padding: 10px 20px; background: #1e293b; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Chẩn Đoán
                </button>

                @if($filters['q'] || $filters['template_id'])
                    <a href="{{ route('admin.assets.diagnostics.index') }}" style="display: inline-flex; align-items: center; padding: 10px 14px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 13px; text-decoration: none;">
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
                        <th style="padding: 14px 20px;">Thiệp Cưới &amp; Cặp Đôi</th>
                        <th style="padding: 14px 20px;">Mẫu Thiết Kế</th>
                        <th style="padding: 14px 20px; text-align: center;">Điểm Sức Khỏe Asset</th>
                        <th style="padding: 14px 20px; text-align: center;">Kết Quả Chẩn Đoán</th>
                        <th style="padding: 14px 20px; text-align: right;">Trạm Phục Hồi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inspected as $item)
                        @php
                            $inv = $item['invitation'];
                            $rep = $item['report'];
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fafaf9'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: #1e293b; font-size: 14px; margin-bottom: 4px;">
                                    {{ $inv->title }}
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
                                    <span>Cặp đôi: <strong>{{ $inv->wedding?->groom_name }} &amp; {{ $inv->wedding?->bride_name }}</strong></span>
                                    <span>•</span>
                                    <a href="/thiep/{{ $inv->slug }}" target="_blank" style="color: #1d4ed8; text-decoration: none;">/thiep/{{ $inv->slug }} &nearr;</a>
                                </div>
                            </td>

                            <td style="padding: 16px 20px;">
                                <div style="font-weight: 600; color: #334155;">
                                    {{ $inv->template?->name ?: 'Celestial Luxury' }}
                                </div>
                                <span style="font-size: 11px; color: #64748b;">
                                    {{ $rep['total_assets_checked'] }} tài nguyên media
                                </span>
                            </td>

                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="font-size: 14px; font-weight: 700; color: {{ $rep['health_score'] >= 90 ? '#059669' : ($rep['health_score'] >= 60 ? '#d97706' : '#b91c1c') }}; margin-bottom: 4px;">
                                    {{ $rep['health_score'] }}%
                                </div>
                                <div style="width: 100px; height: 6px; background: #e2e8f0; border-radius: 3px; margin: 0 auto; overflow: hidden;">
                                    <div style="width: {{ $rep['health_score'] }}%; height: 100%; background: {{ $rep['health_score'] >= 90 ? '#10b981' : ($rep['health_score'] >= 60 ? '#f59e0b' : '#ef4444') }}; border-radius: 3px;"></div>
                                </div>
                            </td>

                            <td style="padding: 16px 20px; text-align: center;">
                                @if(! $rep['has_issues'])
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 12px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 700;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        100% Hoàn Hảo
                                    </span>
                                @else
                                    <div style="display: flex; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                        @if($rep['critical_count'] > 0)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 10px; background: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 700;">
                                                {{ $rep['critical_count'] }} Lỗi Nghiêm Trọng
                                            </span>
                                        @endif
                                        @if($rep['external_count'] > 0)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 10px; background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 700;">
                                                {{ $rep['external_count'] }} Link Ngoài
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td style="padding: 16px 20px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" onclick="inspectInvitationModal({{ $inv->id }})" style="padding: 6px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; color: #1e293b; font-size: 12px; font-weight: 600; cursor: pointer;">
                                        Chi Tiết Lỗi
                                    </button>
                                    @if($rep['has_issues'])
                                        <form method="POST" action="{{ route('admin.assets.diagnostics.batch_repair', $inv->id) }}" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn tự động gán fallback mẫu cho toàn bộ ảnh lỗi của thiệp này?')">
                                            @csrf
                                            <button type="submit" style="padding: 6px 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; color: #1d4ed8; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                Sửa Tự Động
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center; color: #64748b;">
                                Không tìm thấy thiệp cưới nào cần chẩn đoán.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginator->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #ffffff;">
                {{ $paginator->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL: Chi Tiết Chẩn Đoán & Phục Hồi Asset -->
<div id="diagnostics-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 750px; width: 90%; max-height: 85vh; display: flex; flex-direction: column; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0;" id="modal-invitation-title">
                    Chẩn Đoán Tài Nguyên Thiệp
                </h3>
                <span style="font-size: 12px; color: #64748b;" id="modal-invitation-subtitle">Đang tải dữ liệu...</span>
            </div>
            <button type="button" onclick="closeDiagnosticsModal()" style="border: none; background: transparent; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="overflow-y: auto; flex: 1; padding-right: 4px;" id="modal-issues-container">
            <!-- Render via JS -->
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
            <button type="button" onclick="closeDiagnosticsModal()" style="padding: 8px 16px; background: #f1f5f9; color: #475569; border: none; border-radius: 6px; font-size: 13px; cursor: pointer;">Đóng</button>
        </div>
    </div>
</div>

<script>
let currentActiveInvitationId = null;

function inspectInvitationModal(invitationId) {
    currentActiveInvitationId = invitationId;
    const modal = document.getElementById('diagnostics-modal');
    const container = document.getElementById('modal-issues-container');
    const sub = document.getElementById('modal-invitation-subtitle');
    const title = document.getElementById('modal-invitation-title');

    modal.style.display = 'flex';
    container.innerHTML = '<div style="padding: 30px; text-align: center; color: #64748b;">Đang chẩn đoán tài nguyên media...</div>';

    fetch('/admin/assets/diagnostics/scan/' + invitationId)
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            container.innerHTML = '<div style="padding: 30px; color: #ef4444; text-align: center;">' + (data.message || 'Lỗi chẩn đoán.') + '</div>';
            return;
        }

        const report = data.report;
        title.textContent = report.title;
        sub.textContent = 'Tổng tài nguyên kiểm tra: ' + report.total_assets_checked + ' | Điểm sức khỏe: ' + report.health_score + '%';

        if (!report.has_issues || report.issues.length === 0) {
            container.innerHTML = '<div style="padding: 40px; text-align: center; color: #059669; font-weight: 600;">Toàn bộ tài nguyên media của thiệp cưới này đều hợp lệ 100%! Không phát hiện ảnh mất kết nối.</div>';
            return;
        }

        let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
        report.issues.forEach(issue => {
            const isCrit = issue.severity === 'critical';
            html += `
                <div style="background: ${isCrit ? '#fff1f2' : '#fefce8'}; border: 1px solid ${isCrit ? '#fecdd3' : '#fef08a'}; border-radius: 8px; padding: 14px 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                        <div>
                            <span style="font-weight: 700; font-size: 13px; color: #1e293b;">${issue.label}</span>
                            <span style="font-size: 11px; padding: 2px 6px; border-radius: 6px; margin-left: 6px; background: ${isCrit ? '#fee2e2' : '#fef3c7'}; color: ${isCrit ? '#b91c1c' : '#b45309'}; font-weight: 700;">
                                ${isCrit ? 'NGHIÊM TRỌNG' : 'CẢNH BÁO'}
                            </span>
                        </div>
                        <span style="font-size: 11px; color: #64748b;">${issue.field}</span>
                    </div>

                    <div style="font-size: 12px; color: #334155; margin-bottom: 8px;">
                        ${issue.message}
                    </div>

                    <div style="font-size: 11px; color: #64748b; margin-bottom: 10px; word-break: break-all;">
                        Đường dẫn hiện tại: <code>${issue.url || '(Trống)'}</code>
                    </div>

                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button type="button" onclick="applyFallbackAction(${invitationId}, '${issue.field}', '${issue.suggested_fallback}')" style="padding: 6px 12px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; color: #1e293b;">
                            Gán Ảnh Fallback Mẫu
                        </button>
                        ${issue.type === 'external_untrusted' ? `
                            <button type="button" onclick="localizeAssetAction(${invitationId}, '${issue.field}', '${issue.url}')" style="padding: 6px 12px; background: #059669; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; color: #ffffff;">
                                Tải Về Lưu Cục Bộ (Localize)
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    })
    .catch(err => {
        console.error(err);
        container.innerHTML = '<div style="padding: 30px; color: #ef4444; text-align: center;">Lỗi kết nối máy chủ.</div>';
    });
}

function closeDiagnosticsModal() {
    document.getElementById('diagnostics-modal').style.display = 'none';
}

function applyFallbackAction(invitationId, field, fallbackUrl) {
    if (!confirm('Áp dụng ảnh fallback chuẩn mẫu cho trường này?')) return;

    fetch('/admin/assets/diagnostics/' + invitationId + '/apply-fallback', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ field: field, fallback_url: fallbackUrl })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            inspectInvitationModal(invitationId);
        } else {
            alert(data.message || 'Lỗi phục hồi.');
        }
    });
}

function localizeAssetAction(invitationId, field, externalUrl) {
    if (!confirm('Tải ảnh này về máy chủ Mây Hẹn để đảm bảo 100% Local Asset?')) return;

    fetch('/admin/assets/diagnostics/' + invitationId + '/localize', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ field: field, external_url: externalUrl })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            inspectInvitationModal(invitationId);
        } else {
            alert(data.message || 'Lỗi nội địa hóa.');
        }
    });
}
</script>
@endsection

