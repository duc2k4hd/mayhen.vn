@extends('client.layouts.master')

@section('title', 'Rà Soát Lễ Nghi & Duyệt Thiệp Hôn Lễ: ' . $data['side_label'] . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/family-review.css') }}">
@endpush

@section('content')
<div class="mayhen_family_wrapper">
    <div class="mayhen_family_container">
        {{-- Header & Seal --}}
        <header class="mayhen_family_header">
            <div class="mayhen_family_seal">⚜️</div>
            <span class="mayhen_family_pretitle">MÂY HẸN — RÀ SOÁT LỄ NGHI & DUYỆT THIỆP HÔN LỄ</span>
            <h1 class="mayhen_family_title">{{ $data['wedding']['groom_name'] }} &amp; {{ $data['wedding']['bride_name'] }}</h1>
            <div class="mayhen_family_side_badge">
                {{ $data['side_label'] }}
            </div>
            <p style="font-size: 14px; color: var(--mayhen_family_muted); margin-top: 12px;">
                Kính gửi Bố Mẹ, đây là bản tổng hợp các thông tin trọng yếu về nghi lễ và tiệc cưới để Bố Mẹ kiểm tra kỹ lưỡng trước khi các con phát hành thiệp mời chính thức tới họ hàng, người thân.
            </p>
        </header>

        {{-- Trạng thái hiện tại --}}
        @if($data['status'] === 'approved')
            <div class="mayhen_family_status_banner is-approved">
                <span style="font-size: 20px;">✅</span>
                <div>
                    <strong>Phụ mẫu đã duyệt và chúc phúc thành công!</strong>
                    @if($data['approved_at'])
                        <div style="font-size: 12px; margin-top: 2px;">Thời điểm duyệt: {{ \Carbon\Carbon::parse($data['approved_at'])->format('H:i d/m/Y') }}</div>
                    @endif
                    @if($data['blessing_note'])
                        <div style="margin-top: 6px; font-style: italic; background: rgba(255,255,255,0.7); padding: 8px 12px; border-radius: 8px;">
                            "{{ $data['blessing_note'] }}"
                        </div>
                    @endif
                </div>
            </div>
        @elseif($data['status'] === 'feedback')
            <div class="mayhen_family_status_banner is-feedback">
                <span style="font-size: 20px;">✏️</span>
                <div>
                    <strong>Phụ mẫu đã gửi góp ý chỉnh sửa tới các con:</strong>
                    @if($data['feedback'])
                        <div style="margin-top: 6px; font-style: italic; background: rgba(255,255,255,0.7); padding: 8px 12px; border-radius: 8px;">
                            "{{ $data['feedback'] }}"
                        </div>
                    @endif
                    <div style="font-size: 12px; margin-top: 4px;">Các con đang tiến hành cập nhật lại theo chỉ bảo của Bố Mẹ.</div>
                </div>
            </div>
        @else
            <div class="mayhen_family_status_banner is-pending">
                <span style="font-size: 20px;">⏳</span>
                <div>
                    <strong>Trạng thái: Đang chờ Bố Mẹ rà soát & xác nhận.</strong>
                    <div style="font-size: 13px; margin-top: 2px;">Kính xin Bố Mẹ kiểm tra 4 phần thông tin bên dưới. Nếu mọi chi tiết đã chuẩn mực, xin Bố Mẹ bấm xác nhận ở cuối trang.</div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #1B5E20; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- KHỐI 1: THÔNG TIN PHỤ MẪU HAI BÊN & CẶP ĐÔI --}}
        <section class="mayhen_family_section_card">
            <div class="mayhen_family_section_header">
                <span class="mayhen_family_section_num">1</span>
                <h2 class="mayhen_family_section_title">Phụ Mẫu Hai Bên &amp; Uyên Ương</h2>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Đôi Uyên Ương:</span>
                <span class="mayhen_family_info_value mayhen_family_highlight">{{ $data['wedding']['groom_name'] }} &amp; {{ $data['wedding']['bride_name'] }}</span>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Phụ Mẫu Nhà Trai:</span>
                <span class="mayhen_family_info_value">{{ $data['wedding']['groom_parents'] }}</span>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Phụ Mẫu Nhà Gái:</span>
                <span class="mayhen_family_info_value">{{ $data['wedding']['bride_parents'] }}</span>
            </div>
        </section>

        {{-- KHỐI 2: THỜI GIAN LỄ GIA TIÊN & TIỆC CƯỚI --}}
        <section class="mayhen_family_section_card">
            <div class="mayhen_family_section_header">
                <span class="mayhen_family_section_num">2</span>
                <h2 class="mayhen_family_section_title">Thời Gian Lễ Gia Tiên &amp; Tiệc Cưới</h2>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Ngày Hôn Lễ:</span>
                <span class="mayhen_family_info_value mayhen_family_highlight">{{ $data['wedding']['wedding_date'] }}</span>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Lễ Vu Quy (Nhà Gái):</span>
                <span class="mayhen_family_info_value">{{ $data['wedding']['ceremony_bride'] }}</span>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Lễ Thành Hôn (Nhà Trai):</span>
                <span class="mayhen_family_info_value">{{ $data['wedding']['ceremony_groom'] }}</span>
            </div>
        </section>

        {{-- KHỐI 3: ĐỊA ĐIỂM SẢNH TIỆC --}}
        <section class="mayhen_family_section_card">
            <div class="mayhen_family_section_header">
                <span class="mayhen_family_section_num">3</span>
                <h2 class="mayhen_family_section_title">Địa Điểm Tổ Chức Tiệc Mừng</h2>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Tên Sảnh Tiệc:</span>
                <span class="mayhen_family_info_value mayhen_family_highlight">{{ $data['wedding']['venue_name'] }}</span>
            </div>
            <div class="mayhen_family_info_row">
                <span class="mayhen_family_info_label">Địa Chỉ Đón Khách:</span>
                <span class="mayhen_family_info_value">{{ $data['wedding']['venue_address'] }}</span>
            </div>
        </section>

        {{-- KHỐI 4: XEM TRƯỚC TOÀN BỘ MẪU THIỆP CÔNG KHAI --}}
        <section class="mayhen_family_section_card" style="text-align: center; padding: 28px 24px;">
            <div style="font-size: 28px; margin-bottom: 8px;">💌</div>
            <h3 style="font-family: Georgia, serif; font-size: 18px; margin-bottom: 6px;">Xem Thử Toàn Bộ Thiệp Mời</h3>
            <p style="font-size: 13.5px; color: var(--mayhen_family_muted); max-width: 500px; margin: 0 auto 16px auto;">
                Bố Mẹ có thể bấm nút bên dưới để mở xem thử toàn bộ thiệp cưới điện tử (ảnh cưới, câu chuyện tình yêu, bản đồ chỉ đường, sổ lưu bút) như khách mời sẽ nhìn thấy.
            </p>
            <a href="{{ $data['invitation_preview_url'] }}" target="_blank" rel="noopener noreferrer" class="mayhen_family_btn mayhen_family_btn_feedback" style="display: inline-flex;">
                🔍 Mở Xem Trọn Vẹn Thiệp Cưới
            </a>
        </section>

        {{-- HỘP THAO TÁC PHÊ DUYỆT CỦA PHỤ MẪU --}}
        <div class="mayhen_family_action_box">
            <h3 class="mayhen_family_action_title">Ý Kiến Của Phụ Mẫu</h3>
            <p class="mayhen_family_action_desc">
                Sự đồng thuận và chúc phúc của Bố Mẹ là chỗ dựa thiêng liêng nhất cho ngày trọng đại của các con. Xin Bố Mẹ chọn một trong hai hành động dưới đây:
            </p>

            <div class="mayhen_family_btn_group">
                <button type="button" class="mayhen_family_btn mayhen_family_btn_approve" id="btn-show-approve-form">
                    ⚜️ Bố Mẹ Đồng Ý &amp; Chúc Phúc
                </button>
                <button type="button" class="mayhen_family_btn mayhen_family_btn_feedback" id="btn-show-feedback-form">
                    ✏️ Có Chi Tiết Cần Sửa Lại
                </button>
            </div>

            {{-- Form gửi phê duyệt kèm lời dặn dò --}}
            <div class="mayhen_family_note_panel" id="panel-approve-note">
                <form action="{{ route('family_approval.submit', $data['token']) }}" method="POST">
                    @csrf
                    <input type="hidden" name="decision" value="approved">
                    <label for="approve_note" style="font-weight: 700; font-size: 14px; color: #1B5E20; display: block;">
                        Lời dặn dò / Lời chúc phúc của Bố Mẹ gửi tặng các con (tùy chọn):
                    </label>
                    <textarea name="note" id="approve_note" class="mayhen_family_textarea" placeholder="Bố mẹ đã xem kỹ thông tin hôn lễ và hoàn toàn đồng ý. Chúc hai con trăm năm hạnh phúc, yêu thương gắn kết trọn đời!">{{ old('note', $data['blessing_note']) }}</textarea>
                    <button type="submit" class="mayhen_family_btn mayhen_family_btn_approve" style="width: 100%;">
                        Xác Nhận Đồng Ý &amp; Gửi Lời Chúc Phúc Cho Hai Con
                    </button>
                </form>
            </div>

            {{-- Form gửi góp ý chỉnh sửa --}}
            <div class="mayhen_family_note_panel" id="panel-feedback-note">
                <form action="{{ route('family_approval.submit', $data['token']) }}" method="POST">
                    @csrf
                    <input type="hidden" name="decision" value="feedback">
                    <label for="feedback_note" style="font-weight: 700; font-size: 14px; color: #BF360C; display: block;">
                        Ghi rõ những chi tiết Bố Mẹ muốn các con chỉnh sửa lại:
                    </label>
                    <textarea name="note" id="feedback_note" class="mayhen_family_textarea" placeholder="Ví dụ: Giờ làm lễ gia tiên cần sửa thành 09:30 sáng cho đúng giờ hoàng đạo; Tên của Bác cần viết hoa đầy đủ...">{{ old('note', $data['feedback']) }}</textarea>
                    <button type="submit" class="mayhen_family_btn mayhen_family_btn_feedback" style="width: 100%; border-color: #BF360C; color: #BF360C;">
                        Gửi Ý Kiến Góp Ý Tới Các Con Để Chỉnh Sửa
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/family-review.js') }}"></script>
@endpush

