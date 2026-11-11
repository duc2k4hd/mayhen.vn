@extends('admin.layouts.master')

@section('title', 'Đài Quan Sát Chi Phí & Chất Lượng AI (AI Quality & Cost Observatory)')
@section('page_title', 'Đài Quan Sát Chi Phí & Chất Lượng AI')

@section('content')
    {{-- Header & Time Range Filter --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <p style="margin: 0; font-size: 0.85rem; color: var(--mh-admin-muted);">
                Theo dõi chi phí token thực tế, độ trễ phản hồi, kiểm duyệt Brand Safety và tỷ lệ kích hoạt Fallback dự phòng.
            </p>
        </div>

        <form method="GET" action="{{ route('admin.ai_observatory.index') }}" style="display: flex; gap: 8px;">
            <a href="{{ route('admin.ai_observatory.index', ['days' => 7]) }}" class="mayhen_admin_btn" style="padding: 6px 14px; font-size: 0.8rem; font-weight: 600; border-radius: 6px; text-decoration: none; background: {{ $days == 7 ? 'var(--mh-admin-gold)' : '#FFFFFF' }}; color: {{ $days == 7 ? '#FFFFFF' : 'var(--mh-admin-charcoal)' }}; border: 1px solid #D1CCC6;">
                7 Ngày
            </a>
            <a href="{{ route('admin.ai_observatory.index', ['days' => 30]) }}" class="mayhen_admin_btn" style="padding: 6px 14px; font-size: 0.8rem; font-weight: 600; border-radius: 6px; text-decoration: none; background: {{ $days == 30 ? 'var(--mh-admin-gold)' : '#FFFFFF' }}; color: {{ $days == 30 ? '#FFFFFF' : 'var(--mh-admin-charcoal)' }}; border: 1px solid #D1CCC6;">
                30 Ngày
            </a>
            <a href="{{ route('admin.ai_observatory.index', ['days' => 90]) }}" class="mayhen_admin_btn" style="padding: 6px 14px; font-size: 0.8rem; font-weight: 600; border-radius: 6px; text-decoration: none; background: {{ $days == 90 ? 'var(--mh-admin-gold)' : '#FFFFFF' }}; color: {{ $days == 90 ? '#FFFFFF' : 'var(--mh-admin-charcoal)' }}; border: 1px solid #D1CCC6;">
                90 Ngày
            </a>
        </form>
    </div>

    {{-- KPI Overview Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2980B9;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Chi Phí AI
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2980B9;">
                {{ number_format($metrics['total_cost_vnd'], 1, ',', '.') }} đ
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">{{ $metrics['total_requests'] }} lượt gọi</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid var(--mh-admin-gold);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Tokens Tiêu Thụ
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($metrics['total_tokens'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Prompt + Completion</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Độ Trễ Trung Bình
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #27AE60;">
                {{ $metrics['avg_latency_ms'] }} ms
            </div>
            <div style="font-size: 0.75rem; color: #27AE60; margin-top: 4px; font-weight: 600;">Thời gian phản hồi API</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #D35400;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tỷ Lệ Fallback Dự Phòng
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #D35400;">
                {{ $metrics['fallback_rate'] }}%
            </div>
            <div style="font-size: 0.75rem; color: #D35400; margin-top: 4px; font-weight: 600;">Chuyển sang Heuristic</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #8E44AD; background: #FAF5FC;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #8E44AD; margin-bottom: 6px;">
                Tỷ Lệ Chấp Nhận (Acceptance)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #8E44AD;">
                {{ $metrics['acceptance_rate'] }}%
            </div>
            <div style="font-size: 0.75rem; color: #8E44AD; margin-top: 4px; font-weight: 600;">Khách giữ nguyên nội dung</div>
        </div>
    </div>

    {{-- Simulation Sandbox Card --}}
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 22px 26px;">
        <div style="margin-bottom: 16px;">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                Phòng Thử Nghiệm Chất Lượng & Kiểm Duyệt An Toàn (AI Sandbox)
            </h2>
            <p style="font-size: 0.8rem; color: var(--mh-admin-muted); margin: 4px 0 0;">
                Mô phỏng phản hồi từ AI, kiểm tra bộ lọc Brand Safety và đo lường chi phí token trước khi công bố tính năng ra công chúng.
            </p>
        </div>

        <form action="{{ route('admin.ai_observatory.simulate') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 14px; margin-bottom: 14px; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 4px;">Tính Năng</label>
                    <select name="feature_name" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #D1CCC6; font-size: 0.85rem; background: #FFF;">
                        <option value="story_generator">Tạo Câu Chuyện Tình Yêu (Story Generator)</option>
                        <option value="rsvp_wording">Trợ Lý Soạn Lời Mời RSVP</option>
                        <option value="concierge_qa">Hỏi Đáp Lễ Nghi Cưới</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 4px;">Mô Hình (Provider)</label>
                    <select name="provider" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #D1CCC6; font-size: 0.85rem; background: #FFF;">
                        <option value="gemini-1.5-flash">Gemini 1.5 Flash (Tiết kiệm, siêu tốc)</option>
                        <option value="gemini-1.5-pro">Gemini 1.5 Pro (Văn phong cao cấp)</option>
                        <option value="heuristic_fallback">Heuristic Fallback (Văn mẫu Quiet Luxury, 0đ)</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; padding-bottom: 6px;">
                    <label style="font-size: 0.8rem; color: #4A463F; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="checkbox" name="force_fallback" value="1">
                        Cưỡng chế Fallback dự phòng
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 4px;">Câu Lệnh Kiểm Thử (Prompt)</label>
                <textarea name="prompt" rows="2" placeholder="VD: Hãy viết một lời ngỏ lãng mạn cho tiệc cưới của Minh Tuấn và Thu Hà..." required style="width: 100%; padding: 10px 14px; border-radius: 6px; border: 1px solid #D1CCC6; font-size: 0.88rem; font-family: inherit;">{{ session('simulated_prompt', 'Hãy viết một đoạn câu chuyện tình yêu phong cách Quiet Luxury cho cặp đôi Minh Tuấn & Thu Hà.') }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="mayhen_admin_btn" style="padding: 8px 18px; font-size: 0.85rem; font-weight: 700; background: var(--mh-admin-gold); color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer;">
                    ⚡ Chạy Thử Nghiệm & Đo Lường
                </button>
            </div>
        </form>

        {{-- Simulation Result Display --}}
        @if(session('simulation_result'))
            @php $res = session('simulation_result'); @endphp
            <div style="margin-top: 18px; padding: 16px 20px; border-radius: 8px; background: {{ $res['success'] ? '#FAF8F5' : '#FDEDEC' }}; border: 1px solid {{ $res['success'] ? '#ECE7E1' : '#F5B7B1' }};">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <strong style="font-size: 0.88rem; color: var(--mh-admin-charcoal);">Kết Quả Thực Thi:</strong>
                        @if($res['fallback_applied'])
                            <span style="background: #FEF9E7; color: #B7950B; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">DỰ PHÒNG HEURISTIC</span>
                        @elseif($res['success'])
                            <span style="background: #E8F8F5; color: #117864; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">AI GENERATED THÀNH CÔNG</span>
                        @else
                            <span style="background: #FDEDEC; color: #922B21; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">CHẶN BỞI BRAND SAFETY</span>
                        @endif
                    </div>
                    <div style="font-size: 0.75rem; color: #7A756E;">
                        Chi phí: <strong>{{ number_format($res['cost_vnd'], 2, ',', '.') }}đ</strong> &bull; Độ trễ: <strong>{{ $res['latency_ms'] }} ms</strong>
                    </div>
                </div>
                <div style="font-size: 0.85rem; color: #4A463F; line-height: 1.5; white-space: pre-wrap; font-style: italic;">
                    "{{ $res['output'] }}"
                </div>
            </div>
        @endif
    </div>

    {{-- Usage Logs Table --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #ECE7E1; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 0.95rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                Nhật Ký Gọi AI & Đo Lường Chi Phí Gần Nhất
            </h3>
            <span style="font-size: 0.75rem; color: #7A756E;">Tự động che mờ PII nhạy cảm</span>
        </div>

        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #ECE7E1;">
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Thời Gian</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Tính Năng / Provider</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Tokens / Chi Phí</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Độ Trễ</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Trạng Thái</th>
                        <th style="padding: 12px 18px; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Hành Vi Khách</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr style="border-bottom: 1px solid #F0ECE6;">
                            <td style="padding: 14px 18px; font-size: 0.8rem; color: #7A756E;">
                                <div>{{ $log->created_at->format('H:i:s') }}</div>
                                <div style="font-size: 0.72rem;">{{ $log->created_at->format('d/m/Y') }}</div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: var(--mh-admin-charcoal); font-size: 0.88rem;">
                                    {{ $log->feature_label }}
                                </div>
                                <div style="font-size: 0.75rem; color: #7A756E; font-family: monospace;">
                                    {{ $log->provider }}
                                </div>
                            </td>
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 600; color: var(--mh-admin-charcoal); font-size: 0.85rem;">
                                    {{ number_format($log->total_tokens, 0, ',', '.') }} tokens
                                </div>
                                <div style="font-size: 0.72rem; color: #2980B9;">
                                    {{ number_format($log->cost_vnd, 2, ',', '.') }} đ
                                </div>
                            </td>
                            <td style="padding: 14px 18px; font-size: 0.85rem; font-weight: 600; color: {{ $log->latency_ms < 500 ? '#27AE60' : '#E67E22' }};">
                                {{ $log->latency_ms }} ms
                            </td>
                            <td style="padding: 14px 18px;">
                                @if($log->status === 'success')
                                    <span style="background: #E8F8F5; color: #117864; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                                        ✓ THÀNH CÔNG
                                    </span>
                                @elseif($log->status === 'fallback_applied')
                                    <span style="background: #FEF9E7; color: #B7950B; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                                        FALLBACK
                                    </span>
                                @elseif($log->status === 'blocked_safety')
                                    <span style="background: #FDEDEC; color: #922B21; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                                        SAFETY BLOCK
                                    </span>
                                @else
                                    <span style="background: #EAECEE; color: #7F8C8D; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 4px;">
                                        {{ $log->status }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px;">
                                <form action="{{ route('admin.ai_observatory.record_action', $log->id) }}" method="POST" style="display: flex; gap: 4px;">
                                    @csrf
                                    <button type="submit" name="action" value="accepted" title="Chấp nhận" style="border: 1px solid #D1CCC6; background: {{ $log->user_action === 'accepted' ? '#E8F8F5' : '#FFF' }}; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">
                                        {{ $log->user_action === 'accepted' ? '✓ Giữ' : 'Giữ' }}
                                    </button>
                                    <button type="submit" name="action" value="edited" title="Có sửa đổi" style="border: 1px solid #D1CCC6; background: {{ $log->user_action === 'edited' ? '#FEF9E7' : '#FFF' }}; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">
                                        Sửa
                                    </button>
                                    <button type="submit" name="action" value="rejected" title="Bỏ qua" style="border: 1px solid #D1CCC6; background: {{ $log->user_action === 'rejected' ? '#FDEDEC' : '#FFF' }}; padding: 3px 6px; border-radius: 4px; font-size: 0.72rem; cursor: pointer;">
                                        Bỏ
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px 20px; text-align: center; color: var(--mh-admin-muted);">
                                Chưa có nhật ký AI nào trong khoảng thời gian đã chọn. Hãy chạy thử nghiệm trong Sandbox phía trên.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #ECE7E1;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection

