<!-- ========================================================
     LUXURY LIVE DEMO PREVIEW MODAL (SHARED REUSABLE COMPONENT)
     Compatible with Home & Templates Page
     ======================================================== -->
<div class="mayhen_home_showcase_modal" id="mayhen_home_showcase_modal" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Xem mẫu thiệp cưới trực tiếp">
    <!-- Dark Frosted Glass Backdrop -->
    <div class="mayhen_home_showcase_modal_backdrop" data-mayhen-modal-close></div>

    <!-- Floating Close Button (Top Right) -->
    <button type="button" class="mayhen_home_showcase_modal_close_floating" data-mayhen-modal-close aria-label="Đóng cửa sổ xem mẫu" title="Đóng (Phím Esc)">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
    </button>

    <!-- Floating Top Toolbar (Global Preview Topbar) -->
    <div class="mayhen_home_showcase_modal_top_bar">
        <div class="mayhen_home_showcase_modal_badge_info">
            <span class="mayhen_home_showcase_modal_badge_dot"></span>
            <span class="mayhen_home_showcase_modal_badge_label">Xem Thử</span>
            <strong class="mayhen_home_showcase_modal_header_title" id="mayhen_home_showcase_modal_header_title">Mẫu Thiệp Cưới</strong>
        </div>

        <div class="mayhen_home_showcase_modal_top_bar_divider" aria-hidden="true"></div>

        <div class="mayhen_home_showcase_modal_device_toggles">
            <button type="button" class="mayhen_home_showcase_modal_device_btn is-active" data-mayhen-device-mode="mobile" title="Chế độ xem trên Điện Thoại">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                <span>Mobile</span>
            </button>
            <button type="button" class="mayhen_home_showcase_modal_device_btn" data-mayhen-device-mode="desktop" title="Chế độ xem trên Máy Tính / Toàn Màn Hình">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="3" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
                <span>Desktop</span>
            </button>
        </div>

        <div class="mayhen_home_showcase_modal_top_bar_divider" aria-hidden="true"></div>

        <div class="mayhen_home_showcase_modal_top_actions">
            <a href="#" target="_blank" rel="noopener noreferrer" class="mayhen_home_showcase_modal_newtab_btn" id="mayhen_home_showcase_modal_newtab_link" title="Mở trang thiệp này trong tab mới">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                <span>Mở tab mới</span>
            </a>

            <button type="button" class="mayhen_home_showcase_modal_tool_action_btn" id="mayhen_home_showcase_modal_reload_btn" title="Tải lại trang thiệp để xem lại hiệu ứng mở thiệp">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                <span>Tải lại</span>
            </button>

            <button type="button" class="mayhen_home_showcase_modal_tool_action_btn" id="mayhen_home_showcase_modal_copy_btn" title="Sao chép link chia sẻ mẫu thiệp">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                <span>Sao chép</span>
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="mayhen_home_showcase_modal_toast" id="mayhen_home_showcase_modal_toast" aria-live="polite">
        <span>✓ Đã sao chép liên kết mẫu thiệp!</span>
    </div>

    <!-- Central Preview Container -->
    <div class="mayhen_home_showcase_modal_container" id="mayhen_home_showcase_modal_container">
        <!-- Smartphone Outer Titanium Frame -->
        <div class="mayhen_home_showcase_modal_phone_bezel" id="mayhen_home_showcase_modal_phone_bezel">
            <!-- Screen Container with Perfect Hardware Corner Masking -->
            <div class="mayhen_home_showcase_modal_phone_screen" id="mayhen_home_showcase_modal_phone_screen">
                <!-- Dynamic Island Camera Sensor -->
                <div class="mayhen_home_showcase_modal_phone_island" id="mayhen_home_showcase_modal_phone_island">
                    <span class="mayhen_home_showcase_modal_phone_lens"></span>
                    <span class="mayhen_home_showcase_modal_phone_speaker"></span>
                </div>

                <!-- Live Scrollable Iframe -->
                <iframe class="mayhen_home_showcase_modal_iframe" id="mayhen_home_showcase_modal_iframe" title="Trải nghiệm thực tế thiệp cưới" src="about:blank" loading="lazy"></iframe>
            </div>
        </div>

        <!-- Bottom Sticky Action Bar in Modal -->
        <div class="mayhen_home_showcase_modal_bottom_action">
            <div class="mayhen_home_showcase_modal_bottom_info">
                <span class="mayhen_home_showcase_modal_bottom_cat" id="mayhen_home_showcase_modal_bottom_cat">MẪU THIỆP CƯỚI</span>
                <h4 class="mayhen_home_showcase_modal_bottom_title" id="mayhen_home_showcase_modal_title">Mẫu Thiệp Cưới</h4>
            </div>
            <a href="#" class="mayhen_home_showcase_modal_cta_btn" id="mayhen_home_showcase_modal_create_link">
                <span>Dùng Mẫu Này Ngay</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>

