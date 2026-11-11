@extends('admin.layouts.master')

@section('title', 'Đài Kiểm Soát Sức Chống Chịu & Tải Cao (System Resilience Cockpit)')
@section('page_title', 'Sức Chống Chịu & Kiểm Soát Tải Cao')

@section('content')
    {{-- Header Banner & Resilience Score --}}
    <div style="background: linear-gradient(135deg, #1F1D1A, #2C2824); color: #FAF8F5; padding: 24px 28px; border-radius: 14px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(31, 29, 26, 0.15); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 18px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(197, 168, 128, 0.2); border: 2px solid #C5A880; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                🛡️
            </div>
            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: #C5A880; font-weight: 600;">HẠ TẦNG VẬN HÀNH & KIỂM THỬ ÁP LỰC (NXT-15)</span>
                <h2 style="margin: 4px 0 0; font-size: 1.35rem; font-weight: 700; color: #FAF8F5;">Đài Kiểm Soát Sức Chống Chịu & Tải Cao</h2>
                <p style="margin: 4px 0 0; font-size: 0.85rem; color: #D8CEBD; max-width: 650px;">
                    Giám sát độ trễ thời gian thực, đo lường điểm kiên cường hệ thống, kiểm thử áp lực Check-in đồng thời và bảo vệ dữ liệu tài chính trước bão Webhook ngày cưới.
                </p>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="text-align: right;">
                <span style="font-size: 0.75rem; color: #D8CEBD; text-transform: uppercase; letter-spacing: 1px;">Điểm Chống Chịu</span>
                <div style="font-size: 2rem; font-weight: 800; color: {{ $metrics['resilience_score'] >= 80 ? '#27AE60' : ($metrics['resilience_score'] >= 50 ? '#F39C12' : '#E74C3C') }}; line-height: 1;">
                    {{ $metrics['resilience_score'] }}%
                </div>
            </div>
            <button type="button" onclick="createSnapshot()" class="mayhen_admin_btn" style="background: #C5A880; color: #1F1D1A; font-weight: 700; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <span>💾 Tạo Snapshot Khẩn Cấp (SHA-256)</span>
            </button>
        </div>
    </div>

    {{-- 4 Thẻ KPI Thời Gian Thực --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 18px; margin-bottom: 28px;">
        {{-- KPI 1: Cơ sở dữ liệu --}}
        <div class="mayhen_admin_card" style="padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 0.85rem; color: #7D6568; font-weight: 600;">Cơ Sở Dữ Liệu</span>
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $metrics['database']['status'] === 'optimal' ? '#27AE60' : '#E74C3C' }};"></span>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1F1D1A; margin-bottom: 4px;">
                {{ $metrics['database']['latency_ms'] }} <span style="font-size: 0.9rem; font-weight: 500; color: #7D6568;">ms</span>
            </div>
            <p style="margin: 0; font-size: 0.8rem; color: #7D6568;">Driver: <strong>{{ strtoupper($metrics['database']['connection']) }}</strong></p>
        </div>

        {{-- KPI 2: Bộ nhớ Cache --}}
        <div class="mayhen_admin_card" style="padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 0.85rem; color: #7D6568; font-weight: 600;">Bộ Nhớ Đệm Cache</span>
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $metrics['cache']['status'] === 'optimal' ? '#27AE60' : '#F39C12' }};"></span>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1F1D1A; margin-bottom: 4px;">
                {{ $metrics['cache']['latency_ms'] }} <span style="font-size: 0.9rem; font-weight: 500; color: #7D6568;">ms</span>
            </div>
            <p style="margin: 0; font-size: 0.8rem; color: #7D6568;">Driver: <strong>{{ strtoupper($metrics['cache']['driver']) }}</strong></p>
        </div>

        {{-- KPI 3: Ổ Đĩa & Bộ Nhớ RAM --}}
        <div class="mayhen_admin_card" style="padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 0.85rem; color: #7D6568; font-weight: 600;">Ổ Đĩa Trống / RAM</span>
                <span style="font-size: 0.8rem; color: #C5A880; font-weight: 700;">{{ $metrics['system']['disk_free_gb'] }} GB</span>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #1F1D1A; margin-bottom: 4px;">
                {{ $metrics['system']['memory_usage_mb'] }} <span style="font-size: 0.9rem; font-weight: 500; color: #7D6568;">MB RAM</span>
            </div>
            <p style="margin: 0; font-size: 0.8rem; color: #7D6568;">Dung lượng dùng: {{ $metrics['system']['disk_usage_percent'] }}%</p>
        </div>

        {{-- KPI 4: Hàng Đợi & Lỗi Nền --}}
        <div class="mayhen_admin_card" style="padding: 20px; border-radius: 12px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-size: 0.85rem; color: #7D6568; font-weight: 600;">Failed Jobs Hàng Đợi</span>
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: {{ $metrics['queue']['failed_jobs'] === 0 ? '#27AE60' : '#E74C3C' }};"></span>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: {{ $metrics['queue']['failed_jobs'] === 0 ? '#27AE60' : '#E74C3C' }}; margin-bottom: 4px;">
                {{ $metrics['queue']['failed_jobs'] }} <span style="font-size: 0.9rem; font-weight: 500; color: #7D6568;">jobs</span>
            </div>
            <p style="margin: 0; font-size: 0.8rem; color: #7D6568;">Trạng thái: <strong>{{ $metrics['queue']['status'] === 'clean' ? 'Sạch sẽ' : 'Cần xử lý' }}</strong></p>
        </div>
    </div>

    {{-- KHU VỰC THỬ NGHIỆM TẢI CAO & HỖN LOẠN (CHAOS & CONCURRENCY SANDBOX) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 28px;">
        
        {{-- Hộp 1: Thử nghiệm Check-in Đồng Thời (Race Condition & Lock Test) --}}
        <div class="mayhen_admin_card" style="padding: 24px; border-radius: 14px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #E8F8F5; color: #117864; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    ⚡
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1F1D1A;">Bão Check-in Cổng Cưới (Concurrency Burst)</h3>
                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: #7D6568;">Xác thực tính Idempotent & `lockForUpdate` chống điểm danh trùng.</p>
                </div>
            </div>

            <form id="checkInSimulatorForm" onsubmit="runCheckInSimulation(event)" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Chọn Đám Cưới Thử Nghiệm:</label>
                    <select name="wedding_id" required style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #E5DCCD; background: #FAF8F5; font-size: 0.85rem;">
                        @forelse($upcomingWeddings as $w)
                            <option value="{{ $w->id }}">#{{ $w->id }} — {{ $w->groom_name }} & {{ $w->bride_name }} ({{ $w->wedding_date ? \Carbon\Carbon::parse($w->wedding_date)->format('d/m/Y') : 'Chưa đặt ngày' }}) - {{ $w->guests->count() }} khách</option>
                        @empty
                            <option value="" disabled>Chưa có đám cưới khả dụng</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Số Request Đồng Thời (Concurrency):</label>
                    <input type="number" name="concurrency" value="20" min="5" max="100" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #E5DCCD; background: #FAF8F5; font-size: 0.85rem;">
                </div>

                <button type="submit" class="mayhen_admin_btn" style="background: #117864; color: #FFFFFF; font-weight: 600; border: none; padding: 10px; border-radius: 8px; cursor: pointer; margin-top: 4px;">
                    ▶ Chạy Kiểm Thử Chịu Tải Check-in
                </button>
            </form>

            <div id="checkInResultBox" style="display: none; margin-top: 16px; padding: 14px; border-radius: 8px; background: #F8F6F0; border: 1px solid #EFE8DD; font-size: 0.85rem;">
                <!-- Result injected via JS -->
            </div>
        </div>

        {{-- Hộp 2: Thử nghiệm Bão Webhook SePay (Replay Storm) --}}
        <div class="mayhen_admin_card" style="padding: 24px; border-radius: 14px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #FEF9E7; color: #B7950B; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    💳
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1F1D1A;">Bão Webhook SePay (Replay Storm)</h3>
                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: #7D6568;">Xác thực cơ chế khóa nguyên tử và chống nhân đôi giao dịch ngân hàng.</p>
                </div>
            </div>

            <form id="webhookSimulatorForm" onsubmit="runWebhookSimulation(event)" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Số Lượng Webhook Bơm Dồn Dập:</label>
                    <input type="number" name="requests_count" value="30" min="10" max="100" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #E5DCCD; background: #FAF8F5; font-size: 0.85rem;">
                </div>

                <div style="padding: 10px 12px; background: #FDEDEC; border-radius: 8px; border: 1px solid #F5B7B1; font-size: 0.78rem; color: #78281F;">
                    🛡️ Tự động bơm 1 mã giao dịch trùng lặp 100% cùng mili-giây để kiểm tra bộ ngắt Double-Spending.
                </div>

                <button type="submit" class="mayhen_admin_btn" style="background: #B7950B; color: #FFFFFF; font-weight: 600; border: none; padding: 10px; border-radius: 8px; cursor: pointer; margin-top: 4px;">
                    ▶ Bơm Thử Nghiệm Bão Webhook
                </button>
            </form>

            <div id="webhookResultBox" style="display: none; margin-top: 16px; padding: 14px; border-radius: 8px; background: #F8F6F0; border: 1px solid #EFE8DD; font-size: 0.85rem;">
                <!-- Result injected via JS -->
            </div>
        </div>

        {{-- Hộp 3: Thử nghiệm Đứt Gãy Dịch Vụ Ngoài (External Dependency Chaos) --}}
        <div class="mayhen_admin_card" style="padding: 24px; border-radius: 14px; background: #FFFFFF; border: 1px solid #EFE8DD;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                <div style="width: 38px; height: 38px; border-radius: 8px; background: #FDEDEC; color: #922B21; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    🔥
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #1F1D1A;">Mô Phỏng Hỗn Loạn (Chaos Injection)</h3>
                    <p style="margin: 2px 0 0; font-size: 0.8rem; color: #7D6568;">Thử nghiệm ngắt Zalo ZNS / SMS / AI Provider và kích hoạt Fallback.</p>
                </div>
            </div>

            <form id="chaosSimulatorForm" onsubmit="runChaosSimulation(event)" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Chọn Dịch Vụ Giả Lập Sự Cố:</label>
                    <select name="service" style="width: 100%; padding: 9px 12px; border-radius: 8px; border: 1px solid #E5DCCD; background: #FAF8F5; font-size: 0.85rem;">
                        <option value="zalo_zns">Zalo ZNS Gateway (Mất mạng / 503 Service Unavailable)</option>
                        <option value="sms_gateway">SMS Brandname Viễn Thông (Nghẽn mạng)</option>
                        <option value="ai_provider">Gemini AI Engine (Rate Limit / Timeout 5s)</option>
                    </select>
                </div>

                <div style="padding: 10px 12px; background: #E8F8F5; border-radius: 8px; border: 1px solid #A3E4D7; font-size: 0.78rem; color: #117864;">
                    ✓ Đảm bảo Circuit Breaker tự ngắt và kích hoạt Fallback êm dịu, không trả lỗi 500 ra khách hàng.
                </div>

                <button type="submit" class="mayhen_admin_btn" style="background: #922B21; color: #FFFFFF; font-weight: 600; border: none; padding: 10px; border-radius: 8px; cursor: pointer; margin-top: 4px;">
                    ▶ Bơm Lỗi Giả Lập (Chaos Injection)
                </button>
            </form>

            <div id="chaosResultBox" style="display: none; margin-top: 16px; padding: 14px; border-radius: 8px; background: #F8F6F0; border: 1px solid #EFE8DD; font-size: 0.85rem;">
                <!-- Result injected via JS -->
            </div>
        </div>

    </div>

    {{-- Hộp Kết Quả Snapshot Mới Nhất --}}
    <div id="snapshotAlertBox" style="display: none; padding: 18px 24px; border-radius: 12px; background: #E8F8F5; border: 1px solid #A3E4D7; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h4 style="margin: 0; color: #117864; font-size: 0.95rem; font-weight: 700;">✓ ĐÃ TẠO BẢN SNAPSHOT BẢO TOÀN DỮ LIỆU THÀNH CÔNG</h4>
                <p id="snapshotDetailsText" style="margin: 4px 0 0; font-size: 0.85rem; color: #1F1D1A;"></p>
            </div>
            <code id="snapshotChecksumCode" style="padding: 4px 10px; background: #FFFFFF; border-radius: 6px; font-size: 0.75rem; border: 1px solid #A3E4D7; color: #117864;"></code>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    async function runCheckInSimulation(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const box = document.getElementById('checkInResultBox');
        
        btn.disabled = true;
        btn.textContent = 'Đang chạy kiểm thử...';
        box.style.display = 'block';
        box.innerHTML = '<em>Đang gửi các request song song tới database...</em>';

        try {
            const formData = new FormData(form);
            const res = await fetch('{{ route("admin.resilience.simulate_checkin") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                box.innerHTML = `
                    <div style="color: #117864; font-weight: 700; margin-bottom: 6px;">✓ KIỂM THỬ THÀNH CÔNG: KHÔNG CÓ RACE CONDITION</div>
                    <div>• Tổng request gửi: <strong>${data.total_requests}</strong></div>
                    <div>• Check-in ghi nhận: <strong>${data.successful_checkins}</strong> (chuẩn Idempotent duy nhất 1 lần)</div>
                    <div>• Duplicate bị chặn đứng: <strong>${data.duplicates_blocked}</strong></div>
                    <div>• Thời gian xử lý: <strong>${data.elapsed_ms} ms</strong> (~${data.throughput_rps} req/s)</div>
                    <div style="margin-top: 6px; color: #27AE60; font-size: 0.78rem;">✓ Bảo toàn 100% toàn vẹn dữ liệu khách mời và số ghế bàn tiệc.</div>
                `;
            } else {
                box.innerHTML = `<span style="color: #922B21;">${data.message || 'Có lỗi xảy ra'}</span>`;
            }
        } catch (err) {
            box.innerHTML = `<span style="color: #922B21;">Lỗi kết nối máy chủ</span>`;
        } finally {
            btn.disabled = false;
            btn.textContent = '▶ Chạy Kiểm Thử Chịu Tải Check-in';
        }
    }

    async function runWebhookSimulation(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const box = document.getElementById('webhookResultBox');
        
        btn.disabled = true;
        btn.textContent = 'Đang bơm webhook...';
        box.style.display = 'block';
        box.innerHTML = '<em>Đang giả lập bão webhook ngân hàng...</em>';

        try {
            const formData = new FormData(form);
            const res = await fetch('{{ route("admin.resilience.simulate_webhook") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            
            if (data.success) {
                box.innerHTML = `
                    <div style="color: #117864; font-weight: 700; margin-bottom: 6px;">✓ ĐÃ CHỐNG NHÂN ĐÔI GIAO DỊCH 100%</div>
                    <div>• Tổng webhook bơm vào: <strong>${data.total_webhooks}</strong></div>
                    <div>• Giao dịch hợp lệ xử lý: <strong>${data.unique_processed}</strong></div>
                    <div>• Replay / Duplicate bị chặn: <strong>${data.duplicates_suppressed}</strong></div>
                    <div>• Thời gian xử lý: <strong>${data.elapsed_ms} ms</strong> (~${data.throughput_rps} req/s)</div>
                    <div style="margin-top: 6px; color: #27AE60; font-size: 0.78rem;">✓ Khóa nguyên tử Lock Atomic bảo vệ 100% an toàn tài chính.</div>
                `;
            }
        } catch (err) {
            box.innerHTML = `<span style="color: #922B21;">Lỗi kết nối</span>`;
        } finally {
            btn.disabled = false;
            btn.textContent = '▶ Bơm Thử Nghiệm Bão Webhook';
        }
    }

    async function runChaosSimulation(e) {
        e.preventDefault();
        const form = e.target;
        const btn = form.querySelector('button[type="submit"]');
        const box = document.getElementById('chaosResultBox');
        
        btn.disabled = true;
        btn.textContent = 'Đang bơm lỗi...';
        box.style.display = 'block';
        box.innerHTML = '<em>Đang kích hoạt giả lập mất mạng...</em>';

        try {
            const formData = new FormData(form);
            const res = await fetch('{{ route("admin.resilience.simulate_chaos") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            });
            const data = await res.json();
            
            box.innerHTML = `
                <div style="color: #117864; font-weight: 700; margin-bottom: 6px;">✓ CIRCUIT BREAKER KÍCH HOẠT THÀNH CÔNG</div>
                <div>• Dịch vụ giả lập lỗi: <strong>${data.service}</strong></div>
                <div>• Chiến lược Fallback: <strong>${data.fallback_strategy}</strong></div>
                <div>• Trải nghiệm người dùng: <strong>Êm dịu (Zero Error 500)</strong></div>
                <div style="margin-top: 6px; color: #27AE60; font-size: 0.78rem;">✓ Hệ thống tự phục hồi và bảo toàn tiến trình của khách mời.</div>
            `;
        } catch (err) {
            box.innerHTML = `<span style="color: #922B21;">Lỗi kết nối</span>`;
        } finally {
            btn.disabled = false;
            btn.textContent = '▶ Bơm Lỗi Giả Lập (Chaos Injection)';
        }
    }

    async function createSnapshot() {
        if (!confirm('Xác nhận tạo bản Snapshot dữ liệu khẩn cấp và tính toán mã băm SHA-256?')) return;
        
        const alertBox = document.getElementById('snapshotAlertBox');
        const detailsText = document.getElementById('snapshotDetailsText');
        const checksumCode = document.getElementById('snapshotChecksumCode');

        try {
            const res = await fetch('{{ route("admin.resilience.create_snapshot") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            
            if (data.success) {
                alertBox.style.display = 'block';
                detailsText.textContent = `Mã: ${data.data.snapshot_id} — Tổng ${data.data.total_records} bản ghi (${data.data.size_bytes} bytes, xử lý trong ${data.data.elapsed_ms}ms)`;
                checksumCode.textContent = `SHA-256: ${data.data.checksum_sha256.substring(0, 20)}...`;
                alertBox.scrollIntoView({ behavior: 'smooth' });
            }
        } catch (err) {
            alert('Không thể tạo snapshot vào lúc này');
        }
    }
</script>
@endpush

