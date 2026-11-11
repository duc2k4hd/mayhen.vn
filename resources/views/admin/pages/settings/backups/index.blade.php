@extends('admin.layouts.master')

@section('title', 'Sao Lưu & Khôi Phục Dữ Liệu')
@section('page_title', 'Sao Lưu, Khôi Phục Dữ Liệu & Quản Trị Thảm Họa (Disaster Recovery Vault)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
    <style>
        .mayhen_admin_kpi_grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        @media (max-width: 992px) {
            .mayhen_admin_kpi_grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .mayhen_admin_kpi_grid {
                grid-template-columns: 1fr;
            }
        }
        .mayhen_admin_kpi_card {
            background: #FFFFFF;
            border-radius: 12px;
            border: 1px solid #EBE7DF;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .mayhen_admin_kpi_title {
            font-size: 0.8125rem;
            color: var(--mh-admin-muted, #7A756D);
            margin-bottom: 8px;
            font-weight: 500;
        }
        .mayhen_admin_kpi_value {
            font-size: 1.65rem;
            font-weight: 600;
            color: var(--mh-admin-charcoal, #2B2927);
        }
        .mayhen_admin_kpi_meta {
            font-size: 0.75rem;
            color: #8C6B37;
            margin-top: 6px;
        }
        .mayhen_admin_badge_verified {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .mayhen_admin_badge_unverified {
            background: #FFF8E1;
            color: #F57F17;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .mayhen_log_modal {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .mayhen_log_modal_content {
            background: #FFFFFF;
            border-radius: 12px;
            max-width: 700px;
            width: 100%;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
    </style>
@endpush

@section('content')
    <!-- Intro Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px; border-left: 4px solid var(--mh-admin-primary, #A38048);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal, #2B2927);">
                    Kho Sao Lưu & Phục Hồi Thảm Họa (Disaster Recovery Vault — Mục 180 PRD V3)
                </h3>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.84375rem; margin: 0;">
                    Bảo vệ an toàn tuyệt đối cơ sở dữ liệu tiệc cưới, thông tin khách mời và thiết lập nền tảng. Hỗ trợ kiểm thử khôi phục độc lập (OPS-01 Test Restore).
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="mayhen_admin_badge" style="background: #E8F5E9; color: #2E7D32; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    🛡️ SLA Tiêu Chuẩn OPS-01
                </span>
                <span class="mayhen_admin_badge" style="background: #E1F5FE; color: #0277BD; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    🔒 SHA-256 Checksum
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Thông báo Session -->
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #E8F5E9; color: #1B5E20; border: 1px solid #A5D6A7;">
            <strong>✓ Thành công:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert_danger" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #FFEBEE; color: #B71C1C; border: 1px solid #FFCDD2;">
            <strong>✕ Lỗi:</strong> {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mayhen_admin_alert mayhen_admin_alert_info" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #E1F5FE; color: #01579B; border: 1px solid #B3E5FC;">
            <strong>ℹ Thông báo:</strong> {{ session('info') }}
        </div>
    @endif

    <!-- 4 Thẻ KPI Sức Khỏe Sao Lưu -->
    <div class="mayhen_admin_kpi_grid">
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Tổng Số Bản Sao Lưu</div>
            <div class="mayhen_admin_kpi_value">{{ number_format($totalBackups) }}</div>
            <div class="mayhen_admin_kpi_meta">Bản snapshot trong kho</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Dung Lượng Kho Lưu Trữ</div>
            <div class="mayhen_admin_kpi_value">{{ $formattedTotalSize }}</div>
            <div class="mayhen_admin_kpi_meta">Bộ nhớ máy chủ sử dụng</div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Snapshot Gần Nhất</div>
            <div class="mayhen_admin_kpi_value" style="font-size: 1.25rem; font-family: inherit; padding-top: 4px;">
                {{ $latestBackup ? $latestBackup->created_at->diffForHumans() : 'Chưa có' }}
            </div>
            <div class="mayhen_admin_kpi_meta">
                {{ $latestBackup ? $latestBackup->created_at->format('d/m/Y H:i') : 'Khuyến nghị tạo bản sao lưu ngay' }}
            </div>
        </div>
        <div class="mayhen_admin_kpi_card">
            <div class="mayhen_admin_kpi_title">Tỷ Lệ Xác Minh Phục Hồi (OPS-01)</div>
            <div class="mayhen_admin_kpi_value" style="color: {{ $verifiedPct >= 80 ? '#2E7D32' : ($verifiedPct >= 50 ? '#F57F17' : '#C62828') }};">
                {{ $verifiedPct }}%
            </div>
            <div class="mayhen_admin_kpi_meta">{{ $verifiedCount }} / {{ $totalBackups }} bản đã xác minh toàn vẹn</div>
        </div>
    </div>

    <!-- Thanh Công Cụ Tạo & Quản Lý Sao Lưu -->
    <div class="mayhen_admin_card" style="padding: 20px 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <!-- Form Tạo Backup Mới -->
            <form action="{{ route('admin.settings.backups.create') }}" method="POST" style="display: flex; gap: 12px; align-items: center; flex: 1; max-width: 600px;">
                @csrf
                <input type="text" 
                       name="note" 
                       class="mayhen_admin_input" 
                       placeholder="Ghi chú bản sao lưu (VD: Trước khi bảo trì, Cập nhật phiên bản...)" 
                       style="flex: 1; min-width: 200px;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="background: var(--mh-admin-primary, #A38048); color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 500; font-size: 0.84375rem; cursor: pointer; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Tạo Bản Sao Lưu Mới
                </button>
            </form>

            <!-- Nút Dọn Dẹp Bản Cũ (Retention Policy) -->
            <form action="{{ route('admin.settings.backups.clean_old') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn dọn dẹp các bản sao lưu cũ, chỉ giữ lại 7 bản gần nhất?');">
                @csrf
                <input type="hidden" name="keep_count" value="7">
                <button type="submit" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; padding: 10px 18px; border-radius: 6px; font-size: 0.84375rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Dọn Dẹp Bản Cũ (>7 bản)
                </button>
            </form>
        </div>
    </div>

    <!-- Bảng Danh Sách Snapshot Vault -->
    <div class="mayhen_admin_card" style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h4 style="font-size: 1.15rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                    Danh Sách Bản Sao Lưu Trong Kho (Snapshot Vault)
                </h4>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.8125rem; margin: 2px 0 0;">
                    Mỗi bản snapshot đều được tính toán mã kiểm tra tính toàn vẹn SHA-256 và có thể chạy kiểm thử phục hồi ngay trên môi trường sandbox.
                </p>
            </div>
        </div>

        @if($backups->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted, #7A756D); font-size: 0.875rem;">
                <div style="font-size: 2rem; margin-bottom: 8px;">📦</div>
                Chưa có bản sao lưu cơ sở dữ liệu nào trong kho. Hãy nhấn nút <strong>"Tạo Bản Sao Lưu Mới"</strong> ở trên để bảo vệ dữ liệu nền tảng ngay bây giờ.
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table" style="width: 100%; font-size: 0.8125rem;">
                    <thead>
                        <tr>
                            <th>Tệp Snapshot & Checksum</th>
                            <th style="width: 130px;">Quy Mô DB</th>
                            <th style="width: 100px;">Dung Lượng</th>
                            <th style="width: 140px;">Thời Gian Tạo</th>
                            <th style="width: 170px;">Xác Minh Phục Hồi (OPS-01)</th>
                            <th style="width: 150px; text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $b)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927);">
                                        {{ $b->filename }}
                                    </div>
                                    @if($b->note)
                                        <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.75rem; margin-top: 2px;">
                                            {{ $b->note }}
                                        </div>
                                    @endif
                                    @if($b->sha256_checksum)
                                        <div style="color: #8C6B37; font-size: 0.7rem; margin-top: 2px;">
                                            SHA-256: {{ substr($b->sha256_checksum, 0, 16) }}...
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="mayhen_admin_badge" style="background: #F0ECE4; color: #4A4640; font-size: 0.7rem;">
                                        {{ $b->tables_count ?? 0 }} bảng
                                    </span>
                                    <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.7rem; margin-top: 2px;">
                                        ~{{ number_format($b->records_count ?? 0) }} dòng
                                    </div>
                                </td>
                                <td style="font-weight: 500;">
                                    {{ $b->formatted_size }}
                                </td>
                                <td style="color: var(--mh-admin-muted, #7A756D); white-space: nowrap;">
                                    <div>{{ $b->created_at->format('d/m/Y H:i') }}</div>
                                    <div style="font-size: 0.7rem;">{{ $b->creator->name ?? 'Hệ Thống' }}</div>
                                </td>
                                <td>
                                    @if($b->isVerified())
                                        <span class="mayhen_admin_badge_verified" title="Đã xác minh toàn vẹn thành công">
                                            ✓ Đã Test (PASS)
                                        </span>
                                        <div style="margin-top: 4px;">
                                            <button type="button" 
                                                    onclick="showVerificationLog({{ json_encode($b->filename) }}, {{ json_encode($b->verification_log) }})" 
                                                    style="background: none; border: none; color: #0277BD; font-size: 0.72rem; cursor: pointer; text-decoration: underline; padding: 0;">
                                                Xem log khôi phục
                                            </button>
                                        </div>
                                    @else
                                        <span class="mayhen_admin_badge_unverified">
                                            Chưa Kiểm Thử
                                        </span>
                                        <div style="margin-top: 4px;">
                                            <form action="{{ route('admin.settings.backups.verify', ['id' => $b->id]) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" style="background: none; border: none; color: #A38048; font-size: 0.72rem; cursor: pointer; text-decoration: underline; padding: 0; font-weight: 600;">
                                                    Chạy Test Ngay ➔
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <!-- Nút Tải Về -->
                                    <a href="{{ route('admin.settings.backups.download', ['id' => $b->id]) }}" 
                                       class="mayhen_admin_btn" 
                                       title="Tải về máy tính an toàn" 
                                       style="background: #FAF8F5; border: 1px solid #D6D0C4; color: var(--mh-admin-charcoal, #2B2927); font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        ⬇ Tải Về
                                    </a>

                                    <!-- Nút Xóa -->
                                    <form action="{{ route('admin.settings.backups.destroy', ['id' => $b->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản sao lưu [{{ $b->filename }}]? Hành động này không thể hoàn tác.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="mayhen_admin_btn" 
                                                title="Xóa bản sao lưu" 
                                                style="background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; font-size: 0.75rem; padding: 5px 8px; border-radius: 4px; cursor: pointer; margin-left: 4px;">
                                            ✕
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                {{ $backups->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Xem Log Xác Minh (Verification Log Modal) -->
    <div id="logModal" class="mayhen_log_modal" onclick="closeLogModal(event)">
        <div class="mayhen_log_modal_content" onclick="event.stopPropagation()">
            <div style="padding: 16px 20px; border-bottom: 1px solid #EBE7DF; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0; font-size: 1.1rem; color: var(--mh-admin-charcoal, #2B2927);">
                    Nhật Ký Kiểm Thử Phục Hồi Độc Lập (OPS-01 Test Restore Log)
                </h4>
                <button type="button" onclick="document.getElementById('logModal').style.display='none'" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #7A756D;">
                    &times;
                </button>
            </div>
            <div style="padding: 20px; overflow-y: auto;">
                <div id="modalFilename" style="font-family: monospace; font-weight: 600; color: #8C6B37; margin-bottom: 12px; font-size: 0.875rem;"></div>
                <pre id="modalLogContent" style="background: #1E1E1E; color: #4AF626; padding: 16px; border-radius: 8px; font-size: 0.8125rem; white-space: pre-wrap; line-height: 1.5; margin: 0;"></pre>
            </div>
            <div style="padding: 12px 20px; border-top: 1px solid #EBE7DF; display: flex; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('logModal').style.display='none'" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 0.8125rem;">
                    Đóng
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function showVerificationLog(filename, logContent) {
            document.getElementById('modalFilename').textContent = 'Tệp: ' + filename;
            document.getElementById('modalLogContent').textContent = logContent || 'Chưa có nhật ký kiểm thử phục hồi.';
            document.getElementById('logModal').style.display = 'flex';
        }

        function closeLogModal(event) {
            if (event.target.id === 'logModal') {
                document.getElementById('logModal').style.display = 'none';
            }
        }
    </script>
@endpush

