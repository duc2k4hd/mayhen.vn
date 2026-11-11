@extends('admin.layouts.master')

@section('title', 'Diễn Tập Trải Nghiệm Khách Mời — ' . ($wedding->groom_name ?? '') . ' & ' . ($wedding->bride_name ?? '') . '')
@section('page_title', '🎭 Guest Journey Synthetic Rehearsal (Diễn Tập Hành Trình Khách Mời)')

@section('breadcrumb_parent')
    <a href="{{ route('admin.invitations.index') }}">Thiệp Cưới</a> &bull;
    <a href="{{ route('admin.invitations.show', $invitation->id) }}">#{{ $invitation->id }}</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/invitations.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 20px;">
        <div>
            <div class="mayhen_admin_page_title">
                <span>{{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }}</span>
                <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 0.75rem;">🎭 Chế Độ Diễn Tập Sandbox</span>
            </div>
            <div class="mayhen_admin_page_subtitle">
                Mã thiệp: <code>{{ $invitation->slug }}</code> &bull; Mẫu: <strong>{{ $template->name ?? 'Mặc định' }}</strong> &bull; Ngày cưới: <strong>{{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định' }}</strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('admin.invitations.show', $invitation->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Về Chi Tiết Thiệp
            </a>
            <button type="button" id="btn_run_e2e" class="mayhen_admin_btn mayhen_admin_btn_success" style="font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                <span>Bắt Đầu Diễn Tập Tự Động (Run E2E Runner)</span>
            </button>
            <a href="{{ url('/thiep/' . $invitation->slug . '?rehearsal=1') }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline">
                Mở Sandbox Tab Mới &nearr;
            </a>
        </div>
    </div>

    <!-- Sandbox Defense Guarantee Banner -->
    <div class="mayhen_admin_card" style="background: #F4FBF7; border-left: 4px solid #2E7D32; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 1.5rem;">🛡️</span>
            <div>
                <strong style="font-size: 0.9375rem; color: #1B5E20;">Môi Trường Giả Lập Cách Ly Hoàn Toàn (Zero DB Pollution Guarantee)</strong>
                <div style="font-size: 0.8125rem; color: #3D5A45; margin-top: 2px;">
                    Mọi hành động điền RSVP thử, gửi lời chúc hay quét mã điểm danh trong phiên diễn tập đều được kiểm thử thật và dọn dẹp sạch sẽ tức thì, không gây ô nhiễm CSDL thật của cặp đôi.
                </div>
            </div>
        </div>
        <div>
            <span class="mayhen_admin_badge mayhen_admin_badge_success" id="sandbox_badge">Sandbox Active &bull; 0 Bytes Rác</span>
        </div>
    </div>

    <!-- E2E Execution Console & Live Stepper (NXT-05) -->
    <div class="mayhen_admin_card" id="e2e_console_box" style="margin-bottom: 24px; padding: 20px; display: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="mayhen_admin_badge" id="runner_status_badge" style="background: var(--mh-admin-gold); color: #fff; font-weight: 700;">
                    ĐANG DIỄN TẬP...
                </span>
                <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);" id="runner_heading">
                    Tiến Trình Thực Thi E2E Khách Mời 5 Giai Đoạn
                </strong>
            </div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                Tổng thời gian: <strong id="runner_total_latency" style="color: var(--mh-admin-charcoal);">0 ms</strong> &bull; Trạng thái CSDL: <strong style="color: #2E7D32;">0 Bytes Rác (Clean)</strong>
            </div>
        </div>

        <!-- Progress Bar -->
        <div style="width: 100%; height: 8px; background: #EFE8DD; border-radius: 4px; overflow: hidden; margin-bottom: 16px;">
            <div id="runner_progress_bar" style="width: 0%; height: 100%; background: var(--mh-admin-gold); transition: width 0.3s ease;"></div>
        </div>

        <div id="runner_summary_text" style="font-size: 0.8125rem; color: var(--mh-admin-muted); line-height: 1.5;">
            Đang khởi động runner giả lập trải nghiệm thực tế...
        </div>
    </div>

    <!-- 2 Columns Layout: Left Live Mockup | Right 5 Rehearsal Stages -->
    <div class="mayhen_admin_detail_layout" style="display: grid; grid-template-columns: 360px 1fr; gap: 24px;">
        <!-- Left: Live Phone Mockup with Sandbox Mode -->
        <div class="mayhen_admin_phone_col">
            <div class="mayhen_admin_phone_wrapper">
                <div class="mayhen_admin_phone_island"></div>
                <div class="mayhen_admin_phone_screen">
                    <iframe 
                        src="{{ url('/thiep/' . $invitation->slug . '?embed=1&rehearsal=1') }}" 
                        title="Rehearsal Sandbox Preview {{ $invitation->slug }}"
                        loading="lazy"
                        id="rehearsal_iframe"
                        style="width: 100%; height: 100%; border: none;"
                    ></iframe>
                </div>
            </div>

            <!-- Synthetic Guest Profile Box -->
            <div class="mayhen_admin_card" style="margin-top: 16px; padding: 14px; background: #FFFDF9;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                    Quan Khách Giả Lập Đang Diễn Tập
                </div>
                <div style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">
                    <strong>{{ $rehearsalGuest['name'] }}</strong><br>
                    <span style="font-size: 0.75rem; color: #7A756E;">
                        Nhóm: {{ $rehearsalGuest['group_name'] }} &bull; Bàn: {{ $rehearsalGuest['table_name'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Right: 5 Stages Rehearsal Guide & Live Status -->
        <div>
            <!-- Stage 1 -->
            <div class="mayhen_admin_card" id="stage_card_1" style="margin-bottom: 16px; padding: 20px; transition: all 0.3s ease;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div id="stage_num_1" style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        1
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                Giai Đoạn 1: Mở Thiệp & Hiệu Ứng Khởi Đầu (Reveal & Motion)
                            </h4>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral" id="stage_badge_1">Chưa chạy</span>
                        </div>
                        <p id="stage_desc_1" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Khách mời nhận link qua Zalo/Facebook, chạm mở phong bì lụa, cánh thiệp mở ra mượt mà và nhạc cưới du dương cất lên.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Phong bì mở êm ái</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Âm thanh tự phát sau chạm</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Khử chớp nháy Layout Shift</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 2 -->
            <div class="mayhen_admin_card" id="stage_card_2" style="margin-bottom: 16px; padding: 20px; transition: all 0.3s ease;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div id="stage_num_2" style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        2
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                Giai Đoạn 2: Duyệt 10 Section Tiêu Chuẩn & Đồng Bộ Live Sync
                            </h4>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral" id="stage_badge_2">Chưa chạy</span>
                        </div>
                        <p id="stage_desc_2" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Khách cuộn dọc xem câu chuyện tình yêu, thông tin phụ mẫu hai bên, đồng hồ đếm ngược, bản đồ Google Maps và album 10 ảnh cưới.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">10/10 Section hiển thị đầy đủ</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Bản đồ chỉ đường mở đúng toạ độ</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Lịch cưới đồng bộ Apple/Google Calendar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 3 -->
            <div class="mayhen_admin_card" id="stage_card_3" style="margin-bottom: 16px; padding: 20px; transition: all 0.3s ease;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div id="stage_num_3" style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        3
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                Giai Đoạn 3: Xác Nhận Tham Dự RSVP Trực Quan
                            </h4>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral" id="stage_badge_3">Chưa chạy</span>
                        </div>
                        <p id="stage_desc_3" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Quan khách lựa chọn "Có tham dự", chọn số người đi cùng (người lớn/trẻ em), ghi chú khẩu vị ăn chay hoặc dị ứng.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Form RSVP phản hồi tức thì</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Tự động tính sĩ số bàn tiệc</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Chống spam CSRF & Throttle</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 4 -->
            <div class="mayhen_admin_card" id="stage_card_4" style="margin-bottom: 16px; padding: 20px; transition: all 0.3s ease;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div id="stage_num_4" style="width: 36px; height: 36px; border-radius: 50%; background: #FAF3E0; color: #9C7A4A; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        4
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                                Giai Đoạn 4: Sổ Ký Tên Điện Tử & Gửi Lời Chúc Mừng
                            </h4>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral" id="stage_badge_4">Chưa chạy</span>
                        </div>
                        <p id="stage_desc_4" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Quan khách gửi tâm tình, lời chúc phúc vào lưu bút kỹ thuật số, xuất hiện rực rỡ với tem niêm phong sáp hoặc hiệu ứng stardust.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Hiệu ứng thẻ chúc bay bổng</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral">Lọc từ ngữ nhạy cảm tự động</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stage 5 -->
            <div class="mayhen_admin_card" id="stage_card_5" style="padding: 20px; transition: all 0.3s ease;">
                <div style="display: flex; align-items: flex-start; gap: 14px;">
                    <div id="stage_num_5" style="width: 36px; height: 36px; border-radius: 50%; background: #E8F5E9; color: #1B5E20; display: flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0;">
                        5
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 4px;">
                            <h4 style="font-size: 0.95rem; font-weight: 700; color: #1B5E20; margin: 0;">
                                Giai Đoạn 5: Quét Mã QR & Tiếp Tân Đón Tiếp Tại Sảnh Tiệc
                            </h4>
                            <span class="mayhen_admin_badge mayhen_admin_badge_neutral" id="stage_badge_5">Chưa chạy</span>
                        </div>
                        <p id="stage_desc_5" style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin: 0 0 10px; line-height: 1.5;">
                            Ngày cưới đến, khách quét QR cá nhân tại bàn tiếp tân, hệ thống tra cứu số bàn ngay và điểm danh chỉ trong 1 giây.
                        </p>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Điểm danh 1 chạm</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Thông báo số bàn tiệc</span>
                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Đồng bộ Rescue Cockpit</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btnRun = document.getElementById('btn_run_e2e');
            const consoleBox = document.getElementById('e2e_console_box');
            const progressBar = document.getElementById('runner_progress_bar');
            const statusBadge = document.getElementById('runner_status_badge');
            const latencyText = document.getElementById('runner_total_latency');
            const summaryText = document.getElementById('runner_summary_text');
            const iframe = document.getElementById('rehearsal_iframe');

            if (!btnRun) return;

            btnRun.addEventListener('click', async function () {
                btnRun.disabled = true;
                btnRun.innerHTML = '<span>⏳ Đang chạy diễn tập E2E...</span>';
                consoleBox.style.display = 'block';
                progressBar.style.width = '10%';
                statusBadge.innerText = 'ĐANG DIỄN TẬP...';
                statusBadge.style.backgroundColor = 'var(--mh-admin-gold)';
                summaryText.innerText = 'Đang kết nối Sandbox Engine và gửi chuỗi yêu cầu kiểm thử 5 giai đoạn...';

                // Reset stage badges
                for (let i = 1; i <= 5; i++) {
                    const badge = document.getElementById('stage_badge_' + i);
                    if (badge) {
                        badge.className = 'mayhen_admin_badge mayhen_admin_badge_warning';
                        badge.innerText = 'Đang kiểm tra...';
                    }
                }

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const response = await fetch('{{ route('admin.invitations.run_rehearsal', $invitation->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const res = await response.json();

                    if (!response.ok || !res.success) {
                        throw new Error(res.message || 'Lỗi không xác định khi thực thi E2E runner.');
                    }

                    const data = res.data;
                    progressBar.style.width = '100%';
                    latencyText.innerText = data.total_latency_ms + ' ms';

                    // Update each stage dynamically
                    if (data.stages && Array.isArray(data.stages)) {
                        data.stages.forEach(st => {
                            const badge = document.getElementById('stage_badge_' + st.stage);
                            const desc = document.getElementById('stage_desc_' + st.stage);
                            const card = document.getElementById('stage_card_' + st.stage);

                            if (badge) {
                                if (st.status === 'passed') {
                                    badge.className = 'mayhen_admin_badge mayhen_admin_badge_success';
                                    badge.innerText = `✓ Đạt (${st.latency_ms} ms)`;
                                    if (card) card.style.borderLeft = '4px solid #2E7D32';
                                } else {
                                    badge.className = 'mayhen_admin_badge mayhen_admin_badge_danger';
                                    badge.innerText = `✕ Lỗi (${st.latency_ms} ms)`;
                                    if (card) card.style.borderLeft = '4px solid #C62828';
                                }
                            }

                            if (desc && st.summary) {
                                desc.innerHTML = `<strong>${st.summary}</strong>`;
                            }
                        });
                    }

                    if (data.overall_status === 'passed') {
                        statusBadge.className = 'mayhen_admin_badge mayhen_admin_badge_success';
                        statusBadge.innerText = '✓ 100% WEDDING-READY';
                        statusBadge.style.backgroundColor = '#2E7D32';
                        summaryText.innerHTML = `<strong style="color: #2E7D32;">CHỨNG NHẬN SẴN SÀNG 100%:</strong> Toàn bộ 5 giai đoạn trải nghiệm quan khách đã hoàn thành xuất sắc (${data.stages_passed}/5 bước) trong ${data.total_latency_ms}ms. CSDL đã được dọn sạch tuyệt đối (0 Bytes rác).`;
                    } else {
                        statusBadge.className = 'mayhen_admin_badge mayhen_admin_badge_warning';
                        statusBadge.innerText = 'CẦN LƯU Ý';
                        statusBadge.style.backgroundColor = '#D97706';
                        summaryText.innerHTML = `<span style="color: #D97706;">Hoàn thành ${data.stages_passed}/5 bước. Vui lòng kiểm tra lại các mục chưa đạt màu đỏ phía dưới.</span>`;
                    }

                    // Reload iframe to refresh view
                    if (iframe) {
                        iframe.src = iframe.src;
                    }

                } catch (err) {
                    progressBar.style.width = '100%';
                    progressBar.style.backgroundColor = '#DC2626';
                    statusBadge.innerText = 'LỖI THỰC THI';
                    statusBadge.style.backgroundColor = '#DC2626';
                    summaryText.innerText = 'Lỗi trong quá trình chạy E2E Runner: ' + err.message;
                } finally {
                    btnRun.disabled = false;
                    btnRun.innerHTML = '<span>↻ Chạy Lại Diễn Tập E2E</span>';
                }
            });
        });
    </script>
@endpush
