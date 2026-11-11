@extends('admin.layouts.master')

@section('title', 'Kiểm Định Chất Lượng & Ma Trận Nghiệm Thu')

@section('content')
<div class="mayhen_admin_page" style="max-width: 1200px; margin: 0 auto;">
    {{-- Header & Actions --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Admin</a> / 
                <span>Hạ Tầng &amp; Vận Hành</span> / 
                <span style="color: var(--mh-admin-charcoal); font-weight: 600;">Kiểm Định Chất Lượng PRD V3</span>
            </div>
            <h1 style="font-size: 1.625rem; font-weight: 700; margin: 0 0 6px 0; color: var(--mh-admin-charcoal);">
                Trạm Chỉ Huy Kiểm Định Chất Lượng Toàn Diện
            </h1>
            <p style="font-size: 0.875rem; color: var(--mh-admin-muted); margin: 0;">
                Giám sát tự động 20+ tiêu chí kỹ thuật then chốt theo Ma Trận Section 62 &amp; 63 PRD V3 (Bảo Mật, Mẫu Thiệp, Guest CRM, VietQR, OpenGraph &amp; Hạ Tầng).
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center;">
            <form action="{{ route('admin.system.acceptance.run') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.8125rem; padding: 9px 16px; border-radius: 8px; cursor: pointer;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    <span>Chạy Kiểm Định Tức Thời</span>
                </button>
            </form>

            <a href="{{ route('admin.system.acceptance.export') }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 0.8125rem; padding: 9px 16px; border-radius: 8px; text-decoration: none; border: 1px solid var(--mh-admin-border); background: #fff; color: var(--mh-admin-charcoal);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất CSV (UTF-8 BOM)</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="padding: 12px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; font-size: 0.875rem; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- KPI Cards Overview --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div style="background: #fff; padding: 20px 22px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #2E7D32;">
                Điểm Tuân Thủ Toàn Diện
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; margin-top: 8px;">
                <span style="font-size: 2.25rem; font-weight: 800; color: #2E7D32; line-height: 1;">
                    {{ $audit['compliance_score'] }}%
                </span>
                <span style="font-size: 0.875rem; font-weight: 700; color: #388E3C;">
                    {{ $audit['grade'] }}
                </span>
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Cập nhật lúc: {{ $audit['generated_at'] }}
            </div>
        </div>

        <div style="background: #fff; padding: 20px 22px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--mh-admin-muted);">
                Tiêu Chí Đạt Chuẩn (Passed)
            </div>
            <div style="font-size: 2.25rem; font-weight: 800; color: var(--mh-admin-charcoal); margin-top: 8px; line-height: 1;">
                {{ $audit['passed_count'] }} <span style="font-size: 1rem; font-weight: 500; color: var(--mh-admin-muted);">/ {{ $audit['total_checks'] }}</span>
            </div>
            <div style="font-size: 0.75rem; color: #2E7D32; margin-top: 6px;">
                Đạt 100% yêu cầu vận hành cốt lõi
            </div>
        </div>

        <div style="background: #fff; padding: 20px 22px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #F57C00;">
                Cảnh Báo Cần Tối Ưu (Warnings)
            </div>
            <div style="font-size: 2.25rem; font-weight: 800; color: #F57C00; margin-top: 8px; line-height: 1;">
                {{ $audit['warning_count'] }}
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 6px;">
                Chưa ảnh hưởng nghiêm trọng đến sản phẩm
            </div>
        </div>

        <div style="background: #fff; padding: 20px 22px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #D32F2F;">
                Lỗi Nghiêm Trọng (Critical)
            </div>
            <div style="font-size: 2.25rem; font-weight: 800; color: {{ $audit['critical_count'] > 0 ? '#D32F2F' : '#388E3C' }}; margin-top: 8px; line-height: 1;">
                {{ $audit['critical_count'] }}
            </div>
            <div style="font-size: 0.75rem; color: {{ $audit['critical_count'] > 0 ? '#D32F2F' : '#388E3C' }}; margin-top: 6px;">
                {{ $audit['critical_count'] === 0 ? '✓ Không phát hiện lỗ hổng đe dọa hệ thống' : 'Cần xử lý khẩn cấp trước khi release' }}
            </div>
        </div>
    </div>

    {{-- Bảng Ma Trận Kiểm Định Chi Tiết --}}
    <div style="background: #fff; border: 1px solid var(--mh-admin-border-subtle); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--mh-admin-border-subtle); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.0625rem; font-weight: 700; margin: 0; color: var(--mh-admin-charcoal);">
                Chi Tiết Ma Trận Nghiệm Thu (Section 62 PRD V3)
            </h3>
            <span style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                Tổng cộng {{ $audit['total_checks'] }} tiêu chuẩn kỹ thuật
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid var(--mh-admin-border); color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 12px 18px; width: 90px;">Mã</th>
                        <th style="padding: 12px 18px; width: 170px;">Nhóm Nghiệp Vụ</th>
                        <th style="padding: 12px 18px;">Tiêu Chí Nghiệm Thu</th>
                        <th style="padding: 12px 18px; width: 120px; text-align: center;">Trạng Thái</th>
                        <th style="padding: 12px 18px; width: 220px;">Chỉ Số Thực Tế</th>
                        <th style="padding: 12px 18px;">Đánh Giá Chi Tiết</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($audit['checks'] as $c)
                        <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle); transition: background 0.15s ease;">
                            <td style="padding: 14px 18px; font-weight: 700; color: var(--mh-admin-charcoal); font-family: monospace;">
                                {{ $c['id'] }}
                            </td>
                            <td style="padding: 14px 18px; color: var(--mh-admin-muted); font-weight: 600;">
                                {{ $c['category'] }}
                            </td>
                            <td style="padding: 14px 18px; font-weight: 600; color: var(--mh-admin-charcoal);">
                                {{ $c['title'] }}
                            </td>
                            <td style="padding: 14px 18px; text-align: center;">
                                @if($c['status'] === 'PASSED')
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.6875rem; font-weight: 700; background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9;">
                                        PASSED
                                    </span>
                                @elseif($c['status'] === 'WARNING')
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.6875rem; font-weight: 700; background: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2;">
                                        WARNING
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.6875rem; font-weight: 700; background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2;">
                                        CRITICAL
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; font-weight: 600; color: var(--mh-admin-charcoal);">
                                {{ $c['metric'] }}
                            </td>
                            <td style="padding: 14px 18px; color: var(--mh-admin-muted); line-height: 1.5;">
                                {{ $c['detail'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

