@extends('admin.layouts.master')

@section('title', 'Thanh Tra SEO & Sitemap')
@section('page_title', 'Thanh Tra SEO & Sitemap XML')

@section('content')
    <div style="max-width: 900px; margin: 0 auto;">
        @if(session('success'))
            <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32;">
                {{ session('success') }}
            </div>
        @endif

        <!-- Card: Sitemap Status -->
        <div class="mayhen_admin_card" style="background: #fff; border-radius: 14px; border: 1px solid var(--mh-admin-border-subtle); padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; margin: 0 0 6px; color: var(--mh-admin-charcoal);">
                        Sơ Đồ Trang XML (Sitemap.xml)
                    </h3>
                    <p style="color: var(--mh-admin-muted); font-size: 0.8125rem; margin: 0;">
                        Quản lý chỉ mục và tình trạng bộ nhớ đệm phục vụ Googlebot và các công cụ tìm kiếm.
                    </p>
                </div>
                <form action="{{ route('admin.settings.seo.clear_sitemap') }}" method="POST">
                    @csrf
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem;">
                        🔄 Xóa Cache Sitemap
                    </button>
                </form>
            </div>

            <div style="background: #FAF8F5; border-radius: 10px; padding: 16px; border: 1px solid var(--mh-admin-border-subtle); font-size: 0.84375rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="color: var(--mh-admin-muted);">URL Công Khai:</span>
                    <a href="{{ $sitemapUrl }}" target="_blank" style="color: #85182A; font-weight: 600; text-decoration: none;">
                        {{ $sitemapUrl }} &nearr;
                    </a>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="color: var(--mh-admin-muted);">Tình Trạng Cache:</span>
                    <span class="mayhen_admin_badge {{ $hasSitemapCache ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_neutral' }}" style="padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                        {{ $hasSitemapCache ? 'Đang Cache (Hiệu năng cao)' : 'Chưa Cache (Tạo động)' }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--mh-admin-muted);">Tần Suất Tái Tạo Mặc Định:</span>
                    <span style="color: var(--mh-admin-charcoal); font-weight: 500;">86.400 giây (24 giờ)</span>
                </div>
            </div>
        </div>

        <!-- Card: Robots.txt & Meta Tags -->
        <div class="mayhen_admin_card" style="background: #fff; border-radius: 14px; border: 1px solid var(--mh-admin-border-subtle); padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; margin: 0 0 16px; color: var(--mh-admin-charcoal);">
                Quy Chuẩn SEO Toàn Nền Tảng
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.8125rem;">
                <div style="border: 1px solid var(--mh-admin-border-subtle); border-radius: 8px; padding: 14px;">
                    <div style="font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">robots.txt</div>
                    <p style="color: var(--mh-admin-muted); margin-bottom: 8px;">Cho phép crawler truy cập các trang công khai và chặn các thư mục quản trị.</p>
                    <a href="{{ $robotsUrl }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.75rem; padding: 4px 10px;">
                        Kiểm Tra robots.txt &nearr;
                    </a>
                </div>

                <div style="border: 1px solid var(--mh-admin-border-subtle); border-radius: 8px; padding: 14px;">
                    <div style="font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">OpenGraph & Social Share</div>
                    <p style="color: var(--mh-admin-muted); margin-bottom: 8px;">Tự động sinh thẻ og:image, og:title, og:description cho từng thiệp cưới và landing page.</p>
                    <span class="mayhen_admin_badge mayhen_admin_badge_success" style="padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                        Hoạt Động 100%
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

