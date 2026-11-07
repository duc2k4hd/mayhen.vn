@php
    $isEmbedMode = ($isEmbed ?? false) || request()->boolean('embed') || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
    $invConfig = is_array($invitation->custom_config ?? null)
        ? $invitation->custom_config
        : (json_decode((string) ($invitation->custom_config ?? ''), true) ?? []);
    $parkingGuide = $invConfig['parking_guide'] ?? [];
    $isParkingEnabled = !empty($parkingGuide['enabled']);
    $hasParkingData = !empty($parkingGuide['car_parking']) || !empty($parkingGuide['motorbike_parking']) || !empty($parkingGuide['entrance_guide']) || !empty($parkingGuide['fee_note']) || !empty($parkingGuide['map_tip']);
@endphp

@if(!$isEmbedMode && $isParkingEnabled && $hasParkingData)
<div id="mayhen-parking-concierge-root" class="mayhen_parking_concierge_root" aria-label="Hướng dẫn bãi đỗ xe và lối vào sảnh tiệc">
    <!-- Nút bấm nổi kích hoạt Parking Concierge -->
    <button type="button" 
            id="mayhen-parking-btn-trigger" 
            class="mayhen_parking_pill_btn" 
            title="Xem chỉ dẫn bãi đỗ xe ô tô, xe máy và lối vào sảnh tiệc"
            aria-haspopup="dialog"
            aria-expanded="false"
            aria-controls="mayhen-parking-modal">
        <span class="mayhen_parking_pill_icon" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                <circle cx="7" cy="17" r="2"></circle>
                <circle cx="17" cy="17" r="2"></circle>
                <path d="M5 11l2-6h10l2 6"></path>
            </svg>
        </span>
        <span class="mayhen_parking_pill_text">Bãi Đỗ Xe & Lối Vào</span>
    </button>

    <!-- Modal Hướng Dẫn Chi Tiết -->
    <div id="mayhen-parking-modal" class="mayhen_parking_modal_overlay" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mayhen_parking_modal_card">
            <div class="mayhen_parking_modal_header">
                <div class="mayhen_parking_header_badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span>Chỉ Dẫn Tiện Ích Quan Khách</span>
                </div>
                <h3 class="mayhen_parking_modal_title">Bãi Đỗ Xe & Lối Vào Sảnh Tiệc</h3>
                <p class="mayhen_parking_modal_subtitle">
                    Gia đình gửi đến bạn chỉ dẫn thuận tiện nhất khi di chuyển đến chung vui cùng chúng mình
                </p>
                <button type="button" class="mayhen_parking_close_btn" id="mayhen-parking-btn-close" aria-label="Đóng cửa sổ hướng dẫn">
                    &times;
                </button>
            </div>

            <div class="mayhen_parking_modal_body">
                @if(!empty($parkingGuide['car_parking']))
                    <div class="mayhen_parking_info_row">
                        <div class="mayhen_parking_icon_box mayhen_parking_icon_car">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                                <circle cx="7" cy="17" r="2"></circle>
                                <circle cx="17" cy="17" r="2"></circle>
                                <path d="M5 11l2-6h10l2 6"></path>
                            </svg>
                        </div>
                        <div class="mayhen_parking_info_content">
                            <h4 class="mayhen_parking_info_label">Bãi Đỗ Xe Ô Tô</h4>
                            <p class="mayhen_parking_info_text">{{ $parkingGuide['car_parking'] }}</p>
                        </div>
                    </div>
                @endif

                @if(!empty($parkingGuide['motorbike_parking']))
                    <div class="mayhen_parking_info_row">
                        <div class="mayhen_parking_icon_box mayhen_parking_icon_moto">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="5" cy="16" r="3"></circle>
                                <circle cx="19" cy="16" r="3"></circle>
                                <path d="M12 16h3l3-7h-4l-2 4h-4"></path>
                            </svg>
                        </div>
                        <div class="mayhen_parking_info_content">
                            <h4 class="mayhen_parking_info_label">Bãi Đỗ Xe Máy</h4>
                            <p class="mayhen_parking_info_text">{{ $parkingGuide['motorbike_parking'] }}</p>
                        </div>
                    </div>
                @endif

                @if(!empty($parkingGuide['entrance_guide']))
                    <div class="mayhen_parking_info_row">
                        <div class="mayhen_parking_icon_box mayhen_parking_icon_entrance">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                                <path d="M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4"></path>
                            </svg>
                        </div>
                        <div class="mayhen_parking_info_content">
                            <h4 class="mayhen_parking_info_label">Lối Lên Sảnh & Thang Máy</h4>
                            <p class="mayhen_parking_info_text">{{ $parkingGuide['entrance_guide'] }}</p>
                        </div>
                    </div>
                @endif

                @if(!empty($parkingGuide['fee_note']))
                    <div class="mayhen_parking_info_row">
                        <div class="mayhen_parking_icon_box mayhen_parking_icon_fee">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                <circle cx="12" cy="12" r="2"></circle>
                                <path d="M6 12h.01M18 12h.01"></path>
                            </svg>
                        </div>
                        <div class="mayhen_parking_info_content">
                            <h4 class="mayhen_parking_info_label">Vé & Chi Phí Gửi Xe</h4>
                            <p class="mayhen_parking_info_text">{{ $parkingGuide['fee_note'] }}</p>
                        </div>
                    </div>
                @endif

                @if(!empty($parkingGuide['map_tip']))
                    <div class="mayhen_parking_tip_box">
                        <div class="mayhen_parking_tip_icon">💡</div>
                        <div class="mayhen_parking_tip_text">
                            <strong>Lưu ý di chuyển:</strong> {{ $parkingGuide['map_tip'] }}
                        </div>
                    </div>
                @endif
            </div>

            <div class="mayhen_parking_modal_footer">
                @php
                    $mapUrl = $wedding->extra_data['event_party_map_url'] ?? ($wedding->extra_data['party_map_url'] ?? null);
                @endphp
                @if($mapUrl)
                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_parking_action_map_btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                            <line x1="8" y1="2" x2="8" y2="18"></line>
                            <line x1="16" y1="6" x2="16" y2="22"></line>
                        </svg>
                        <span>Mở Bản Đồ Đường Đi</span>
                    </a>
                @endif
                <button type="button" class="mayhen_parking_action_close_btn" id="mayhen-parking-btn-close-bottom">
                    <span>Đã Hiểu</span>
                </button>
            </div>
        </div>
    </div>

    <style>
        .mayhen_parking_concierge_root {
            position: fixed;
            bottom: calc(env(safe-area-inset-bottom, 16px) + 68px);
            left: 16px;
            z-index: 9980;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .mayhen_parking_pill_btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            background: rgba(255, 253, 249, 0.94);
            border: 1px solid #D8CEBD;
            border-radius: 999px;
            box-shadow: 0 4px 16px rgba(31, 29, 26, 0.12);
            color: #2C2824;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: all 0.25s ease;
        }

        .mayhen_parking_pill_btn:hover {
            background: #FFFFFF;
            border-color: #C5A880;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(197, 168, 128, 0.28);
            color: #9C7A4A;
        }

        .mayhen_parking_pill_icon {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9C7A4A;
        }

        .mayhen_parking_modal_overlay {
            position: fixed;
            inset: 0;
            background: rgba(31, 29, 26, 0.65);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .mayhen_parking_modal_overlay.is-active {
            opacity: 1;
            visibility: visible;
        }

        .mayhen_parking_modal_card {
            background: #FFFDF9;
            border: 1px solid #EFE8DD;
            border-radius: 20px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 48px rgba(31, 29, 26, 0.22);
            transform: translateY(20px) scale(0.96);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .mayhen_parking_modal_overlay.is-active .mayhen_parking_modal_card {
            transform: translateY(0) scale(1);
        }

        .mayhen_parking_modal_header {
            padding: 24px 24px 16px 24px;
            border-bottom: 1px solid #F0EAE1;
            position: relative;
        }

        .mayhen_parking_header_badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: #9C7A4A;
            background: #F8F4ED;
            padding: 3px 10px;
            border-radius: 12px;
            margin-bottom: 8px;
        }

        .mayhen_parking_modal_title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 24px;
            font-weight: 600;
            color: #1F1D1A;
            margin: 0 0 6px 0;
            line-height: 1.25;
        }

        .mayhen_parking_modal_subtitle {
            font-size: 13px;
            color: #7A726A;
            margin: 0;
            line-height: 1.5;
        }

        .mayhen_parking_close_btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #F0EAE1;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #5A534B;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mayhen_parking_close_btn:hover {
            background: #E5DCCD;
            color: #1F1D1A;
        }

        .mayhen_parking_modal_body {
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .mayhen_parking_info_row {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 14px;
            background: #FAF8F5;
            border: 1px solid #F0EAE1;
            border-radius: 12px;
        }

        .mayhen_parking_icon_box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #FFFFFF;
            border: 1px solid #EAE3D9;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #9C7A4A;
        }

        .mayhen_parking_info_content {
            flex: 1;
        }

        .mayhen_parking_info_label {
            font-size: 13px;
            font-weight: 700;
            color: #1F1D1A;
            margin: 0 0 4px 0;
        }

        .mayhen_parking_info_text {
            font-size: 13px;
            color: #4A453E;
            margin: 0;
            line-height: 1.55;
            word-break: break-word;
        }

        .mayhen_parking_tip_box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: #FFF9EF;
            border: 1px dashed #E5D2B8;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 12.5px;
            color: #6E5B3D;
            line-height: 1.5;
        }

        .mayhen_parking_modal_footer {
            padding: 14px 24px 20px 24px;
            border-top: 1px solid #F0EAE1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mayhen_parking_action_map_btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            background: #9C7A4A;
            color: #FFFFFF;
            text-decoration: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .mayhen_parking_action_map_btn:hover {
            background: #85663B;
            color: #FFFFFF;
        }

        .mayhen_parking_action_close_btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            background: #FAF8F5;
            border: 1px solid #D8CEBD;
            color: #4A453E;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .mayhen_parking_action_close_btn:hover {
            background: #EFE8DD;
            color: #1F1D1A;
        }

        @media (max-width: 480px) {
            .mayhen_parking_concierge_root {
                bottom: calc(env(safe-area-inset-bottom, 16px) + 60px);
                left: 12px;
            }
            .mayhen_parking_pill_btn {
                padding: 7px 12px;
                font-size: 12px;
            }
            .mayhen_parking_modal_card {
                border-radius: 16px;
            }
            .mayhen_parking_modal_header, .mayhen_parking_modal_body, .mayhen_parking_modal_footer {
                padding-left: 18px;
                padding-right: 18px;
            }
        }
    </style>

    <script>
        (function() {
            var btnTrigger = document.getElementById('mayhen-parking-btn-trigger');
            var modal = document.getElementById('mayhen-parking-modal');
            var btnClose = document.getElementById('mayhen-parking-btn-close');
            var btnCloseBottom = document.getElementById('mayhen-parking-btn-close-bottom');

            if (!btnTrigger || !modal) return;

            function openModal() {
                modal.classList.add('is-active');
                modal.setAttribute('aria-hidden', 'false');
                btnTrigger.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('is-active');
                modal.setAttribute('aria-hidden', 'true');
                btnTrigger.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            btnTrigger.addEventListener('click', openModal);
            if (btnClose) btnClose.addEventListener('click', closeModal);
            if (btnCloseBottom) btnCloseBottom.addEventListener('click', closeModal);

            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('is-active')) {
                    closeModal();
                }
            });
        })();
    </script>
</div>
@endif

