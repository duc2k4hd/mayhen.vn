@extends('admin.layouts.master')

@section('title', 'Đài Giám Sát Quét Thiệp Giấy AI OCR')

@section('content')
<div class="mayhen_admin_paper_scans" style="padding: 24px; max-width: 1400px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px;">
                    Đài Giám Sát Quét Thiệp Giấy AI OCR
                </h1>
                <span style="font-size: 11px; font-weight: 700; background: #ede9fe; color: #6d28d9; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Vision AI Observatory
                </span>
            </div>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Giám sát chất lượng trích xuất tự động từ ảnh chụp thiệp giấy truyền thống sang dữ liệu số theo Mục 18, 23 PRD V3.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.ai_observatory.index') }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg>
                Đài Quan Sát AI
            </a>
            <a href="{{ route('admin.paper_scans.export', request()->all()) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
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

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tổng Lượt Quét Thiệp Giấy
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #1e293b;">
                    {{ number_format($stats['total_scans']) }}
                </div>
                <span style="font-size: 12px; color: #64748b;">lượt chụp</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Độ Tin Cậy Cao (&ge; 80%)
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #059669;">
                    {{ number_format($stats['high_confidence_scans']) }}
                </div>
                <span style="font-size: 12px; color: #059669; font-weight: 600;">High Accuracy</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đã Áp Dụng Tạo Thiệp Thật
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #6366f1;">
                    {{ number_format($stats['applied_scans']) }}
                </div>
                <span style="font-size: 12px; color: #6366f1; font-weight: 600;">Wedding Synced</span>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Điểm Tin Cậy Trung Bình
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #d97706;">
                    {{ $stats['avg_confidence'] }}%
                </div>
                <span style="font-size: 12px; color: #64748b;">AI Confidence</span>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.paper_scans.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex: 1; min-width: 280px; flex-wrap: wrap;">
                <div style="position: relative; flex: 1; min-width: 220px;">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên người dùng, tên cặp đôi hoặc nội dung trích xuất..." style="width: 100%; padding: 10px 14px 10px 38px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box; outline: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; top: 12px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
                </div>

                <select name="status" style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; background: #ffffff; color: #334155; outline: none;">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="parsed" {{ $filters['status'] === 'parsed' ? 'selected' : '' }}>Đã trích xuất (Chờ duyệt)</option>
                    <option value="applied" {{ $filters['status'] === 'applied' ? 'selected' : '' }}>Đã áp dụng vào thiệp</option>
                    <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Trích xuất thất bại</option>
                </select>

                <select name="confidence" style="padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; background: #ffffff; color: #334155; outline: none;">
                    <option value="">Tất cả mức độ tin cậy</option>
                    <option value="high" {{ $filters['confidence'] === 'high' ? 'selected' : '' }}>Cao (&ge; 80%)</option>
                    <option value="medium" {{ $filters['confidence'] === 'medium' ? 'selected' : '' }}>Trung bình (50% - 79%)</option>
                    <option value="low" {{ $filters['confidence'] === 'low' ? 'selected' : '' }}>Thấp (&lt; 50%)</option>
                </select>

                <button type="submit" style="padding: 10px 20px; background: #1e293b; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Lọc dữ liệu
                </button>

                @if($filters['q'] || $filters['status'] !== 'all' || $filters['confidence'])
                    <a href="{{ route('admin.paper_scans.index') }}" style="display: inline-flex; align-items: center; padding: 10px 14px; background: #f1f5f9; color: #475569; border-radius: 8px; font-size: 13px; text-decoration: none;">
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
                        <th style="padding: 14px 20px;">Ảnh Thiệp Giấy</th>
                        <th style="padding: 14px 20px;">Người Dùng</th>
                        <th style="padding: 14px 20px;">Dữ Liệu Trích Xuất (AI OCR)</th>
                        <th style="padding: 14px 20px; text-align: center;">Độ Tin Cậy</th>
                        <th style="padding: 14px 20px; text-align: center;">Trạng Thái</th>
                        <th style="padding: 14px 20px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scans as $scan)
                        @php
                            $p = $scan->verified_data ?: ($scan->parsed_data ?? []);
                            $confPercent = round($scan->confidence_score * 100);
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#fafaf9'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 14px 20px; width: 80px;">
                                <div style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; background: #f1f5f9; cursor: pointer;" onclick="viewScanDetails({{ $scan->id }})">
                                    <img src="{{ $scan->image_url }}" alt="Scan #{{ $scan->id }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='/assets/client/images/template-placeholder.webp';">
                                </div>
                            </td>

                            <td style="padding: 14px 20px;">
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 2px;">
                                    {{ $scan->user?->name ?: 'Người dùng ẩn danh' }}
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $scan->user?->email }}
                                </div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">
                                    Quét: {{ $scan->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            <td style="padding: 14px 20px;">
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 2px;">
                                    {{ ($p['groom_name'] ?? '—') }} &amp; {{ ($p['bride_name'] ?? '—') }}
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-bottom: 2px;">
                                    Ngày: <strong>{{ $p['wedding_date'] ?? 'Chưa xác định' }}</strong>
                                </div>
                                <div style="font-size: 11px; color: #64748b; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    Sảnh: {{ $p['venue_name'] ?? 'Chưa xác định' }}
                                </div>
                            </td>

                            <td style="padding: 14px 20px; text-align: center;">
                                <div style="font-weight: 700; font-size: 13px; color: {{ $confPercent >= 80 ? '#059669' : ($confPercent >= 50 ? '#d97706' : '#b91c1c') }}; margin-bottom: 4px;">
                                    {{ $confPercent }}%
                                </div>
                                <div style="width: 80px; height: 5px; background: #e2e8f0; border-radius: 3px; margin: 0 auto; overflow: hidden;">
                                    <div style="width: {{ $confPercent }}%; height: 100%; background: {{ $confPercent >= 80 ? '#10b981' : ($confPercent >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                                </div>
                            </td>

                            <td style="padding: 14px 20px; text-align: center;">
                                @if($scan->status === 'applied')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 10px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 700;">
                                        Đã Áp Dụng
                                    </span>
                                @elseif($scan->status === 'parsed')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 10px; background: #eff6ff; color: #1d4ed8; font-size: 11px; font-weight: 700;">
                                        Đã Trích Xuất
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 10px; background: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 700;">
                                        Thất Bại
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 14px 20px; text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <button type="button" onclick="viewScanDetails({{ $scan->id }})" style="padding: 6px 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; color: #1e293b; font-size: 12px; font-weight: 600; cursor: pointer;">
                                        Hiệu Đính &amp; Xem
                                    </button>
                                    @if($scan->status !== 'applied')
                                        <form method="POST" action="{{ route('admin.paper_scans.apply', $scan->id) }}" style="display: inline;" onsubmit="return confirm('Áp dụng dữ liệu trích xuất này vào thiệp cưới của người dùng?')">
                                            @csrf
                                            <button type="submit" style="padding: 6px 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; color: #1d4ed8; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                Áp Dụng
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #64748b;">
                                Không tìm thấy lượt quét thiệp giấy nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($scans->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #ffffff;">
                {{ $scans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- MODAL: Chi Tiết & Hiệu Đính Dữ Liệu Thiệp Giấy -->
<div id="scan-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 900px; width: 92%; max-height: 90vh; display: flex; flex-direction: column; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0;">
                    Hiệu Đính Dữ Liệu Thiệp Giấy AI OCR
                </h3>
                <span style="font-size: 12px; color: #64748b;" id="modal-scan-subtitle">Đang tải...</span>
            </div>
            <button type="button" onclick="closeScanModal()" style="border: none; background: transparent; font-size: 20px; cursor: pointer; color: #94a3b8;">&times;</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; overflow-y: auto; flex: 1;" id="modal-scan-content">
            <!-- Cột Trái: Ảnh Thiệp Giấy -->
            <div>
                <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px;">ẢNH THIỆP GỐC:</div>
                <div style="width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #0f172a; text-align: center;">
                    <img id="modal-scan-image" src="" alt="Ảnh thiệp giấy" style="max-width: 100%; max-height: 400px; object-fit: contain;">
                </div>
            </div>

            <!-- Cột Phải: Form Hiệu Đính -->
            <div>
                <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px;">DỮ LIỆU ĐÃ TRÍCH XUẤT (CÓ THỂ CHỈNH SỬA):</div>
                <form id="verify-form" onsubmit="submitVerifyForm(event)">
                    <input type="hidden" id="modal-scan-id" value="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Tên Chú Rể</label>
                            <input type="text" id="verify-groom-name" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Tên Cô Dâu</label>
                            <input type="text" id="verify-bride-name" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Ngày Tổ Chức Hôn Lễ</label>
                        <input type="date" id="verify-wedding-date" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Tên Sảnh Tiệc / Trung Tâm Tiệc Cưới</label>
                        <input type="text" id="verify-venue-name" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 4px;">Địa Chỉ Tổ Chức</label>
                        <textarea id="verify-venue-address" rows="2" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-family: inherit; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                        <button type="submit" id="btn-save-verify" style="padding: 8px 16px; background: #1e293b; color: #ffffff; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
                            Lưu Dữ Liệu Hiệu Đính
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function viewScanDetails(scanId) {
    const modal = document.getElementById('scan-modal');
    modal.style.display = 'flex';

    fetch('/admin/paper-scans/' + scanId)
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Không tìm thấy thông tin lượt quét.');
            modal.style.display = 'none';
            return;
        }

        const s = data.scan;
        document.getElementById('modal-scan-id').value = s.id;
        document.getElementById('modal-scan-subtitle').textContent = 'Người gửi: ' + (s.user_name || 'Khách') + ' | Độ tin cậy: ' + s.confidence_percent + '% (' + s.ai_model + ')';
        document.getElementById('modal-scan-image').src = s.image_url;

        const p = s.verified_data || s.parsed_data || {};
        document.getElementById('verify-groom-name').value = p.groom_name || '';
        document.getElementById('verify-bride-name').value = p.bride_name || '';
        document.getElementById('verify-wedding-date').value = p.wedding_date || '';
        document.getElementById('verify-venue-name').value = p.venue_name || '';
        document.getElementById('verify-venue-address').value = p.venue_address || '';
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối máy chủ.');
        modal.style.display = 'none';
    });
}

function closeScanModal() {
    document.getElementById('scan-modal').style.display = 'none';
}

function submitVerifyForm(e) {
    e.preventDefault();
    const scanId = document.getElementById('modal-scan-id').value;
    const btn = document.getElementById('btn-save-verify');

    const verifiedData = {
        groom_name: document.getElementById('verify-groom-name').value,
        bride_name: document.getElementById('verify-bride-name').value,
        wedding_date: document.getElementById('verify-wedding-date').value,
        venue_name: document.getElementById('verify-venue-name').value,
        venue_address: document.getElementById('verify-venue-address').value,
    };

    btn.disabled = true;
    btn.textContent = 'Đang lưu...';

    fetch('/admin/paper-scans/' + scanId + '/verify', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ verified_data: verifiedData })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Lưu Dữ Liệu Hiệu Đính';
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Lỗi lưu dữ liệu.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Lưu Dữ Liệu Hiệu Đính';
        console.error(err);
        alert('Lỗi kết nối máy chủ.');
    });
}
</script>
@endsection

