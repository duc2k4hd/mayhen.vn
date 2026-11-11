@extends('admin.layouts.master')

@section('title', 'Phòng Thí Nghiệm Phát Hành & Visual Regression: ' . $template->name . '')
@section('page_title', 'Release Lab & Visual Regression: ' . $template->name)

@section('breadcrumb_parent')
    <a href="{{ route('admin.templates.index') }}">Kho Mẫu Thiệp</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/templates.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_page_title" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span>{{ $template->name }}</span>
                <span class="mayhen_admin_badge" style="background-color: #2C2824; color: #FAF8F5;">
                    Phiên bản: {{ $template->current_version ?? 'v1.0.0' }}
                </span>
                @if ($template->release_status === 'published')
                    <span class="mayhen_admin_badge mayhen_admin_badge_success">● Đang Hoạt Động (Published)</span>
                @elseif ($template->release_status === 'staged')
                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">⚙ Đang Thử Nghiệm (Staged)</span>
                @elseif ($template->release_status === 'draft')
                    <span class="mayhen_admin_badge" style="background-color: #E2E8F0; color: #475569;">Bản Nháp (Draft)</span>
                @else
                    <span class="mayhen_admin_badge" style="background-color: #FEE2E2; color: #991B1B;">Đã Ngừng Hỗ Trợ (Deprecated)</span>
                @endif

                <span class="mayhen_admin_badge" style="background-color: #FEF3C7; color: #92400E;">
                    Triển khai: {{ $template->rollout_percentage ?? 100 }}%
                </span>
            </div>
            <div class="mayhen_admin_page_subtitle">
                Mã mẫu: <code>{{ $template->slug }}</code> &bull; Hạng gói: <strong>{{ strtoupper($template->tier ?? 'PLUS') }}</strong> &bull; Đang phục vụ: <strong>{{ $template->invitations_count }}</strong> thiệp cưới thực tế
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('admin.templates.edit', $template->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                Sửa Thông Tin & Schema
            </a>
            <a href="{{ route('templates.preview', $template->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline">
                Xem Mẫu Trực Tiếp &nearr;
            </a>
        </div>
    </div>

    <!-- Preflight Quality Gate Status Banner -->
    @if ($preflight['is_ready_for_release'])
        <div style="background-color: #F0FDF4; border: 1px solid #BBF7D0; border-radius: var(--mh-admin-radius-md); padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold;">
                    ✓
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1rem; color: #166534; font-weight: 700;">Preflight Quality Gate: Đạt 100% Tiêu Chuẩn Phát Hành (Score: {{ $preflight['readiness_score'] }}/100)</h4>
                    <p style="margin: 4px 0 0 0; font-size: 0.8125rem; color: #15803D;">Mẫu thiệp đã thỏa mãn đầy đủ 10 Section Tiêu chuẩn, hợp đồng Live Sync, tài nguyên cục bộ 100% không dính URL server ngoài và sẵn sàng công bố an toàn.</p>
                </div>
            </div>
            <span class="mayhen_admin_badge mayhen_admin_badge_success" style="font-size: 0.75rem; padding: 6px 12px;">READY TO RELEASE</span>
        </div>
    @else
        <div style="background-color: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--mh-admin-radius-md); padding: 16px 20px; margin-bottom: 24px;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: bold; flex-shrink: 0;">
                    ✕
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1rem; color: #991B1B; font-weight: 700;">Preflight Quality Gate: Chưa Đạt Chuẩn Phát Hành (Bị Chặn)</h4>
                    <p style="margin: 4px 0 10px 0; font-size: 0.8125rem; color: #B91C1C;">Phát hiện {{ count($preflight['blockers']) }} rủi ro kỹ thuật cần khắc phục trước khi được phép công bố phiên bản:</p>
                    <ul style="margin: 0; padding-left: 20px; font-size: 0.8125rem; color: #991B1B;">
                        @foreach ($preflight['blockers'] as $blocker)
                            <li style="margin-bottom: 4px;">{{ $blocker }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Visual Regression Lab Toolbar -->
    <div class="mayhen_admin_lab_toolbar">
        <div class="mayhen_admin_lab_viewports">
            <span style="font-size: 0.75rem; font-weight: 700; color: var(--mh-admin-muted); text-transform: uppercase; margin-right: 4px;">Khung Thử Nghiệm:</span>
            <button type="button" class="mayhen_admin_lab_vp_btn active" data-width="375" id="btn_vp_375">
                📱 375px <span style="font-size: 0.6875rem; opacity: 0.7;">(Mobile SE)</span>
            </button>
            <button type="button" class="mayhen_admin_lab_vp_btn" data-width="430" id="btn_vp_430">
                📱 430px <span style="font-size: 0.6875rem; opacity: 0.7;">(Pro Max)</span>
            </button>
            <button type="button" class="mayhen_admin_lab_vp_btn" data-width="768" id="btn_vp_768">
                📟 768px <span style="font-size: 0.6875rem; opacity: 0.7;">(Tablet)</span>
            </button>
            <button type="button" class="mayhen_admin_lab_vp_btn" data-width="1100" id="btn_vp_1440">
                💻 1440px <span style="font-size: 0.6875rem; opacity: 0.7;">(Desktop)</span>
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer;">
                <input type="checkbox" id="toggle_split_view" checked style="accent-color: var(--mh-admin-gold);">
                So Sánh Song Song (Split Diff View)
            </label>
            <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" id="btn_reload_iframes" style="padding: 6px 12px; font-size: 0.75rem;">
                ↻ Nạp Lại Preview
            </button>
        </div>
    </div>

    <!-- Visual Regression Workbench -->
    <div class="mayhen_admin_lab_workspace" id="lab_workspace">
        <!-- Frame 1: Published Live Version -->
        <div class="mayhen_admin_lab_frame_container" id="frame_live_container" style="width: 375px;">
            <div class="mayhen_admin_lab_frame_header">
                <span>🌐 BẢN CÔNG KHAI (LIVE: {{ $template->current_version ?? 'v1.0.0' }})</span>
                <span id="dimension_gauge_live">375 &times; 720px</span>
            </div>
            <iframe 
                src="{{ route('templates.preview', $template->slug) }}?embed=1&rehearsal=1" 
                class="mayhen_admin_lab_iframe" 
                id="iframe_live"
                title="Bản Công Khai Hiện Tại"
            ></iframe>
        </div>

        <!-- Frame 2: Staged Experimental Version -->
        <div class="mayhen_admin_lab_frame_container" id="frame_staged_container" style="width: 375px;">
            <div class="mayhen_admin_lab_frame_header" style="background-color: var(--mh-admin-gold);">
                <span>🧪 BẢN THỬ NGHIỆM (STAGED: {{ $template->staged_version ?? 'Bản Mới' }})</span>
                <span id="dimension_gauge_staged">375 &times; 720px</span>
            </div>
            <iframe 
                src="{{ route('templates.preview', $template->slug) }}?embed=1&rehearsal=1&staged=1" 
                class="mayhen_admin_lab_iframe" 
                id="iframe_staged"
                title="Bản Thử Nghiệm Staged"
            ></iframe>
        </div>
    </div>

    <!-- Preflight Quality Gate Checklist Card -->
    <div class="mayhen_admin_lab_checklist_card">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--mh-admin-border); padding-bottom: 12px;">
            <div style="font-weight: 700; font-size: 1rem; color: var(--mh-admin-charcoal); display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mh-admin-gold);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                Kiểm Tra Toàn Vẹn Preflight (Pre-Release Integrity Audit)
            </div>
            <span style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                Điểm Đánh Giá: <strong>{{ $preflight['readiness_score'] }}/100</strong>
            </span>
        </div>

        <div class="mayhen_admin_lab_checklist_grid">
            <!-- Check 1: 10 Canonical Sections -->
            <div class="mayhen_admin_lab_check_item {{ count($preflight['blockers']) === 0 ? 'success' : 'danger' }}">
                <div class="mayhen_admin_lab_check_icon">{{ count($preflight['blockers']) === 0 ? '✓' : '!' }}</div>
                <div class="mayhen_admin_lab_check_content">
                    <div class="mayhen_admin_lab_check_title">10 Section Tiêu Chuẩn</div>
                    <div class="mayhen_admin_lab_check_desc">
                        Hiện có: <strong>{{ $preflight['metrics']['canonical_sections'] }}</strong> mục. Mọi mẫu thiệp bắt buộc có đủ 10 Section từ Hero đến Ending.
                    </div>
                </div>
            </div>

            <!-- Check 2: Live Sync Contract -->
            <div class="mayhen_admin_lab_check_item success">
                <div class="mayhen_admin_lab_check_icon">✓</div>
                <div class="mayhen_admin_lab_check_content">
                    <div class="mayhen_admin_lab_check_title">Hợp Đồng Live Sync Studio</div>
                    <div class="mayhen_admin_lab_check_desc">
                        Ghi nhận <strong>{{ $preflight['metrics']['sync_targets_count'] }}</strong> thẻ <code>data-sync-target</code> đồng bộ dữ liệu thời gian thực.
                    </div>
                </div>
            </div>

            <!-- Check 3: Zero External CDN -->
            <div class="mayhen_admin_lab_check_item success">
                <div class="mayhen_admin_lab_check_icon">✓</div>
                <div class="mayhen_admin_lab_check_content">
                    <div class="mayhen_admin_lab_check_title">100% Local Assets (Zero CDN)</div>
                    <div class="mayhen_admin_lab_check_desc">
                        Đã tải <strong>{{ $preflight['metrics']['local_assets_count'] }}</strong> tài nguyên cục bộ. Không phụ thuộc link máy chủ bên thứ ba.
                    </div>
                </div>
            </div>

            <!-- Check 4: Brand Harmony & Theme -->
            <div class="mayhen_admin_lab_check_item success">
                <div class="mayhen_admin_lab_check_icon">✓</div>
                <div class="mayhen_admin_lab_check_content">
                    <div class="mayhen_admin_lab_check_title">Hòa Sắc Quiet Luxury ({{ $preflight['metrics']['brand_harmony_score'] }}%)</div>
                    <div class="mayhen_admin_lab_check_desc">
                        Phong cách mỹ thuật: <strong>{{ $preflight['metrics']['theme'] }}</strong>. Không sử dụng các màu chói vi phạm nhận diện thương hiệu.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Release Operations Grid: Prepare Release & Actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 24px;">
        <!-- Card 1: Chuẩn Bị Bản Phát Hành Mới (Staged) -->
        <div class="mayhen_admin_card">
            <div class="mayhen_admin_card_header">
                <div class="mayhen_admin_card_title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mh-admin-gold);"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    <span>Khởi Tạo Bản Phát Hành Thử Nghiệm (Staged Release)</span>
                </div>
            </div>
            <div class="mayhen_admin_card_body">
                <form action="{{ route('admin.templates.releases.create', $template->id) }}" method="POST">
                    @csrf
                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_form_label" for="version">
                            Số Hiệu Phiên Bản Mới <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="version" 
                            name="version" 
                            placeholder="Ví dụ: v1.1.0 hoặc v2.0.0" 
                            class="mayhen_admin_form_input" 
                            required 
                            value="{{ old('version') }}"
                        >
                        <div class="mayhen_admin_form_help">Định dạng chuẩn Semantic Versioning (v1.0.0, v1.1.0). Phiên bản hiện tại: <code>{{ $template->current_version ?? 'v1.0.0' }}</code>.</div>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_form_label" for="rollout_percentage">
                            Tỷ Lệ Triển Khai Thử Nghiệm (Canary Rollout) <span class="required">*</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <input 
                                type="range" 
                                id="rollout_percentage" 
                                name="rollout_percentage" 
                                min="0" 
                                max="100" 
                                step="5" 
                                value="{{ old('rollout_percentage', 25) }}" 
                                class="mayhen_admin_form_input" 
                                style="flex: 1;"
                                oninput="document.getElementById('rollout_val_display').innerText = this.value + '%'"
                            >
                            <span id="rollout_val_display" style="font-weight: 700; font-size: 1rem; width: 50px; text-align: right; color: var(--mh-admin-gold);">25%</span>
                        </div>
                        <div class="mayhen_admin_form_help">Khi ở chế độ Staged, chỉ % các cặp đôi mới tạo thiệp nạp bản này; 100% thiệp cũ vẫn chạy bản ổn định.</div>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_form_label" for="release_notes">
                            Nhật Ký Thay Đổi (Changelog / Release Notes)
                        </label>
                        <textarea 
                            id="release_notes" 
                            name="release_notes" 
                            rows="3" 
                            class="mayhen_admin_form_textarea" 
                            placeholder="Mô tả các cải tiến CSS, cập nhật layout, hoặc sửa lỗi font chữ..."
                        >{{ old('release_notes') }}</textarea>
                    </div>

                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="width: 100%;">
                        ⚙ Tạo Bản Thử Nghiệm Staged
                    </button>
                </form>
            </div>
        </div>

        <!-- Card 2: Công Bố Phát Hành Hoặc Hoàn Nguyên Tức Thì -->
        <div class="mayhen_admin_card">
            <div class="mayhen_admin_card_header">
                <div class="mayhen_admin_card_title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #2E7D32;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                    <span>Điều Hành Phát Hành & An Toàn Vận Hành</span>
                </div>
            </div>
            <div class="mayhen_admin_card_body">
                @php
                    $stagedRelease = $releases->where('release_status', 'staged')->first();
                    $publishedRelease = $releases->where('release_status', 'published')->first();
                @endphp

                <!-- Publish Action -->
                @if ($stagedRelease)
                    <div style="background-color: #FFFDF5; border: 1px solid #FEF08A; border-radius: var(--mh-admin-radius-sm); padding: 14px; margin-bottom: 16px;">
                        <div style="font-weight: 700; font-size: 0.875rem; color: #854D0E; margin-bottom: 4px;">
                            Bản Thử Nghiệm Chờ Công Bố: {{ $stagedRelease->version }}
                        </div>
                        <div style="font-size: 0.75rem; color: #A16207; margin-bottom: 10px;">
                            Đã thử nghiệm thành công với tỷ lệ {{ $stagedRelease->rollout_percentage }}%. Sẵn sàng phát hành chính thức ra toàn bộ {{ $template->invitations_count }} thiệp.
                        </div>
                        <form action="{{ route('admin.templates.releases.publish', ['id' => $template->id, 'releaseId' => $stagedRelease->id]) }}" method="POST">
                            @csrf
                            <button 
                                type="submit" 
                                class="mayhen_admin_btn mayhen_admin_btn_success" 
                                style="width: 100%;"
                                {{ ! $preflight['is_ready_for_release'] ? 'disabled title="Cần vượt qua Preflight Gate trước"' : '' }}
                                onclick="return confirm('XÁC NHẬN CÔNG BỐ: Bạn có chắc chắn muốn công bố phiên bản {{ $stagedRelease->version }} cho toàn bộ khách hàng?')"
                            >
                                🚀 Phê Duyệt & Công Bố Toàn Hệ Thống (Publish 100%)
                            </button>
                        </form>
                    </div>
                @else
                    <div style="background-color: var(--mh-admin-bg); border: 1px dashed var(--mh-admin-border); border-radius: var(--mh-admin-radius-sm); padding: 14px; margin-bottom: 16px; text-align: center; color: var(--mh-admin-muted); font-size: 0.8125rem;">
                        Hiện chưa có bản phát hành thử nghiệm nào đang chờ duyệt. Khởi tạo bản mới ở cột bên trái để bắt đầu.
                    </div>
                @endif

                <!-- Rollback Action -->
                <div style="background-color: #FFF5F5; border: 1px solid #FECACA; border-radius: var(--mh-admin-radius-sm); padding: 14px;">
                    <div style="font-weight: 700; font-size: 0.875rem; color: #991B1B; margin-bottom: 4px;">
                        🛡️ Hoàn Nguyên Khẩn Cấp (1-Click Instant Rollback)
                    </div>
                    <div style="font-size: 0.75rem; color: #B91C1C; margin-bottom: 12px;">
                        Nếu bản cập nhật phát sinh lỗi vỡ layout hoặc không tương thích trên thiết bị khách hàng, bạn có thể lập tức hoàn nguyên về phiên bản ổn định trước đó: <code>{{ $template->previous_version ?? 'v1.0.0' }}</code>.
                    </div>

                    @php
                        $previousRelease = $releases->where('version', $template->previous_version)->first()
                            ?? $releases->where('id', '!=', $publishedRelease?->id)->whereIn('release_status', ['published', 'deprecated'])->first();
                    @endphp

                    @if ($previousRelease)
                        <button 
                            type="button" 
                            class="mayhen_admin_btn mayhen_admin_btn_danger" 
                            style="width: 100%;"
                            onclick="document.getElementById('rollback_modal').style.display = 'flex';"
                        >
                            ↺ Hoàn Nguyên Về Bản {{ $previousRelease->version }}
                        </button>
                    @else
                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" style="width: 100%; opacity: 0.6;" disabled>
                            Chưa Có Bản Dự Phòng Để Hoàn Nguyên
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Release History Ledger (Sổ Cái Lịch Sử Phiên Bản) -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--mh-admin-gold);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                <span>Sổ Cái Lịch Sử Phiên Bản (Template Release Ledger)</span>
            </div>
        </div>
        <div class="mayhen_admin_card_body" style="padding: 0;">
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_lab_history_table">
                    <thead>
                        <tr>
                            <th>Phiên Bản</th>
                            <th>Trạng Thái</th>
                            <th>Tỷ Lệ Triển Khai</th>
                            <th>Người Khởi Tạo</th>
                            <th>Người Phê Duyệt</th>
                            <th>Thời Điểm Công Bố</th>
                            <th>Nhật Ký Thay Đổi</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($releases as $rel)
                            <tr>
                                <td>
                                    <strong style="font-family: monospace; font-size: 0.875rem;">{{ $rel->version }}</strong>
                                    @if ($rel->version === $template->current_version)
                                        <span class="mayhen_admin_badge mayhen_admin_badge_success" style="font-size: 0.625rem; margin-left: 4px;">HIỆN TẠI</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($rel->release_status === 'published')
                                        <span class="mayhen_admin_badge mayhen_admin_badge_success">Published</span>
                                    @elseif ($rel->release_status === 'staged')
                                        <span class="mayhen_admin_badge mayhen_admin_badge_warning">Staged</span>
                                    @elseif ($rel->release_status === 'rolled_back')
                                        <span class="mayhen_admin_badge" style="background-color: #FEE2E2; color: #991B1B;">Rolled Back</span>
                                    @else
                                        <span class="mayhen_admin_badge" style="background-color: #E2E8F0; color: #475569;">Deprecated</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.templates.releases.rollout', ['id' => $template->id, 'releaseId' => $rel->id]) }}" method="POST" style="display: flex; align-items: center; gap: 6px;">
                                        @csrf
                                        <input 
                                            type="number" 
                                            name="rollout_percentage" 
                                            value="{{ $rel->rollout_percentage }}" 
                                            min="0" 
                                            max="100" 
                                            style="width: 60px; padding: 4px 6px; font-size: 0.75rem; border: 1px solid var(--mh-admin-border); border-radius: 4px;"
                                        >
                                        <span style="font-size: 0.75rem;">%</span>
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 2px 6px; font-size: 0.6875rem;" title="Cập nhật tỷ lệ">Lưu</button>
                                    </form>
                                </td>
                                <td>{{ $rel->creator?->name ?? 'Hệ Thống' }}</td>
                                <td>{{ $rel->publisher?->name ?? '—' }}</td>
                                <td>{{ $rel->published_at ? $rel->published_at->format('d/m/Y H:i') : 'Chưa công bố' }}</td>
                                <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $rel->release_notes }}">
                                    {{ $rel->release_notes ?? 'Không có ghi chú' }}
                                    @if ($rel->rollback_reason)
                                        <div style="color: #DC2626; font-size: 0.6875rem; font-style: italic;">Lý do rollback: {{ $rel->rollback_reason }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($rel->version !== $template->current_version)
                                        <form action="{{ route('admin.templates.releases.rollback', ['id' => $template->id, 'releaseId' => $rel->id]) }}" method="POST" onsubmit="return prompt('Nhập lý do hoàn nguyên (ít nhất 6 ký tự):') ? true : false;">
                                            @csrf
                                            <input type="hidden" name="rollback_reason" value="Hoàn nguyên quản trị viên từ giao diện Release Lab">
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 0.6875rem; color: #DC2626;">
                                                ↺ Rollback Bản Này
                                            </button>
                                        </form>
                                    @else
                                        <span style="color: var(--mh-admin-muted); font-size: 0.75rem;">Đang chạy</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 24px; color: var(--mh-admin-muted);">
                                    Chưa ghi nhận lịch sử phát hành cho mẫu thiệp này. Hãy tạo bản phát hành đầu tiên ở form phía trên.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Popup Hoàn Nguyên Khẩn Cấp -->
    @if ($previousRelease ?? null)
        <div id="rollback_modal" style="display: none; position: fixed; inset: 0; background-color: rgba(0, 0, 0, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
            <div style="background-color: #FFFFFF; border-radius: var(--mh-admin-radius-md); max-width: 500px; width: 100%; padding: 24px; box-shadow: var(--mh-admin-shadow-lg);">
                <h3 style="margin-top: 0; font-size: 1.125rem; color: #991B1B; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    Xác Nhận Hoàn Nguyên Khẩn Cấp (Instant Rollback)
                </h3>
                <p style="font-size: 0.875rem; color: var(--mh-admin-charcoal); line-height: 1.5;">
                    Bạn đang chuẩn bị hoàn nguyên mẫu thiệp <strong>{{ $template->name }}</strong> từ phiên bản <code>{{ $template->current_version }}</code> về phiên bản an toàn trước đó: <code>{{ $previousRelease->version }}</code>.
                </p>

                <form action="{{ route('admin.templates.releases.rollback', ['id' => $template->id, 'releaseId' => $previousRelease->id]) }}" method="POST">
                    @csrf
                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_form_label" for="modal_rollback_reason">
                            Lý Do Hoàn Nguyên (Bắt buộc kiểm toán) <span class="required">*</span>
                        </label>
                        <textarea 
                            name="rollback_reason" 
                            id="modal_rollback_reason" 
                            rows="3" 
                            class="mayhen_admin_form_textarea" 
                            required 
                            placeholder="Ví dụ: Phát hiện vỡ layout trên Safari iOS 16 hoặc lỗi font chữ..."
                        ></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="document.getElementById('rollback_modal').style.display = 'none';">
                            Hủy Bỏ
                        </button>
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_danger">
                            Xác Nhận Hoàn Nguyên Ngay Lập Tức
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const vpButtons = document.querySelectorAll('.mayhen_admin_lab_vp_btn');
            const frameLive = document.getElementById('frame_live_container');
            const frameStaged = document.getElementById('frame_staged_container');
            const gaugeLive = document.getElementById('dimension_gauge_live');
            const gaugeStaged = document.getElementById('dimension_gauge_staged');
            const toggleSplit = document.getElementById('toggle_split_view');
            const btnReload = document.getElementById('btn_reload_iframes');
            const iframeLive = document.getElementById('iframe_live');
            const iframeStaged = document.getElementById('iframe_staged');

            // 1. Chuyển đổi Viewports
            vpButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    vpButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const width = this.getAttribute('data-width') + 'px';
                    frameLive.style.width = width;
                    frameStaged.style.width = width;

                    gaugeLive.innerText = this.getAttribute('data-width') + ' × 720px';
                    gaugeStaged.innerText = this.getAttribute('data-width') + ' × 720px';
                });
            });

            // 2. Chuyển đổi Split View / Single View
            if (toggleSplit) {
                toggleSplit.addEventListener('change', function () {
                    if (this.checked) {
                        frameStaged.style.display = 'flex';
                    } else {
                        frameStaged.style.display = 'none';
                    }
                });
            }

            // 3. Nạp lại Iframes
            if (btnReload) {
                btnReload.addEventListener('click', function () {
                    if (iframeLive) iframeLive.src = iframeLive.src;
                    if (iframeStaged) iframeStaged.src = iframeStaged.src;
                });
            }
        });
    </script>
@endpush

