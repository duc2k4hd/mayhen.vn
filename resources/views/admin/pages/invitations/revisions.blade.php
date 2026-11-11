@extends('admin.layouts.master')

@section('title', 'Lịch Sử Phiên Bản & Phục Hồi Thiệp Cưới')

@section('content')
<div class="mayhen_admin_revisions_container" style="display: flex; flex-direction: column; gap: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.invitations.index') }}" style="color: inherit; text-decoration: none;">Quản lý thiệp cưới</a>
                <span>/</span>
                <a href="{{ route('admin.invitations.show', $invitation->id) }}" style="color: inherit; text-decoration: none;">{{ $invitation->title }}</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Lịch sử phiên bản</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Lịch Sử Phiên Bản & Phục Hồi Dữ Liệu An Toàn</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Theo dõi nhật ký snapshot tự động (Autosave, Xuất bản, Chuyển mẫu), so sánh sai khác (Change Diff) và khôi phục dữ liệu không làm mất lịch sử.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.invitations.show', $invitation->id) }}" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Quay lại chi tiết thiệp
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; color: #166534; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Invitation Info Card -->
    <div class="mayhen_admin_card" style="padding: 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Thiệp cưới</div>
            <div style="font-size: 16px; font-weight: 700; color: #1c1917; margin-top: 4px;">{{ $invitation->title }}</div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">Slug: <code style="background: #f5f5f4; padding: 1px 5px; border-radius: 3px;">{{ $invitation->slug }}</code></div>
        </div>

        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Cặp đôi / Chủ sở hữu</div>
            <div style="font-size: 14px; font-weight: 600; color: #1c1917; margin-top: 4px;">{{ $invitation->wedding?->groom_name }} & {{ $invitation->wedding?->bride_name }}</div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">Tài khoản: {{ $invitation->wedding?->user?->name ?? 'Vãng lai' }}</div>
        </div>

        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Mẫu thiết kế</div>
            <div style="font-size: 14px; font-weight: 600; color: #1c1917; margin-top: 4px;">{{ $invitation->template?->name ?? 'Mặc định' }}</div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">Schema: v{{ $invitation->schema_version ?? '1.0' }}</div>
        </div>

        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Phiên bản hiện tại</div>
            <div style="font-size: 16px; font-weight: 700; color: #047857; margin-top: 4px;">
                v{{ $invitation->latestRevision?->version_number ?? 1 }}
            </div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                Trạng thái: {{ $invitation->is_published ? 'Đã xuất bản' : 'Bản nháp' }}
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="mayhen_admin_card" style="padding: 14px 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <form method="GET" action="{{ route('admin.invitations.revisions.index', $invitation->id) }}" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <label style="font-size: 13px; font-weight: 600; color: #44403c;">Lọc theo loại:</label>
            <select name="type" onchange="this.form.submit()" style="padding: 6px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>Tất cả các mốc ({{ $revisions->total() }})</option>
                <option value="published" {{ $filters['type'] === 'published' ? 'selected' : '' }}>Xuất bản chính thức</option>
                <option value="manual_save" {{ $filters['type'] === 'manual_save' ? 'selected' : '' }}>Lưu thủ công</option>
                <option value="autosave" {{ $filters['type'] === 'autosave' ? 'selected' : '' }}>Tự động lưu (Autosave)</option>
                <option value="restored" {{ $filters['type'] === 'restored' ? 'selected' : '' }}>Phục hồi dữ liệu</option>
                <option value="template_switched" {{ $filters['type'] === 'template_switched' ? 'selected' : '' }}>Chuyển đổi mẫu</option>
            </select>
        </form>

        <div style="font-size: 12px; color: #78716c;">
            Hệ thống tự động lưu giữ tối đa 30 bản nháp gần nhất; các mốc Xuất bản và Phục hồi được bảo tồn vĩnh viễn.
        </div>
    </div>

    <!-- Revisions Table -->
    <div class="mayhen_admin_card" style="border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; font-family: var(--mh-admin-font);">
                <thead>
                    <tr style="background: #fafaf9; border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 14px 20px; font-weight: 600;">Phiên Bản</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Phân Loại</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Mô Tả & Tóm Tắt Thay Đổi</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Người Lưu / IP</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Thời Gian</th>
                        <th style="padding: 14px 20px; font-weight: 600; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revisions as $rev)
                        <tr style="border-bottom: 1px solid #f5f5f4; transition: background 0.15s ease; {{ $rev->id === $invitation->revision_id ? 'background: #f0fdf4;' : '' }}">
                            <!-- Version -->
                            <td style="padding: 16px 20px; vertical-align: top;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; font-weight: 700; font-size: 13px; {{ $rev->id === $invitation->revision_id ? 'background: #dcfce7; color: #15803d; border: 1px solid #86efac;' : 'background: #f5f5f4; color: #44403c;' }}">
                                        v{{ $rev->version_number }}
                                    </span>
                                    @if($rev->id === $invitation->revision_id)
                                        <span style="font-size: 10px; background: #22c55e; color: #ffffff; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Hiện tại</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Type -->
                            <td style="padding: 16px 16px; vertical-align: top;">
                                @php
                                    $badgeStyle = match($rev->revision_type) {
                                        'published' => 'background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                        'manual_save' => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
                                        'restored' => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
                                        'template_switched' => 'background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;',
                                        default => 'background: #f5f5f4; color: #57534e; border: 1px solid #e7e5e4;'
                                    };
                                @endphp
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; {{ $badgeStyle }}">
                                    {{ $rev->revision_type_label }}
                                </span>
                            </td>

                            <!-- Title & Diff Summary -->
                            <td style="padding: 16px 16px; vertical-align: top;">
                                <div style="font-weight: 600; color: #1c1917; font-size: 13px;">{{ $rev->title }}</div>
                                @if(!empty($rev->diff_summary) && is_array($rev->diff_summary))
                                    <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                                        @foreach(array_slice($rev->diff_summary, 0, 5) as $diffKey)
                                            <span style="background: #fafaf9; border: 1px solid #e7e5e4; padding: 1px 6px; border-radius: 4px; font-size: 11px; color: #78716c; font-family: monospace;">
                                                {{ $diffKey }}
                                            </span>
                                        @endforeach
                                        @if(count($rev->diff_summary) > 5)
                                            <span style="font-size: 11px; color: #a8a29e; align-self: center;">+{{ count($rev->diff_summary) - 5 }} mục khác</span>
                                        @endif
                                    </div>
                                @endif
                                <div style="font-size: 11px; color: #a8a29e; margin-top: 4px; font-family: monospace;">
                                    SHA: {{ substr($rev->checksum, 0, 12) }}...
                                </div>
                            </td>

                            <!-- Creator & IP -->
                            <td style="padding: 16px 16px; vertical-align: top; font-size: 12px; color: #44403c;">
                                <div style="font-weight: 500;">{{ $rev->creator?->name ?? 'Khách vãng lai / Studio' }}</div>
                                <div style="font-size: 11px; color: #a8a29e; margin-top: 2px;">{{ $rev->ip_address }}</div>
                            </td>

                            <!-- Created At -->
                            <td style="padding: 16px 16px; vertical-align: top; font-size: 12px; color: #78716c;">
                                <div>{{ $rev->created_at->format('d/m/Y H:i:s') }}</div>
                                <div style="font-size: 11px; color: #a8a29e; margin-top: 2px;">{{ $rev->created_at->diffForHumans() }}</div>
                            </td>

                            <!-- Actions -->
                            <td style="padding: 16px 20px; vertical-align: top; text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                    <!-- View Diff Button -->
                                    <button type="button" onclick="loadDiff({{ $rev->id }}, {{ $rev->version_number }})" style="background: #ffffff; border: 1px solid #d6d3d1; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #44403c; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 18l6-6-6-6"></path><path d="M8 6l-6 6 6 6"></path></svg>
                                        Xem Diff
                                    </button>

                                    <!-- Restore Button (if not already current) -->
                                    @if($rev->id !== $invitation->revision_id)
                                        <button type="button" onclick="openRestoreModal({{ $rev->id }}, {{ $rev->version_number }}, '{{ addslashes($rev->title) }}')" style="background: #fef3c7; border: 1px solid #fde68a; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #b45309; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                                            Phục Hồi
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px 20px; text-align: center; color: #78716c;">
                                <div style="font-size: 14px; font-weight: 500;">Chưa ghi nhận phiên bản snapshot nào cho thiệp cưới này.</div>
                                <div style="font-size: 12px; color: #a8a29e; margin-top: 4px;">Phiên bản sẽ tự động được tạo khi thiệp được lưu hoặc xuất bản trên Studio.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($revisions->hasPages())
            <div style="padding: 14px 20px; border-top: 1px solid #e7e5e4; background: #fafaf9;">
                {{ $revisions->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Xem So Sánh Sai Khác (Change Diff Modal) -->
<div id="diffModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 720px; width: 90%; max-height: 85vh; display: flex; flex-direction: column; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); font-family: var(--mh-admin-font); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f5f5f4; padding-bottom: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1917;">So Sánh Chi Tiết Sai Khác (Change Diff)</h3>
                <div id="diffSubtitle" style="font-size: 12px; color: #78716c; margin-top: 3px;">Đang tải dữ liệu...</div>
            </div>
            <button type="button" onclick="closeDiffModal()" style="background: none; border: none; font-size: 20px; color: #a8a29e; cursor: pointer;">&times;</button>
        </div>

        <div id="diffContent" style="flex: 1; overflow-y: auto; padding-right: 6px;">
            <!-- Dữ liệu diff render động -->
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 18px; padding-top: 12px; border-top: 1px solid #f5f5f4;">
            <button type="button" onclick="closeDiffModal()" style="padding: 8px 16px; font-size: 13px; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">Đóng cửa sổ</button>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Phục Hồi (Restore Modal) -->
<div id="restoreModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 460px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); font-family: var(--mh-admin-font); box-sizing: border-box;">
        <div style="display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1c1917;">Xác Nhận Phục Hồi Phiên Bản</h3>
                <div id="restoreModalVersionText" style="font-size: 13px; font-weight: 600; color: #b45309; margin-top: 2px;"></div>
            </div>
        </div>

        <p style="font-size: 13px; color: #4b5563; line-height: 1.5; margin: 0 0 16px 0;">
            Hành động này sẽ <strong>tạo một phiên bản mới</strong> mang cấu hình của bản được chọn và áp dụng ngay lập tức vào thiệp cưới. <strong>Toàn bộ lịch sử cũ vẫn được giữ nguyên vẹn 100%</strong>.
        </p>

        <form id="restoreForm" method="POST" action="">
            @csrf
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRestoreModal()" style="padding: 8px 14px; font-size: 13px; border-radius: 6px; background: #f3f4f6; border: 1px solid #d1d5db; color: #374151; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #b45309; border: none; color: #ffffff; cursor: pointer;">Xác Nhận Phục Hồi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function loadDiff(revisionId, versionNumber) {
        document.getElementById('diffModal').style.display = 'flex';
        document.getElementById('diffSubtitle').innerText = 'Đang so sánh phiên bản v' + versionNumber + ' với bản trước đó...';
        document.getElementById('diffContent').innerHTML = '<div style="padding: 30px; text-align: center; color: #78716c;">Đang xử lý thuật toán phân tích sai khác...</div>';

        fetch("{{ url('admin/invitations/' . $invitation->id . '/revisions') }}/" + revisionId + "/diff")
            .then(function(res) { return res.json(); })
            .then(function(json) {
                if (!json.success || !json.data) {
                    document.getElementById('diffContent').innerHTML = '<div style="color: #ef4444; padding: 20px;">Không thể tải dữ liệu so sánh.</div>';
                    return;
                }
                var d = json.data;
                var baseText = d.base ? 'Bản v' + d.base.version + ' (' + d.base.created_at + ')' : 'Khởi tạo ban đầu';
                document.getElementById('diffSubtitle').innerText = 'Bản v' + d.current.version + ' (' + d.current.created_at + ') so với ' + baseText + ' • ' + d.changes_count + ' mục thay đổi';

                if (d.changes.length === 0) {
                    document.getElementById('diffContent').innerHTML = '<div style="padding: 30px; text-align: center; color: #059669; font-weight: 600;">Không có sai khác về nội dung giữa hai phiên bản.</div>';
                    return;
                }

                var html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
                d.changes.forEach(function(item) {
                    var actionColor = item.action === 'added' ? '#059669' : (item.action === 'deleted' ? '#dc2626' : '#d97706');
                    var actionLabel = item.action === 'added' ? 'Thêm mới' : (item.action === 'deleted' ? 'Đã xóa' : 'Chỉnh sửa');
                    var actionBg = item.action === 'added' ? '#ecfdf5' : (item.action === 'deleted' ? '#fef2f2' : '#fffbeb');

                    html += '<div style="border: 1px solid #e7e5e4; border-radius: 8px; padding: 12px; background: ' + actionBg + ';">';
                    html += '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">';
                    html += '<span style="font-weight: 700; font-family: monospace; font-size: 13px; color: #1c1917;">' + item.key + '</span>';
                    html += '<span style="font-size: 11px; font-weight: 600; color: ' + actionColor + '; text-transform: uppercase;">' + actionLabel + '</span>';
                    html += '</div>';

                    if (item.action === 'modified') {
                        html += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px; font-family: monospace; margin-top: 4px;">';
                        html += '<div style="background: #fee2e2; padding: 8px; border-radius: 4px; overflow-x: auto; color: #991b1b;"><div style="font-weight: bold; margin-bottom: 2px;">Cũ:</div>' + escapeHtml(item.old_value) + '</div>';
                        html += '<div style="background: #dcfce7; padding: 8px; border-radius: 4px; overflow-x: auto; color: #166534;"><div style="font-weight: bold; margin-bottom: 2px;">Mới:</div>' + escapeHtml(item.new_value) + '</div>';
                        html += '</div>';
                    } else if (item.action === 'added') {
                        html += '<div style="background: #dcfce7; padding: 8px; border-radius: 4px; overflow-x: auto; font-size: 12px; font-family: monospace; color: #166534;">' + escapeHtml(item.new_value) + '</div>';
                    } else {
                        html += '<div style="background: #fee2e2; padding: 8px; border-radius: 4px; overflow-x: auto; font-size: 12px; font-family: monospace; color: #991b1b;">' + escapeHtml(item.old_value) + '</div>';
                    }

                    html += '</div>';
                });
                html += '</div>';
                document.getElementById('diffContent').innerHTML = html;
            })
            .catch(function() {
                document.getElementById('diffContent').innerHTML = '<div style="color: #ef4444; padding: 20px;">Lỗi kết nối khi tải phân tích sai khác.</div>';
            });
    }

    function escapeHtml(str) {
        if (!str) return '<em>(trống)</em>';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    function closeDiffModal() {
        document.getElementById('diffModal').style.display = 'none';
    }

    function openRestoreModal(revisionId, versionNumber, title) {
        document.getElementById('restoreModalVersionText').innerText = 'Phiên bản v' + versionNumber + ' — ' + title;
        var form = document.getElementById('restoreForm');
        form.action = "{{ url('admin/invitations/' . $invitation->id . '/revisions') }}/" + revisionId + "/restore";
        document.getElementById('restoreModal').style.display = 'flex';
    }

    function closeRestoreModal() {
        document.getElementById('restoreModal').style.display = 'none';
    }

    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('diffModal')) closeDiffModal();
        if (e.target === document.getElementById('restoreModal')) closeRestoreModal();
    });
</script>
@endsection

