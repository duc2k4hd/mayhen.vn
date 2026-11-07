{{-- Modal Mã QR Thiệp Cưới & Thẻ In Sang Trọng (Mây Hẹn) --}}
<div id="mayhen-qr-modal" class="mayhen_qr_modal" aria-hidden="true" role="dialog" aria-labelledby="mayhen-qr-couple-name">
    <div class="mayhen_qr_modal_backdrop"></div>

    <div class="mayhen_qr_modal_dialog">
        <div class="mayhen_qr_modal_header">
            <div>
                <span class="mayhen_qr_modal_subtitle">MÃ QR & THẺ IN THIỆP CƯỚI</span>
                <h3 id="mayhen-qr-couple-name" class="mayhen_qr_modal_title">Cô Dâu & Chú Rể</h3>
                <p id="mayhen-qr-wedding-date" class="mayhen_qr_modal_date">Ngày hôn lễ thiêng liêng</p>
            </div>
            <button type="button" class="mayhen_qr_modal_close" aria-label="Đóng cửa sổ">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="mayhen_qr_modal_body">
            <!-- Khu vực hiển thị Canvas QR xem trước -->
            <div class="mayhen_qr_preview_box">
                <div class="mayhen_qr_canvas_wrapper">
                    <canvas id="mayhen-qr-preview-canvas" width="220" height="220" aria-label="Mã QR thiệp cưới"></canvas>
                </div>
                <div class="mayhen_qr_hint">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    <span>Quét thử mã QR bằng camera điện thoại hoặc Zalo</span>
                </div>
            </div>

            <!-- Liên kết thiệp cưới & Nút chép -->
            <div class="mayhen_qr_link_section">
                <label class="mayhen_qr_link_label">Đường dẫn thiệp cưới trực tiếp:</label>
                <div class="mayhen_qr_link_field">
                    <a id="mayhen-qr-invitation-link" href="#" target="_blank" rel="noopener" class="mayhen_qr_link_text">https://mayhen.vn/thiep/...</a>
                    <button type="button" id="mayhen-qr-copy-btn" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span>Sao chép</span>
                    </button>
                </div>
            </div>

            <!-- Khối ghi chú in ấn cho cặp đôi -->
            <div class="mayhen_qr_tip_card">
                <div class="mayhen_qr_tip_icon">💡</div>
                <div class="mayhen_qr_tip_text">
                    <strong>Gợi ý in ấn cho Cặp Đôi:</strong>
                    Tải về thẻ in kích thước chuẩn HD để gửi nhà in kẹp vào phong bì thiệp giấy truyền thống hoặc in Standee để tại bàn đón khách lễ cưới.
                </div>
            </div>
        </div>

        <div class="mayhen_qr_modal_footer">
            <button type="button" id="mayhen-qr-download-card-btn" class="mayhen_account_btn mayhen_account_btn_gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Tải Thẻ QR In Thiệp (.PNG)</span>
            </button>

            <button type="button" id="mayhen-qr-download-raw-btn" class="mayhen_account_btn mayhen_account_btn_outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
                <span>Tải Riêng Mã QR Vuông</span>
            </button>
        </div>
    </div>
</div>

