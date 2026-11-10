@extends('admin.layouts.master')

@section('title', 'Vòng Lặp Phản Hồi Khách Hàng (Customer Delight Feedback Loop) — Mây Hẹn')
@section('page_title', '🌟 Customer Delight Feedback Loop (Vòng Lặp Đo Lường CSAT / NPS & Can Thiệp Khách Hàng)')

@section('content')
    <!-- KPI Header Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid var(--mh-admin-gold);">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Điểm CSAT Trung Bình
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px;">
                <span style="font-size: 1.8rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    {{ $metrics['average_score'] }}
                </span>
                <span style="font-size: 1.1rem; color: #E65100;">/ 5.0 ⭐</span>
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Dựa trên {{ $metrics['total_feedbacks'] }} lượt đánh giá</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2E7D32;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #2E7D32; margin-bottom: 6px;">
                Tỷ Lệ Khách Hài Lòng
            </div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #2E7D32;">
                {{ $metrics['satisfaction_rate'] }}%
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Tỷ lệ chấm 4 - 5 sao (Promoters)</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #1565C0;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #1565C0; margin-bottom: 6px;">
                Chỉ Số NPS (Net Promoter)
            </div>
            <div style="font-size: 1.8rem; font-weight: 700; color: #1565C0;">
                {{ $metrics['nps_score'] > 0 ? '+' : '' }}{{ $metrics['nps_score'] }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Mức độ sẵn sàng giới thiệu Mây Hẹn</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid {{ $metrics['alerts_count'] > 0 ? '#C62828' : '#757575' }};">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: {{ $metrics['alerts_count'] > 0 ? '#C62828' : '#757575' }}; margin-bottom: 6px;">
                Cảnh Báo Cần Can Thiệp
            </div>
            <div style="font-size: 1.8rem; font-weight: 700; color: {{ $metrics['alerts_count'] > 0 ? '#C62828' : '#757575' }};">
                {{ $metrics['alerts_count'] }}
            </div>
            <div style="font-size: 0.75rem; color: {{ $metrics['alerts_count'] > 0 ? '#C62828' : '#7A756E' }}; margin-top: 4px; font-weight: 600;">
                {{ $metrics['alerts_count'] > 0 ? 'Khách đánh giá 1-2 sao chờ xử lý' : '100% ca tiêu cực đã giải quyết' }}
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 16px 20px;">
        <form action="{{ route('admin.intelligence.feedback_loop') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">Cột Mốc</label>
                    <select name="milestone" class="mayhen_admin_form_select" style="min-width: 170px;">
                        <option value="">Tất cả cột mốc</option>
                        <option value="after_publish" {{ $currentMilestone === 'after_publish' ? 'selected' : '' }}>Sau xuất bản thiệp</option>
                        <option value="after_payment" {{ $currentMilestone === 'after_payment' ? 'selected' : '' }}>Sau thanh toán gói</option>
                        <option value="post_wedding" {{ $currentMilestone === 'post_wedding' ? 'selected' : '' }}>Sau ngày cưới</option>
                        <option value="general" {{ $currentMilestone === 'general' ? 'selected' : '' }}>Trải nghiệm chung</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">Số Sao (Rating)</label>
                    <select name="rating" class="mayhen_admin_form_select" style="min-width: 140px;">
                        <option value="">Tất cả mức sao</option>
                        <option value="5" {{ $currentRating == 5 ? 'selected' : '' }}>5 Sao (Rất hài lòng)</option>
                        <option value="4" {{ $currentRating == 4 ? 'selected' : '' }}>4 Sao (Hài lòng)</option>
                        <option value="3" {{ $currentRating == 3 ? 'selected' : '' }}>3 Sao (Bình thường)</option>
                        <option value="2" {{ $currentRating == 2 ? 'selected' : '' }}>2 Sao (Chưa ưng ý)</option>
                        <option value="1" {{ $currentRating == 1 ? 'selected' : '' }}>1 Sao (Thất vọng)</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">Trạng Thái Can Thiệp</label>
                    <select name="status" class="mayhen_admin_form_select" style="min-width: 160px;">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending_review" {{ $currentStatus === 'pending_review' ? 'selected' : '' }}>Chờ can thiệp</option>
                        <option value="contacted" {{ $currentStatus === 'contacted' ? 'selected' : '' }}>Đang liên hệ</option>
                        <option value="resolved" {{ $currentStatus === 'resolved' ? 'selected' : '' }}>Đã hoàn tất</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 8px; align-items: flex-end;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                    Lọc Dữ Liệu
                </button>
                @if($currentMilestone || $currentRating || $currentStatus)
                    <a href="{{ route('admin.intelligence.feedback_loop') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        Xóa Lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Feedback Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <span>💬 Nhật Ký Phản Hồi Khách Hàng (Customer Feedback Stream)</span>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 220px;">Khách Hàng & Cặp Đôi</th>
                        <th style="width: 140px;">Cột Mốc & Danh Mục</th>
                        <th style="width: 130px;">Đánh Giá</th>
                        <th>Ý Kiến / Lời Nhắn Của Khách</th>
                        <th style="width: 160px;">Ticket SLA Cảnh Báo</th>
                        <th style="width: 190px;">Can Thiệp CSKH</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedbacks as $fb)
                        <tr style="{{ $fb->isLowRating() && $fb->action_status === 'pending_review' ? 'background: rgba(198, 40, 40, 0.04);' : '' }}">
                            <td>
                                <strong>{{ $fb->user->name ?? 'Khách Ẩn Danh' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                    {{ $fb->user->email ?? '—' }} &bull; {{ $fb->user->phone ?? 'Chưa có SĐT' }}
                                </div>
                                @if($fb->wedding)
                                    <div style="font-size: 0.6875rem; color: var(--mh-admin-gold); font-weight: 600; margin-top: 2px;">
                                        💍 {{ $fb->wedding->groom_name }} &amp; {{ $fb->wedding->bride_name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge_secondary" style="margin-bottom: 4px; display: inline-block;">
                                    {{ $fb->milestone_label }}
                                </span>
                                <div style="font-size: 0.75rem; color: #7A756E;">
                                    {{ $fb->category_label }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 0.95rem; font-weight: 700; color: #E65100;">
                                    @for($i = 1; $i <= 5; $i++)
                                        {{ $i <= $fb->rating ? '★' : '☆' }}
                                    @endfor
                                </div>
                                <span class="mayhen_admin_badge {{ $fb->getSentimentBadgeClass() }}" style="margin-top: 4px; display: inline-block;">
                                    {{ strtoupper($fb->sentiment) }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($fb->comment))
                                    <div style="font-size: 0.8125rem; color: var(--mh-admin-charcoal); line-height: 1.4;">
                                        "{{ $fb->comment }}"
                                    </div>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--mh-admin-muted); font-style: italic;">Không để lại lời nhắn</span>
                                @endif
                                <div style="font-size: 0.6875rem; color: #9E9E9E; margin-top: 4px;">
                                    Gửi vào: {{ $fb->created_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                            <td>
                                @if($fb->linked_ticket_id && $fb->supportTicket)
                                    <a href="{{ route('admin.support.show', $fb->linked_ticket_id) }}" class="mayhen_admin_badge mayhen_admin_badge_danger" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                        🚨 {{ $fb->supportTicket->ticket_code }}
                                    </a>
                                    <div style="font-size: 0.6875rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                        Trạng thái: {{ strtoupper($fb->supportTicket->status) }}
                                    </div>
                                @else
                                    <span style="font-size: 0.75rem; color: var(--mh-admin-muted);">—</span>
                                @endif
                            </td>
                            <td>
                                @if($fb->action_status === 'resolved')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Hoàn Tất</span>
                                    @if($fb->action_plan_note)
                                        <div style="font-size: 0.75rem; color: #555; margin-top: 4px; font-style: italic;">
                                            "{{ Str::limit($fb->action_plan_note, 60) }}"
                                        </div>
                                    @endif
                                    <div style="font-size: 0.6875rem; color: #888; margin-top: 2px;">
                                        Bởi: {{ $fb->handler->name ?? 'CSKH' }}
                                    </div>
                                @elseif($fb->action_status === 'contacted')
                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Đang Liên Hệ</span>
                                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn_outline" style="margin-top: 4px;" onclick="openActionModal({{ $fb->id }}, '{{ addslashes($fb->action_plan_note ?? '') }}')">
                                        Cập Nhật
                                    </button>
                                @else
                                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_sm mayhen_admin_btn--primary" onclick="openActionModal({{ $fb->id }}, '')">
                                        Can Thiệp CSKH
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px 20px; color: var(--mh-admin-muted);">
                                🕊️ Chưa ghi nhận phản hồi đánh giá nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedbacks->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $feedbacks->links() }}
            </div>
        @endif
    </div>

    <!-- Native Vanilla JS Action Plan Modal -->
    <div id="actionPlanModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: #fff; border-radius: 12px; width: 90%; max-width: 500px; padding: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 12px;">
                📝 Biên Bản Can Thiệp & Phục Hồi Hài Lòng (Delight Action Plan)
            </h3>
            <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 16px;">
                Ghi nhận nội dung CSKH đã trao đổi, phương án bồi hoàn hoặc giải quyết khúc mắc cho cặp đôi:
            </p>

            <form id="actionPlanForm" method="POST" action="">
                @csrf
                <div style="margin-bottom: 14px;">
                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Trạng Thái Can Thiệp</label>
                    <select name="action_status" class="mayhen_admin_form_select" style="width: 100%;">
                        <option value="resolved">Đã Giải Quyết Khách Hài Lòng (Resolved)</option>
                        <option value="contacted">Đã Liên Hệ Chờ Phản Hồi (Contacted)</option>
                        <option value="dismissed">Không Cần Can Thiệp Thêm (Dismissed)</option>
                    </select>
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px;">Biên Bản / Kế Hoạch Xử Lý</label>
                    <textarea name="action_plan_note" id="modalActionNote" rows="4" class="mayhen_admin_form_textarea" style="width: 100%;" placeholder="Ví dụ: Đã gọi điện xin lỗi cô dâu về sự cố tải ảnh, hỗ trợ nén ảnh và tặng thêm 1 tháng lưu trữ vĩnh cửu..." required></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" onclick="closeActionModal()">
                        Hủy
                    </button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                        Lưu Biên Bản
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openActionModal(id, note) {
            const modal = document.getElementById('actionPlanModal');
            const form = document.getElementById('actionPlanForm');
            const noteInput = document.getElementById('modalActionNote');

            form.action = '/admin/intelligence/feedback/' + id + '/resolve';
            noteInput.value = note || '';
            modal.style.display = 'flex';
        }

        function closeActionModal() {
            document.getElementById('actionPlanModal').style.display = 'none';
        }
    </script>
@endsection

