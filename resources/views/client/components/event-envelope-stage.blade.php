@props([
    'isStudio' => false,
    'isEmbed' => false,
    'envelopeBackImg' => null,
    'envLiner' => 'gold-foil',
    'cardBorder' => 'gold-double',
])
@php
    $initialStateClass = (! $isStudio && empty($isEmbed)) ? 'is-sealed' : 'is-extracted';
    $envelopeImgClass = ! empty($envelopeBackImg) ? 'has-real-envelope-img' : '';
@endphp

{{-- SÂN KHẤU 3D VẬT LÝ CHUẨN PAPERLESS POST (TÚI SAU z:2 -> NẮP 2 MẶT z:20/3 -> TỜ THIỆP XOAY NGANG/ĐỨNG z:6/14 -> TÚI TRƯỚC CHỮ V z:10/4) --}}
<div class="mayhen_event_3d_stage {{ $initialStateClass }} {{ $envelopeImgClass }}"
     id="mayhenEvent3dStage"
     data-initial-state="{{ $initialStateClass }}">

    {{-- Nút tròn Play/Replay (▷) bên trái tấm thiệp giống hệt 00:06 trong Video mẫu --}}
    <button type="button"
            class="mayhen_event_replay_circle_btn"
            data-event-toggle-envelope
            title="Xem lại hiệu ứng mở phong bì & rút thiệp"
            aria-label="Xem lại hiệu ứng rút thiệp">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="6 4 20 12 6 20 6 4"></polygon>
        </svg>
    </button>

    {{-- LỚP 1 (SAU CÙNG z:2): THÂN LƯNG PHONG BÌ + NỀN LÓT MIỆNG TÚI --}}
    <div class="mayhen_event_env_layer_back" id="mayhenEventStudioEnvelopeBack" aria-hidden="true">
        @if(!empty($envelopeBackImg))
            <img src="{{ asset($envelopeBackImg) }}" alt="" class="mayhen_event_real_env_img">
        @else
            <div class="mayhen_event_3d_back_body">
                <div class="mayhen_event_3d_liner_throat mayhen_event_liner_{{ $envLiner }}" data-liner-sync="true"></div>
            </div>
        @endif
    </div>

    {{-- LỚP 2 (NẮP 2 MẶT 3D: z:20 KHI ĐÓNG -> ĐỔI SANG z:3 KHI LẬT QUA 90°) --}}
    <div class="mayhen_event_env_layer_flap" aria-hidden="true">
        <div class="mayhen_event_3d_flap_pivot">
            {{-- Mặt ngoài nắp (hiện khi phong bì đóng kín — đổ bóng vẽ trực tiếp trong SVG để bảo toàn 100% preserve-3d trên mọi trình duyệt) --}}
            <div class="mayhen_event_flap_outer">
                <svg class="mayhen_event_env_svg" viewBox="0 0 460 220" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M 3,2 L 457,2 L 253,199 Q 230,221 207,199 Z" fill="rgba(0,0,0,0.14)" />
                    <path d="M 1,0 L 459,0 L 252,196 Q 230,217 208,196 Z" fill="var(--mh-env-hex)" />
                    <path d="M 1,0 L 459,0 L 252,196 Q 230,217 208,196 Z" fill="rgba(255,255,255,0.06)" />
                    <path d="M 1,0 L 208,196 Q 230,217 252,196 L 459,0" fill="none" stroke="rgba(0,0,0,0.09)" stroke-width="1.2" />
                </svg>
            </div>

            {{-- Mặt trong nắp (hiện khi nắp bật mở lên trên: lớp lót hoa văn + viền giấy phong bì đều đặn 18px) --}}
            <div class="mayhen_event_flap_inner">
                <div class="mayhen_event_flap_liner_fill mayhen_event_liner_{{ $envLiner }}"
                     id="mayhenEventPreviewLiner"
                     data-liner-sync="true"></div>
                <svg class="mayhen_event_env_svg mayhen_event_flap_border_mask" viewBox="0 0 460 220" preserveAspectRatio="none" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M 0,0 L 460,0 L 252,196 Q 230,217 208,196 Z M 22,0 L 438,0 L 246,179 Q 230,194 214,179 Z"
                          fill="var(--mh-env-hex)" />
                    <path d="M 22,0 L 214,179 Q 230,194 246,179 L 438,0" fill="none" stroke="rgba(0,0,0,0.06)" stroke-width="1" />
                </svg>
            </div>
        </div>
    </div>

    {{-- LỚP 3 (TỜ THIỆP 1 TRANG: NẰM NGANG TRONG TÚI z:6 -> RÚT RA & XOAY ĐỨNG z:14) --}}
    <article class="mayhen_event_stationery_sheet mayhen_event_border_{{ $cardBorder }}" id="mayhenEventStationerySheet">
        {{ $slot }}
    </article>

    {{-- LỚP 4 (TÚI TRƯỚC CHỮ V 3 NẾP GẤP GIẤY THỰC: KẸP THIỆP z:10 -> LÙI SAU z:4) --}}
    <div class="mayhen_event_env_layer_pocket" id="mayhenEventStudioEnvelopePocket" aria-hidden="true">
        @if(!empty($envelopeBackImg))
            <img src="{{ asset($envelopeBackImg) }}" alt="" class="mayhen_event_real_env_pocket_img">
        @else
            <svg class="mayhen_event_env_svg mayhen_event_3d_pocket_svg" viewBox="0 0 460 320" preserveAspectRatio="none" aria-hidden="true">
                {{-- Vạt gấp bên Trái --}}
                <path d="M 0,0 L 185,155 L 0,320 Z" fill="var(--mh-env-hex)" />
                <path d="M 0,0 L 185,155 L 0,320 Z" fill="rgba(0,0,0,0.045)" />
                {{-- Vạt gấp bên Phải --}}
                <path d="M 460,0 L 275,155 L 460,320 Z" fill="var(--mh-env-hex)" />
                <path d="M 460,0 L 275,155 L 460,320 Z" fill="rgba(0,0,0,0.025)" />
                {{-- Vạt Đáy gập lên đè nhẹ tạo rãnh chữ V miệng túi + đỉnh bo tròn mềm mại --}}
                <path d="M 0,320 L 182,158 Q 230,126 278,158 L 460,320 Z" fill="var(--mh-env-hex)" />
                <path d="M 0,320 L 182,158 Q 230,126 278,158 L 460,320 Z" fill="rgba(255,255,255,0.04)" />
                {{-- Đường chỉ gập giấy tinh tế tạo khối nổi 3D trên mọi gam màu --}}
                <path d="M 0,0 L 182,158 M 460,0 L 278,158" fill="none" stroke="rgba(0,0,0,0.075)" stroke-width="1.1" />
                <path d="M 0,320 L 182,158 Q 230,126 278,158 L 460,320" fill="none" stroke="rgba(0,0,0,0.07)" stroke-width="1.1" />
            </svg>
        @endif
    </div>
</div>
