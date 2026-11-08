{{-- Modal Bảng Đánh Giá Độ Sẵn Sàng Ngày Cưới (Wedding Readiness Modal - TASK-VAL-12) --}}
<div id="mayhen_readiness_modal" class="mayhen_account_modal" style="display: none;" aria-hidden="true" role="dialog" aria-labelledby="readiness_modal_title">
    <div class="mayhen_account_modal_backdrop" data-close-readiness></div>
    <div class="mayhen_account_modal_container" style="max-width: 620px;">
        <div class="mayhen_account_modal_header">
            <div>
                <h3 id="readiness_modal_title" class="mayhen_account_modal_title">Độ Sẵn Sàng Ngày Cưới</h3>
                <p id="readiness_modal_subtitle" style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                    Rà soát 7 hạng mục sống còn trước ngày diễn ra hôn lễ
                </p>
            </div>
            <button type="button" class="mayhen_account_modal_close" data-close-readiness aria-label="Đóng">&times;</button>
        </div>

        <div class="mayhen_account_modal_body" style="max-height: 70vh; overflow-y: auto;">
            {{-- Hộp tóm tắt điểm số --}}
            <div class="mayhen_readiness_summary_box">
                <div id="readiness_summary_circle" class="mayhen_readiness_circle" style="background: #2E7D32;">
                    0%
                </div>
                <div>
                    <h4 id="readiness_summary_title" style="margin: 0 0 4px 0; font-size: 16px; color: #1F1D1A; font-family: 'Cormorant Garamond', serif; font-weight: 700;">
                        Đang đánh giá...
                    </h4>
                    <p id="readiness_summary_desc" style="margin: 0; font-size: 12px; color: #7A726A; line-height: 1.4;">
                        Hoàn tất đầy đủ 7 hạng mục để đám cưới diễn ra trọn vẹn và an tâm nhất.
                    </p>
                </div>
            </div>

            {{-- Danh sách 7 hạng mục --}}
            <div id="readiness_items_container" style="display: flex; flex-direction: column; gap: 10px; margin-top: 8px;">
                {{-- Dynamic via JavaScript --}}
            </div>
        </div>

        <div class="mayhen_account_modal_footer">
            <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" data-close-readiness>
                Đóng Cửa Sổ
            </button>
        </div>
    </div>
</div>

