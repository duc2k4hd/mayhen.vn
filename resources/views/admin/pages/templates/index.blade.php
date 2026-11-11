@extends('admin.layouts.master')

@section('title', 'Quản Lý Mẫu Thiệp')
@section('page_title', 'Kho Mẫu Thiệp Cưới (Templates Engine)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/templates.css') }}">
@endpush

@section('content')
    <!-- Top KPI Cards -->
    <div class="mayhen_admin_template_kpi_grid">
        <div class="mayhen_admin_template_kpi_card">
            <div class="mayhen_admin_template_kpi_icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"></path>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_template_kpi_val">{{ $totalCount }}</div>
                <div class="mayhen_admin_template_kpi_lbl">Tổng Mẫu Thiệp</div>
            </div>
        </div>

        <div class="mayhen_admin_template_kpi_card">
            <div class="mayhen_admin_template_kpi_icon" style="color: #1B5E20; background-color: #E8F5E9; border-color: #C8E6C9;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_template_kpi_val" style="color: #1B5E20;">{{ $activeCount }}</div>
                <div class="mayhen_admin_template_kpi_lbl">Đang Kích Hoạt</div>
            </div>
        </div>

        <div class="mayhen_admin_template_kpi_card">
            <div class="mayhen_admin_template_kpi_icon" style="color: #B78103; background-color: #FFF8E1; border-color: #FFE082;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_template_kpi_val" style="color: #B78103;">{{ $featuredCount }}</div>
                <div class="mayhen_admin_template_kpi_lbl">Mẫu Nổi Bật</div>
            </div>
        </div>

        <div class="mayhen_admin_template_kpi_card">
            <div class="mayhen_admin_template_kpi_icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
            </div>
            <div>
                <div class="mayhen_admin_template_kpi_val">{{ $totalInvitationsCount }}</div>
                <div class="mayhen_admin_template_kpi_lbl">Thiệp Đã Tạo</div>
            </div>
        </div>
    </div>

    <!-- Filter & Discovery Action Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
        <!-- Filters Form -->
        <form action="{{ route('admin.templates.index') }}" method="GET" class="mayhen_admin_filter_bar" style="margin-bottom: 0; flex: 1;">
            <div class="mayhen_admin_search_box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #7A756E;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $currentSearch }}" 
                    placeholder="Tìm tên mẫu, slug, phong cách..." 
                    class="mayhen_admin_search_input"
                >
            </div>

            <select name="category" class="mayhen_admin_select">
                <option value="">-- Tất cả phong cách --</option>
                <option value="luxury" {{ $currentCategory === 'luxury' ? 'selected' : '' }}>Hoàng Gia Tem Sáp</option>
                <option value="modern" {{ $currentCategory === 'modern' ? 'selected' : '' }}>Hiện Đại Sắc Nét</option>
                <option value="traditional" {{ $currentCategory === 'traditional' ? 'selected' : '' }}>Truyền Thống Á Đông</option>
                <option value="floral" {{ $currentCategory === 'floral' ? 'selected' : '' }}>Hoa Lá Thiên Nhiên</option>
                <option value="minimal" {{ $currentCategory === 'minimal' ? 'selected' : '' }}>Tối Giản Tinh Tế</option>
                <option value="editorial" {{ $currentCategory === 'editorial' ? 'selected' : '' }}>Tạp Chí Editorial</option>
            </select>

            <select name="status" class="mayhen_admin_select">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Đang kích hoạt</option>
                <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Đã tạm ẩn</option>
                <option value="featured" {{ $currentStatus === 'featured' ? 'selected' : '' }}>Ghim nổi bật</option>
            </select>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                <span>Lọc</span>
            </button>

            @if ($currentSearch || $currentCategory || $currentStatus)
                <a href="{{ route('admin.templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                    <span>Đặt Lại</span>
                </a>
            @endif
        </form>

        <!-- Discovery & Public Library Action Buttons -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('admin.templates.export', ['q' => $currentSearch, 'category' => $currentCategory, 'status' => $currentStatus]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline" title="Xuất danh sách kho mẫu thiệp ra file CSV Excel">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất CSV (Excel)</span>
            </a>
            <form action="{{ route('admin.templates.scan_discovery') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" title="Quét thư mục resources/views/client/invitations để đồng bộ mẫu mới">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span>Quét & Đồng Bộ Mẫu</span>
                </button>
            </form>
            <a href="{{ route('templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn_primary" target="_blank">
                <span>Xem Thư Viện Public &nearr;</span>
            </a>
        </div>
    </div>

    <!-- Templates Data Table -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M14.31 8l5.74 9.94M9.69 8h11.48M7.38 12l5.74-9.94M9.69 16L3.95 6.06M14.31 16H2.83m13.79-4l-5.74 9.94"></path>
                </svg>
                <span>Danh Sách Mẫu Thiệp ({{ $templates->total() }} mẫu)</span>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Ảnh & Tên Mẫu</th>
                        <th>Slug Kỹ Thuật</th>
                        <th>Phong Cách & Gói</th>
                        <th>Số Thiệp Đã Tạo</th>
                        <th>Nổi Bật</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($templates as $tpl)
                        <tr>
                            <!-- Thumbnail & Name -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <img src="{{ $tpl->thumbnail_url }}" alt="{{ $tpl->name }}" class="mayhen_admin_tpl_thumb">
                                    <div>
                                        <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);">{{ $tpl->name }}</strong><br>
                                        <span style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                            {{ Str::limit($tpl->description, 45) }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Technical Slug -->
                            <td>
                                <code>{{ $tpl->slug }}</code>
                            </td>

                            <!-- Category & Tier -->
                            <td>
                                <div>
                                    <strong>{{ $tpl->category_label }}</strong><br>
                                    @php
                                        $tier = $tpl->tier;
                                        $tierBadgeClass = match($tier) {
                                            'free' => 'mayhen_admin_tier_free',
                                            'pro' => 'mayhen_admin_tier_pro',
                                            default => 'mayhen_admin_tier_plus',
                                        };
                                        $tierLabel = match($tier) {
                                            'free' => 'Gói Miễn Phí',
                                            'pro' => 'Gói Pro VIP',
                                            default => 'Gói Hạnh Phúc (Plus)',
                                        };
                                    @endphp
                                    <span class="mayhen_admin_tier_badge {{ $tierBadgeClass }}">{{ $tierLabel }}</span>
                                </div>
                            </td>

                            <!-- Usage Count -->
                            <td>
                                <strong style="font-size: 0.9375rem;">{{ $tpl->invitations_count }}</strong> thiệp
                            </td>

                            <!-- Featured Toggle -->
                            <td>
                                <form action="{{ route('admin.templates.toggle_featured', $tpl->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" style="font-size: 0.75rem;">
                                        @if ($tpl->is_featured)
                                            <span style="color: #B78103;">★ Nổi Bật</span>
                                        @else
                                            <span style="color: #7A756E;">☆ Thường</span>
                                        @endif
                                    </button>
                                </form>
                            </td>

                            <!-- Active Status -->
                            <td>
                                <form action="{{ route('admin.templates.toggle_active', $tpl->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_badge {{ $tpl->is_active ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}" style="border: none; cursor: pointer;" title="Bấm để {{ $tpl->is_active ? 'Ẩn mẫu' : 'Bật mẫu' }}">
                                        {{ $tpl->is_active ? '● Đang Hoạt Động' : '○ Tạm Ẩn' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('admin.templates.release_lab', $tpl->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" style="color: var(--mh-admin-gold); font-weight: 600;" title="Phòng Thí Nghiệm Phát Hành & Visual Regression">
                                        Release Lab 🧪
                                    </a>
                                    <a href="{{ route('admin.templates.edit', $tpl->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" title="Chỉnh sửa thông tin & Soát lỗi Schema">
                                        Sửa & Schema &nearr;
                                    </a>
                                    <a href="{{ route('templates.preview', $tpl->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" title="Xem thử giao diện mẫu thiệp">
                                        Xem Thử
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #7A756E; padding: 40px;">
                                Không tìm thấy mẫu thiệp nào phù hợp với bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($templates->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
@endsection

