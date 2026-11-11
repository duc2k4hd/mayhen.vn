@php
    $keepsake = $wedding->keepsake ?? null;
@endphp

@if($keepsake && $keepsake->is_enabled && !($isEmbed ?? false))
    {{-- PHÒNG VỆ CHẾ ĐỘ THIỆP KỶ NIỆM (POST-WEDDING MEMORIAL MODE — SECTION 2.8 MASTER PROMPT) --}}
    <section class="mayhen_invitation_section mayhen_memorial_keepsake_section" style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; padding: 48px 20px; text-align: center;">
        <div style="max-width: 680px; margin: 0 auto;">
            <div style="font-size: 1.5rem; color: #C5A880; margin-bottom: 12px;">✦</div>
            <span style="font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase; color: #9C7A4A; font-weight: 600; display: block; margin-bottom: 10px;">
                Khoảnh Khắc Kỷ Niệm • Lời Tri Ân
            </span>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 2rem; color: #1F1D1A; margin: 0 0 16px; font-weight: 600; line-height: 1.3;">
                {{ $keepsake->thank_you_title ?: 'Lời Cảm Ơn Chân Thành Từ Chúng Mình' }}
            </h2>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 1.18rem; font-style: italic; line-height: 1.8; color: #3E3A35; white-space: pre-line; margin-bottom: 24px;">
                {{ $keepsake->thank_you_message ?: \App\Services\WeddingKeepsakeService::DEFAULT_THANK_YOU_MESSAGE }}
            </div>

            @if($keepsake->show_video && $keepsake->getEmbeddedVideoUrl())
                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); margin-top: 24px; border: 1px solid #E5DCCD;">
                    <iframe 
                        src="{{ $keepsake->getEmbeddedVideoUrl() }}" 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen
                        title="Video Highlight Ngày Cưới"
                    ></iframe>
                </div>
            @endif
        </div>
    </section>

    @if($keepsake->hide_rsvp)
        <style>
            /* Ẩn hoàn toàn form gửi RSVP ở Chế Độ Kỷ Niệm */
            .mayhen_invitation_rsvp_form,
            form[data-mayhen-rsvp-form],
            #rsvpForm {
                display: none !important;
            }
        </style>
    @endif

    @if($keepsake->hide_gifts)
        <style>
            /* Ẩn thông tin số tài khoản mừng cưới khi có yêu cầu */
            .mayhen_invitation_bank_card,
            .mayhen_invitation_banking_card,
            .mayhen_invitation_gift_banking {
                display: none !important;
            }
        </style>
    @endif
@endif

