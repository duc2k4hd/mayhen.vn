@extends('admin.layouts.master')

@section('title', 'Buồng Replay Vận Hành: ' . strtoupper($type) . ' #' . $id . '')
@section('page_title', '⏱️ Read-Only Operations Replay (Hợp Nhất Chuỗi Hành Vi ' . strtoupper($type) . ' #' . $id . ')')

@section('content')
    <!-- Top Scope Summary Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                    <a href="{{ route('admin.intelligence.replay.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        &larr; Tra Cứu Khác
                    </a>
                    <span class="mayhen_admin_badge mayhen_admin_badge_secondary" style="font-size: 0.75rem;">
                        PHẠM VI: {{ strtoupper($type) }}
                    </span>
                    <span class="mayhen_admin_badge mayhen_admin_badge_info" style="font-size: 0.75rem;">
                        {{ $replay['total_steps'] }} BƯỚC DIỄN TIẾN
                    </span>
                    @if($isMasked)
                        <span class="mayhen_admin_badge mayhen_admin_badge_success" style="font-size: 0.75rem;">
                            🛡️ PII PROTECTION: ON (LUẬT 91/2025)
                        </span>
                    @else
                        <span class="mayhen_admin_badge mayhen_admin_badge_danger" style="font-size: 0.75rem;">
                            🔓 PII UNMASKED (SUPER ADMIN LENS)
                        </span>
                    @endif
                </div>

                <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                    @if($replay['target_wedding'])
                        💍 Đám Cưới: {{ $replay['target_wedding']->groom_name }} &amp; {{ $replay['target_wedding']->bride_name }}
                    @else
                        Hồ Sơ Vận Hành: #{{ $id }}
                    @endif
                </h2>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 4px;">
                    Môi trường Sandbox hoàn toàn Read-Only &bull; Zero DB Pollution Guarantee
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                @if($canUnmask)
                    @if($isMasked)
                        <a href="{{ request()->fullUrlWithQuery(['unmask' => 1]) }}" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline" onclick="return confirm('CẢNH BÁO KIỂM TOÁN: Bạn đang yêu cầu giải mã số điện thoại và STK khách hàng theo quyền Super Admin. Thao tác này sẽ được ghi vào Audit Log bảo vệ dữ liệu cá nhân Luật 91/2025/QH15. Tiếp tục?');">
                            🔍 Mở Kính Safe Support Lens (Unmask PII)
                        </a>
                    @else
                        <a href="{{ request()->url() }}" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn--primary">
                            🛡️ Kích Hoạt Lại Masking PII
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>

    @if(!empty($replay['anomalies']))
        <!-- Anomaly Detector Banner -->
        <div class="mayhen_admin_card" style="margin-bottom: 24px; background: #FFF8E1; border-left: 4px solid #FFA000; padding: 18px 20px;">
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <span style="font-size: 1.4rem;">⚠️</span>
                <div>
                    <strong style="font-size: 0.9375rem; color: #E65100;">Phát Hiện {{ count($replay['anomalies']) }} Điểm Cần Lưu Ý Trong Chuỗi Sự Kiện:</strong>
                    <ul style="margin: 6px 0 0 16px; padding: 0; font-size: 0.8125rem; color: #5D4037;">
                        @foreach($replay['anomalies'] as $anomaly)
                            <li>
                                <strong>Bước #{{ $anomaly['step_index'] }}:</strong> {{ $anomaly['message'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Interactive Stepper Controller -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 16px 20px; background: #FAF9F6; border: 1px solid var(--mh-admin-border);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 0.875rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    Bộ Điều Khiển Replay:
                </span>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline" onclick="goToStep(1)">
                    &laquo; Đầu
                </button>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline" onclick="prevStep()">
                    &lsaquo; Trước
                </button>
                <span id="currentStepDisplay" style="font-size: 0.875rem; font-weight: 700; color: var(--mh-admin-gold); min-width: 90px; text-align: center;">
                    Bước 1 / {{ max(1, $replay['total_steps']) }}
                </span>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline" onclick="nextStep()">
                    Tiếp &rsaquo;
                </button>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline" onclick="goToStep({{ $replay['total_steps'] }})">
                    Cuối &raquo;
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" id="playPauseBtn" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm" onclick="toggleAutoPlay()">
                    ▶ Tự Động Replay (2s)
                </button>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" onclick="showAllSteps()">
                    👁️ Hiện Toàn Bộ
                </button>
            </div>
        </div>
    </div>

    <!-- Chronological Event Timeline Stream -->
    <div style="position: relative; padding-left: 28px; margin-bottom: 40px;">
        <!-- Vertical Timeline Line -->
        <div style="position: absolute; left: 11px; top: 10px; bottom: 10px; width: 2px; background: var(--mh-admin-border);"></div>

        @forelse($replay['timeline'] as $step)
            @php
                $severityColor = match($step['severity']) {
                    'success' => '#2E7D32',
                    'danger' => '#C62828',
                    'warning' => '#E65100',
                    default => '#4A423B',
                };
                $badgeClass = match($step['source']) {
                    'Finance' => 'mayhen_admin_badge_success',
                    'Support' => 'mayhen_admin_badge_danger',
                    'Feedback' => 'mayhen_admin_badge_warning',
                    'Studio' => 'mayhen_admin_badge_info',
                    default => 'mayhen_admin_badge_secondary',
                };
            @endphp

            <div class="replay-step-card mayhen_admin_card" id="step-{{ $step['step_index'] }}" data-step="{{ $step['step_index'] }}" style="margin-bottom: 18px; padding: 18px 22px; position: relative; transition: all 0.3s ease; border-left: 4px solid {{ $severityColor }};">
                <!-- Timeline Node Circle -->
                <div style="position: absolute; left: -28px; top: 22px; width: 22px; height: 22px; border-radius: 50%; background: #fff; border: 3px solid {{ $severityColor }}; box-shadow: 0 0 0 3px #FAF9F6; display: flex; align-items: center; justify-content: center; font-size: 0.625rem; font-weight: 700;">
                    {{ $step['step_index'] }}
                </div>

                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span class="mayhen_admin_badge {{ $badgeClass }}">
                            {{ $step['source'] }}
                        </span>
                        <strong style="font-size: 0.95rem; color: var(--mh-admin-charcoal);">
                            {{ $step['title'] }}
                        </strong>
                    </div>

                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); display: flex; align-items: center; gap: 8px;">
                        <span>🕒 {{ $step['time_formatted'] }}</span>
                        @if($step['gap_from_prev_minutes'] > 0)
                            <span style="background: #EFEBE9; padding: 2px 6px; border-radius: 4px; font-size: 0.6875rem;">
                                +{{ $step['gap_from_prev_minutes'] }} phút
                            </span>
                        @endif
                    </div>
                </div>

                <p style="font-size: 0.84375rem; color: #4A423B; margin: 0 0 10px 0; line-height: 1.45;">
                    {{ $step['description'] }}
                </p>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 0.75rem; color: var(--mh-admin-muted); border-top: 1px dashed var(--mh-admin-border-subtle); padding-top: 8px;">
                    <div>
                        👤 Tác nhân: <strong>{{ $step['actor'] }}</strong>
                    </div>
                    @if(!empty($step['metadata']))
                        <div style="font-family: monospace; font-size: 0.6875rem; background: #F5F5F5; padding: 2px 6px; border-radius: 4px;">
                            {{ json_encode($step['metadata']) }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="mayhen_admin_card" style="text-align: center; padding: 40px; color: var(--mh-admin-muted);">
                🕊️ Không tìm thấy sự kiện nào trong chuỗi lịch sử của phạm vi này.
            </div>
        @endforelse
    </div>

    <script>
        const totalSteps = {{ count($replay['timeline']) }};
        let currentStep = 1;
        let playInterval = null;

        function updateStepUI() {
            document.getElementById('currentStepDisplay').innerText = 'Bước ' + currentStep + ' / ' + totalSteps;
            const cards = document.querySelectorAll('.replay-step-card');
            cards.forEach(card => {
                const s = parseInt(card.getAttribute('data-step'));
                if (s === currentStep) {
                    card.style.opacity = '1';
                    card.style.boxShadow = '0 6px 20px rgba(197, 168, 128, 0.35)';
                    card.style.transform = 'scale(1.01)';
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else if (s < currentStep) {
                    card.style.opacity = '0.85';
                    card.style.boxShadow = 'none';
                    card.style.transform = 'scale(1)';
                } else {
                    card.style.opacity = '0.35';
                    card.style.boxShadow = 'none';
                    card.style.transform = 'scale(1)';
                }
            });
        }

        function goToStep(s) {
            currentStep = Math.max(1, Math.min(totalSteps, s));
            updateStepUI();
        }

        function nextStep() {
            if (currentStep < totalSteps) {
                currentStep++;
                updateStepUI();
            } else {
                stopAutoPlay();
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepUI();
            }
        }

        function showAllSteps() {
            stopAutoPlay();
            const cards = document.querySelectorAll('.replay-step-card');
            cards.forEach(card => {
                card.style.opacity = '1';
                card.style.boxShadow = 'none';
                card.style.transform = 'scale(1)';
            });
            document.getElementById('currentStepDisplay').innerText = 'Toàn bộ (' + totalSteps + ')';
        }

        function toggleAutoPlay() {
            if (playInterval) {
                stopAutoPlay();
            } else {
                startAutoPlay();
            }
        }

        function startAutoPlay() {
            const btn = document.getElementById('playPauseBtn');
            btn.innerText = '⏸ Tạm Dừng';
            if (currentStep >= totalSteps) {
                currentStep = 1;
                updateStepUI();
            }
            playInterval = setInterval(() => {
                if (currentStep < totalSteps) {
                    nextStep();
                } else {
                    stopAutoPlay();
                }
            }, 2000);
        }

        function stopAutoPlay() {
            if (playInterval) {
                clearInterval(playInterval);
                playInterval = null;
            }
            const btn = document.getElementById('playPauseBtn');
            if (btn) btn.innerText = '▶ Tự Động Replay (2s)';
        }

        // Initialize on step 1
        if (totalSteps > 0) {
            updateStepUI();
        }
    </script>
@endsection

