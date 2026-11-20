{{-- Trợ Lý Chatbot AI Mây Hẹn — Zero-Trust Grounded AI Popup Widget --}}
@php
    $chatbotBrandName = \App\Models\PlatformSetting::getBrandName();
    $chatbotCategories = \App\Models\AiKnowledgeEntry::CATEGORIES;
@endphp

<div
    class="mayhen_client_chatbot_root"
    id="mayhenClientChatbot"
    data-ask-url="{{ route('client.ai_chatbot.ask') }}"
    data-train-url="{{ route('client.ai_chatbot.train') }}"
    data-feedback-url="{{ route('client.ai_chatbot.feedback') }}"
    data-docs-url="{{ route('docs.index') }}"
    data-templates-url="{{ route('templates.index') }}"
>
    {{-- Khung Popup Chat (mở trực tiếp khi bấm nút Trợ lý AI trong thanh điều hướng chân trang) --}}
    <section
        class="mayhen_client_chatbot_popup"
        id="mayhenChatbotPopup"
        role="dialog"
        aria-label="Trợ lý AI {{ $chatbotBrandName }}"
        aria-hidden="true"
    >
        <header class="mayhen_client_chatbot_header">
            <div class="mayhen_client_chatbot_header_info">
                <div class="mayhen_client_chatbot_avatar" aria-hidden="true">AI</div>
                <div>
                    <h2 class="mayhen_client_chatbot_title">Trợ lý {{ $chatbotBrandName }}</h2>
                    <p class="mayhen_client_chatbot_subtitle">Chỉ trả lời từ dữ liệu đã xác thực &amp; Docs</p>
                </div>
            </div>
            <div class="mayhen_client_chatbot_header_actions">
                <button
                    type="button"
                    class="mayhen_client_chatbot_icon_btn"
                    id="mayhenChatbotClearBtn"
                    title="Bắt đầu cuộc trò chuyện mới"
                    aria-label="Bắt đầu cuộc trò chuyện mới"
                    hidden
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                        <path d="M3 3v5h5"></path>
                    </svg>
                </button>
                <button
                    type="button"
                    class="mayhen_client_chatbot_icon_btn"
                    id="mayhenChatbotTrainToggleBtn"
                    title="Góp ý dạy thêm cho Trợ lý AI"
                    aria-label="Góp ý dạy thêm cho Trợ lý AI"
                >
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                </button>
                <button
                    type="button"
                    class="mayhen_client_chatbot_icon_btn"
                    id="mayhenChatbotCloseBtn"
                    title="Đóng cửa sổ chat"
                    aria-label="Đóng cửa sổ chat"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </header>

        {{-- Khu vực tin nhắn --}}
        <div class="mayhen_client_chatbot_messages" id="mayhenChatbotMessages" aria-live="polite">
            <div class="mayhen_client_chatbot_msg mayhen_client_chatbot_msg_bot">
                <div class="mayhen_client_chatbot_bubble">
                    <p class="mayhen_client_chatbot_text">Xin chào! Mình là Trợ lý AI của {{ $chatbotBrandName }}. Mình có thể gợi ý mẫu thiệp đúng phong cách bạn thích hoặc mở nhanh bài hướng dẫn sử dụng chi tiết khi bạn cần.</p>
                    <div class="mayhen_client_chatbot_badge_verified">Nguồn xác thực từ hệ thống {{ $chatbotBrandName }}</div>
                </div>
            </div>

            {{-- Gợi ý câu hỏi nhanh --}}
            <div class="mayhen_client_chatbot_quick_chips" id="mayhenChatbotQuickChips">
                <button type="button" class="mayhen_client_chatbot_chip" data-prompt="Gợi ý mẫu thiệp cưới phong cách sang trọng">Mẫu thiệp sang trọng</button>
                <button type="button" class="mayhen_client_chatbot_chip" data-prompt="Có những mẫu thiệp cưới miễn phí nào đẹp?">Mẫu thiệp miễn phí</button>
                <button type="button" class="mayhen_client_chatbot_chip" data-prompt="Hướng dẫn tạo thiệp cưới cho người mới bắt đầu">Hướng dẫn tạo thiệp</button>
                <button type="button" class="mayhen_client_chatbot_chip" data-prompt="Bảng giá gói dịch vụ Mây Hẹn là bao nhiêu?">Bảng giá &amp; gói</button>
                <button type="button" class="mayhen_client_chatbot_chip" data-prompt="Cách quản lý khách mời, RSVP và điểm danh QR">Khách mời &amp; QR</button>
            </div>
        </div>

        {{-- Ngăn góp ý huấn luyện AI (chuyển vào hàng đợi Admin kiểm duyệt — Zero-Trust) --}}
        <div class="mayhen_client_chatbot_train_panel" id="mayhenChatbotTrainPanel" hidden>
            <div class="mayhen_client_chatbot_train_head">
                <strong>Góp ý dạy thêm cho Trợ lý AI</strong>
                <button type="button" class="mayhen_client_chatbot_train_close" id="mayhenChatbotTrainCancelBtn">Quay lại chat</button>
            </div>
            <p class="mayhen_client_chatbot_train_note">
                Mọi đóng góp đều đi qua kiểm duyệt của Quản trị viên {{ $chatbotBrandName }} trước khi kích hoạt để bảo đảm độ chính xác tuyệt đối.
            </p>
            <form class="mayhen_client_chatbot_train_form" id="mayhenChatbotTrainForm">
                <label class="mayhen_client_chatbot_label">
                    <span>Chủ đề</span>
                    <select class="mayhen_client_chatbot_select" name="category" id="mayhenChatbotTrainCategory">
                        @foreach($chatbotCategories as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="mayhen_client_chatbot_label">
                    <span>Câu hỏi khách thường gặp</span>
                    <input type="text" class="mayhen_client_chatbot_input" name="question" id="mayhenChatbotTrainQuestion" maxlength="190" required placeholder="Ví dụ: Làm sao đổi nhạc nền cho thiệp?">
                </label>
                <label class="mayhen_client_chatbot_label">
                    <span>Câu trả lời hoặc thông tin bạn đề xuất</span>
                    <textarea class="mayhen_client_chatbot_textarea" name="suggested_answer" id="mayhenChatbotTrainAnswer" rows="3" maxlength="1000" required placeholder="Nhập nội dung góp ý rõ ràng, không chèn mã HTML..."></textarea>
                </label>
                <div class="mayhen_client_chatbot_train_status" id="mayhenChatbotTrainStatus" role="status"></div>
                <button type="submit" class="mayhen_client_chatbot_train_submit" id="mayhenChatbotTrainSubmitBtn">
                    Gửi đề xuất chờ Admin duyệt
                </button>
            </form>
        </div>

        {{-- Thanh nhập câu hỏi --}}
        <form class="mayhen_client_chatbot_composer" id="mayhenChatbotForm" autocomplete="off">
            <input
                type="text"
                class="mayhen_client_chatbot_composer_input"
                id="mayhenChatbotInput"
                name="message"
                maxlength="600"
                placeholder="Hỏi về mẫu thiệp, bảng giá hoặc cách sử dụng..."
                aria-label="Nhập câu hỏi cho Trợ lý AI"
                required
            >
            <button type="submit" class="mayhen_client_chatbot_send_btn" id="mayhenChatbotSendBtn" aria-label="Gửi câu hỏi">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </section>
</div>
