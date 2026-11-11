@extends('admin.layouts.master')

@section('title', 'Trung Tâm Điều Hành SEO & Sức Khỏe Tìm Kiếm')

@section('content')
<div class="mayhen_admin_page" style="max-width: 1100px; margin: 0 auto;">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <div class="mayhen_admin_breadcrumb" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a> / <span>SEO &amp; Tối ưu hóa tìm kiếm</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--mh-admin-heading);">
                Trung Tâm Điều Hành SEO &amp; Chuyển Hướng 301/302
            </h1>
        </div>
        <div>
            <form action="{{ route('admin.seo.clear_sitemap') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem; padding: 9px 14px; border-radius: 8px; border: 1px solid var(--mh-admin-border); background: #fff; cursor: pointer;">
                    🔄 Tái Tạo Sitemap XML
                </button>
            </form>
        </div>
    </div>

    {{-- Flash Message --}}
    @if(session('success'))
        <div style="padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32; margin-bottom: 20px; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; margin-bottom: 20px; font-size: 0.875rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Thống kê KPI --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #2E7D32; font-weight: 600;">Điểm Sức Khỏe SEO</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #2E7D32; margin-top: 6px;">{{ $stats['health_score'] }}/100</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Tổng Tuyến Redirect</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--mh-admin-heading); margin-top: 6px;">{{ $stats['redirects_total'] }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: #C5A880; font-weight: 600;">Lượt Điều Hướng (Hits)</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: #9C7A4A; margin-top: 6px;">{{ number_format($stats['total_hits']) }}</div>
        </div>
        <div style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--mh-admin-muted); font-weight: 600;">Sitemap XML Cache</div>
            <div style="font-size: 1.125rem; font-weight: 700; color: var(--mh-admin-heading); margin-top: 8px;">
                @if($hasSitemapCache)
                    <span style="color: #2E7D32;">● Đang Lưu Đệm (24h)</span>
                @else
                    <span style="color: #F57F17;">○ Tạo Động Tức Thời</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KHỐI 1: Quản Lý Chuyển Hướng 301/302 & Phát Hiện Vòng Lặp --}}
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); padding: 24px; margin-bottom: 24px;">
        <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0 0 16px 0; color: var(--mh-admin-heading);">
            Quản Lý Chuyển Hướng URL (301 Permanent &amp; 302 Temporary)
        </h3>
        <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 20px 0;">
            Tự động điều hướng các liên kết cũ, đường dẫn thay đổi hoặc gộp bài viết. Tích hợp thuật toán phát hiện vòng lặp vô tận (Loop Detection) để bảo vệ thứ hạng SEO.
        </p>

        {{-- Form Thêm Chuyển Hướng Mới --}}
        <form method="POST" action="{{ route('admin.seo.redirects.store') }}" style="background: #FAF8F5; padding: 18px; border-radius: 10px; border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 24px;">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 2fr 1fr 2fr auto; gap: 12px; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Đường Dẫn Cũ (Source Path) <span style="color: red;">*</span></label>
                    <input type="text" name="source_path" value="{{ old('source_path') }}" required placeholder="/duong-dan-cu" style="width: 100%; padding: 9px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.8125rem; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Đích Đến Mới (Target URL) <span style="color: red;">*</span></label>
                    <input type="text" name="target_url" value="{{ old('target_url') }}" required placeholder="/mau-thiep hoặc URL đầy đủ" style="width: 100%; padding: 9px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.8125rem; font-family: monospace;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Mã Chuyển</label>
                    <select name="status_code" style="width: 100%; padding: 9px 10px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.8125rem; background: #fff;">
                        <option value="301" {{ old('status_code', '301') == '301' ? 'selected' : '' }}>301 Vĩnh viễn</option>
                        <option value="302" {{ old('status_code') == '302' ? 'selected' : '' }}>302 Tạm thời</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 4px;">Ghi Chú Lý Do</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="Đổi slug thiệp cưới cũ..." style="width: 100%; padding: 9px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.8125rem;">
                </div>
                <div>
                    <button type="submit" style="padding: 9px 18px; border-radius: 6px; background: #1F1D1A; color: #fff; border: none; font-size: 0.8125rem; font-weight: 600; cursor: pointer; white-space: nowrap;">
                        + Thêm Redirect
                    </button>
                </div>
            </div>
        </form>

        {{-- Bảng Danh Sách Redirects --}}
        <div style="border: 1px solid var(--mh-admin-border-subtle); border-radius: 8px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid var(--mh-admin-border); color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase;">
                        <th style="padding: 10px 14px;">Đường Dẫn Nguồn</th>
                        <th style="padding: 10px 14px;">Đích Đến</th>
                        <th style="padding: 10px 14px;">Mã</th>
                        <th style="padding: 10px 14px;">Số Hits</th>
                        <th style="padding: 10px 14px;">Trạng Thái</th>
                        <th style="padding: 10px 14px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($redirects as $redirect)
                        <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle);">
                            <td style="padding: 12px 14px; font-family: monospace; color: #85182A; font-weight: 600;">
                                {{ $redirect->source_path }}
                                @if($redirect->note)
                                    <div style="font-family: inherit; font-size: 0.75rem; color: #888; font-weight: normal;">{{ $redirect->note }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px 14px; font-family: monospace; color: #2E7D32;">
                                &rarr; {{ $redirect->target_url }}
                            </td>
                            <td style="padding: 12px 14px;">
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 4px; background: {{ $redirect->status_code === 301 ? '#E8F5E9' : '#FFF8E1' }}; color: {{ $redirect->status_code === 301 ? '#2E7D32' : '#F57F17' }}; font-weight: 700;">
                                    {{ $redirect->status_code }}
                                </span>
                            </td>
                            <td style="padding: 12px 14px; font-weight: 600;">
                                {{ number_format($redirect->hits) }}
                            </td>
                            <td style="padding: 12px 14px;">
                                @if($redirect->is_active)
                                    <span style="color: #2E7D32; font-weight: 600;">● Đang bật</span>
                                @else
                                    <span style="color: #999;">○ Tạm ngưng</span>
                                @endif
                            </td>
                            <td style="padding: 12px 14px; text-align: right;">
                                <form method="POST" action="{{ route('admin.seo.redirects.destroy', $redirect->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tuyến redirect này?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 4px 8px; border-radius: 4px; border: 1px solid #FFCDD2; background: #FFEBEE; color: #C62828; font-size: 0.75rem; cursor: pointer;">
                                        Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 24px; text-align: center; color: var(--mh-admin-muted);">
                                Chưa có tuyến chuyển hướng URL nào được thiết lập.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($redirects->hasPages())
            <div style="margin-top: 14px;">
                {{ $redirects->links() }}
            </div>
        @endif
    </div>

    {{-- KHỐI 2: Thanh Tra Sức Khỏe SEO Từng Trang Trọng Yếu --}}
    <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <div>
                <h3 style="font-size: 1.125rem; font-weight: 700; margin: 0 0 4px 0; color: var(--mh-admin-heading);">
                    Thanh Tra Sức Khỏe SEO Các Trang Cốt Lõi (SEO Health Check)
                </h3>
                <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0;">
                    Rà soát tự động các tiêu chuẩn Meta Title, Meta Description, Thẻ Canonical và Open Graph.
                </p>
            </div>
            <div style="font-size: 0.8125rem;">
                <span style="color: #2E7D32; font-weight: 600;">{{ $healthAudit['passed_count'] }} Đạt Chuẩn</span> &bull; 
                <span style="color: #E65100; font-weight: 600;">{{ $healthAudit['warnings_count'] }} Cần Lưu Ý</span>
            </div>
        </div>

        <div style="border: 1px solid var(--mh-admin-border-subtle); border-radius: 8px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.8125rem;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid var(--mh-admin-border); color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase;">
                        <th style="padding: 10px 14px;">Trang Đích</th>
                        <th style="padding: 10px 14px;">Meta Title</th>
                        <th style="padding: 10px 14px;">Meta Description</th>
                        <th style="padding: 10px 14px;">Canonical &amp; OG</th>
                        <th style="padding: 10px 14px; text-align: right;">Đánh Giá</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($healthAudit['checks'] as $check)
                        <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle);">
                            <td style="padding: 12px 14px;">
                                <div style="font-weight: 600; color: var(--mh-admin-heading);">{{ $check['name'] }}</div>
                                <a href="{{ $check['url'] }}" target="_blank" style="font-size: 0.75rem; color: #888; text-decoration: none;">Xem trang &nearr;</a>
                            </td>
                            <td style="padding: 12px 14px; max-width: 280px;">
                                <div style="color: #1F1D1A; font-weight: 500;">{{ Str::limit($check['title'], 60) }}</div>
                                <div style="font-size: 0.75rem; color: #888;">{{ $check['title_length'] }} ký tự (Chuẩn: 40-70)</div>
                            </td>
                            <td style="padding: 12px 14px; max-width: 300px;">
                                <div style="color: #555;">{{ Str::limit($check['description'], 80) }}</div>
                                <div style="font-size: 0.75rem; color: #888;">{{ $check['description_length'] }} ký tự (Chuẩn: 120-160)</div>
                            </td>
                            <td style="padding: 12px 14px;">
                                <div style="color: {{ $check['has_canonical'] ? '#2E7D32' : '#C62828' }};">
                                    {{ $check['has_canonical'] ? '✓ Canonical' : '✗ Thiếu Canonical' }}
                                </div>
                                <div style="color: {{ $check['has_og_image'] ? '#2E7D32' : '#C62828' }};">
                                    {{ $check['has_og_image'] ? '✓ OpenGraph Image' : '✗ Thiếu OG' }}
                                </div>
                            </td>
                            <td style="padding: 12px 14px; text-align: right;">
                                @if($check['is_healthy'])
                                    <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; background: #E8F5E9; color: #2E7D32; font-weight: 600;">Tốt 100%</span>
                                @else
                                    <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; background: #FFF3E0; color: #E65100; font-weight: 600;">Cảnh báo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- KHỐI 3: Tài Liệu Crawler & Sitemap Status --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); padding: 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 0.9375rem; font-weight: 700; color: var(--mh-admin-heading);">XML Sitemap (sitemap.xml)</h4>
            <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 12px;">
                Tự động thu thập URL các mẫu thiệp cưới công khai, bài viết cẩm nang và các trang tĩnh.
            </p>
            <div style="font-size: 0.8125rem; font-family: monospace; background: #FAF8F5; padding: 10px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 12px;">
                <a href="{{ $sitemapUrl }}" target="_blank" style="color: #9C7A4A; text-decoration: none;">{{ $sitemapUrl }} &nearr;</a>
            </div>
            <form action="{{ route('admin.seo.clear_sitemap') }}" method="POST">
                @csrf
                <button type="submit" style="padding: 8px 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border); background: #fff; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                    Xóa Cache &amp; Làm Mới Sitemap
                </button>
            </form>
        </div>

        <div style="background: #fff; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); padding: 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 0.9375rem; font-weight: 700; color: var(--mh-admin-heading);">Tệp Robots.txt</h4>
            <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 12px;">
                Chỉ định phạm vi crawl cho Googlebot, Bingbot; chặn index thư mục nhạy cảm (`/admin`, `/tai-khoan`).
            </p>
            <div style="font-size: 0.8125rem; font-family: monospace; background: #FAF8F5; padding: 10px; border-radius: 6px; border: 1px solid var(--mh-admin-border-subtle); margin-bottom: 12px;">
                <a href="{{ $robotsUrl }}" target="_blank" style="color: #9C7A4A; text-decoration: none;">{{ $robotsUrl }} &nearr;</a>
            </div>
            <a href="{{ $robotsUrl }}" target="_blank" style="display: inline-block; padding: 8px 14px; border-radius: 6px; border: 1px solid var(--mh-admin-border); background: #fff; font-size: 0.75rem; font-weight: 600; text-decoration: none; color: #1F1D1A;">
                Kiểm Tra robots.txt Ngoài Web &nearr;
            </a>
        </div>
    </div>
</div>
@endsection
