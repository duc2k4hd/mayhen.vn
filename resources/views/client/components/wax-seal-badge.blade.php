@php
    $sealConfig = \App\Services\WeddingWaxSealService::resolveWaxSealConfig($invitation ?? null);
    $size = $size ?? 'normal'; // 'small', 'normal', 'large'
    $interactive = $interactive ?? true;
@endphp

<div class="mayhen_wax_seal_component mayhen_wax_seal--{{ $sealConfig['color_palette'] }} mayhen_wax_seal--{{ $sealConfig['rim_style'] }} mayhen_wax_seal--{{ $size }} {{ $interactive ? 'is-interactive' : '' }}"
     data-sound="{{ $sealConfig['crack_sound'] ? 'true' : 'false' }}"
     data-shimmer="{{ $sealConfig['shimmer_effect'] ? 'true' : 'false' }}"
     role="{{ $interactive ? 'button' : 'presentation' }}"
     tabindex="{{ $interactive ? '0' : '-1' }}"
     aria-label="Con dấu sáp hoàng gia {{ $sealConfig['monogram_text'] }}">

    {{-- Viền sáp chảy 3D --}}
    <div class="mayhen_wax_seal_disc">
        {{-- Viền hạt hoặc rãnh xước --}}
        <div class="mayhen_wax_seal_rim"></div>

        {{-- Lõi lòng trong của dấu sáp --}}
        <div class="mayhen_wax_seal_inner">
            {{-- Dải chữ khẩu hiệu dập chìm cong cong --}}
            <div class="mayhen_wax_seal_motto" aria-hidden="true">
                <span>{{ $sealConfig['motto_text'] }}</span>
            </div>

            {{-- Biểu tượng trung tâm --}}
            <div class="mayhen_wax_seal_center">
                @if($sealConfig['emblem'] === 'monogram')
                    <span class="mayhen_wax_monogram_text">{{ $sealConfig['monogram_text'] }}</span>
                @elseif($sealConfig['emblem'] === 'double_happiness')
                    <span class="mayhen_wax_symbol_happiness">囍</span>
                @elseif($sealConfig['emblem'] === 'olive_branch')
                    <svg class="mayhen_wax_svg_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 2C6.5 2 2 6.5 2 12c0 5 3.5 9 8 9.8"></path>
                        <path d="M12 2c5.5 0 10 4.5 10 10 0 5-3.5 9-8 9.8"></path>
                        <path d="M12 22V2"></path>
                        <path d="M12 7c-2 0-3-1.5-3-3"></path>
                        <path d="M12 11c-2 0-3-1.5-3-3"></path>
                        <path d="M12 15c-2 0-3-1.5-3-3"></path>
                        <path d="M12 7c2 0 3-1.5 3-3"></path>
                        <path d="M12 11c2 0 3-1.5 3-3"></path>
                        <path d="M12 15c2 0 3-1.5 3-3"></path>
                    </svg>
                @elseif($sealConfig['emblem'] === 'intertwined_rings')
                    <svg class="mayhen_wax_svg_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="12" r="5"></circle>
                        <circle cx="16" cy="12" r="5"></circle>
                    </svg>
                @elseif($sealConfig['emblem'] === 'love_birds')
                    <svg class="mayhen_wax_svg_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 8s-2-2-4-2-4 2-4 2"></path>
                        <path d="M12 14c-3 0-5 2-5 5h10c0-3-2-5-5-5z"></path>
                        <circle cx="9" cy="9" r="1"></circle>
                        <circle cx="15" cy="9" r="1"></circle>
                    </svg>
                @elseif($sealConfig['emblem'] === 'hydrangea_flower')
                    <svg class="mayhen_wax_svg_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 2a4 4 0 0 0-4 4 4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4z"></path>
                        <path d="M12 14a4 4 0 0 0-4 4 4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4z"></path>
                        <path d="M2 12a4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4 4 4 0 0 0-4 4z"></path>
                        <path d="M14 12a4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4 4 4 0 0 0-4 4z"></path>
                    </svg>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   QUIET LUXURY 3D WAX SEAL BADGE COMPONENT (ĐỀ ÁN 131 — MÂY HẸN)
   100% Pure Vanilla CSS — BEM Prefix mayhen_wax_seal_*
   ========================================================================== */
.mayhen_wax_seal_component {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    cursor: default;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease;
}

.mayhen_wax_seal_component.is-interactive {
    cursor: pointer;
}
.mayhen_wax_seal_component.is-interactive:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.25));
}
.mayhen_wax_seal_component.is-interactive:active {
    transform: scale(0.97);
}

/* Kích thước */
.mayhen_wax_seal--normal .mayhen_wax_seal_disc {
    width: 64px;
    height: 64px;
}
.mayhen_wax_seal--small .mayhen_wax_seal_disc {
    width: 44px;
    height: 44px;
}
.mayhen_wax_seal--large .mayhen_wax_seal_disc {
    width: 88px;
    height: 88px;
}

/* Thân đĩa sáp */
.mayhen_wax_seal_disc {
    position: relative;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 2px 4px rgba(255, 255, 255, 0.25), inset 0 -3px 6px rgba(0, 0, 0, 0.35);
}

/* Các Bảng Màu Hoàng Gia */
.mayhen_wax_seal--burgundy .mayhen_wax_seal_disc {
    background: radial-gradient(circle at 35% 35%, #9E242B 0%, #8C1D24 55%, #560E13 100%);
}
.mayhen_wax_seal--royal_gold .mayhen_wax_seal_disc {
    background: radial-gradient(circle at 35% 35%, #F0D36B 0%, #D4AF37 50%, #8C6D15 100%);
}
.mayhen_wax_seal--sage_green .mayhen_wax_seal_disc {
    background: radial-gradient(circle at 35% 35%, #6E8775 0%, #586E5F 55%, #37453B 100%);
}
.mayhen_wax_seal--dusty_rose .mayhen_wax_seal_disc {
    background: radial-gradient(circle at 35% 35%, #D48C97 0%, #B76E79 55%, #7A4048 100%);
}
.mayhen_wax_seal--midnight_navy .mayhen_wax_seal_disc {
    background: radial-gradient(circle at 35% 35%, #253B66 0%, #1B2A4A 55%, #0D172B 100%);
}
.mayhen_wax_seal--pearl_white .mayhen_wax_seal_disc {
    background: radial-gradient(circle at 35% 35%, #FFFFFF 0%, #F4EFE6 60%, #C4B9A7 100%);
}

/* Kiểu Viền Sáp */
.mayhen_wax_seal--vintage_deckled .mayhen_wax_seal_disc {
    border-radius: 48% 52% 49% 51% / 51% 48% 52% 49%;
}
.mayhen_wax_seal--royal_beaded .mayhen_wax_seal_rim {
    position: absolute;
    inset: 4px;
    border-radius: 50%;
    border: 2px dotted rgba(255, 255, 255, 0.35);
    box-shadow: inset 0 0 2px rgba(0, 0, 0, 0.3);
}
.mayhen_wax_seal--minimal_smooth .mayhen_wax_seal_rim {
    position: absolute;
    inset: 4px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.25);
}

/* Lõi Dập Nổi */
.mayhen_wax_seal_inner {
    position: relative;
    width: 72%;
    height: 72%;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.08);
    box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.4), 0 1px 1px rgba(255, 255, 255, 0.2);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.mayhen_wax_seal_motto {
    position: absolute;
    top: 3px;
    font-size: 5.5px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.5);
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
}
.mayhen_wax_seal--pearl_white .mayhen_wax_seal_motto {
    color: rgba(60, 50, 40, 0.5);
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);
}

.mayhen_wax_seal_center {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #F7E7CE;
    text-shadow: 0 1.5px 2px rgba(0, 0, 0, 0.6), 0 -0.5px 0 rgba(255, 255, 255, 0.4);
}
.mayhen_wax_seal--royal_gold .mayhen_wax_seal_center {
    color: #5A4305;
    text-shadow: 0 1px 1px rgba(255, 255, 255, 0.6), 0 -1px 0 rgba(0, 0, 0, 0.4);
}
.mayhen_wax_seal--pearl_white .mayhen_wax_seal_center {
    color: #7A6953;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8), 0 -1px 1px rgba(0, 0, 0, 0.3);
}

.mayhen_wax_monogram_text {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.15rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    line-height: 1;
}
.mayhen_wax_seal--small .mayhen_wax_monogram_text {
    font-size: 0.85rem;
}
.mayhen_wax_seal--large .mayhen_wax_monogram_text {
    font-size: 1.55rem;
}

.mayhen_wax_symbol_happiness {
    font-family: 'Cinzel', serif;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1;
}

.mayhen_wax_svg_icon {
    width: 22px;
    height: 22px;
}
.mayhen_wax_seal--small .mayhen_wax_svg_icon {
    width: 16px;
    height: 16px;
}
.mayhen_wax_seal--large .mayhen_wax_svg_icon {
    width: 32px;
    height: 32px;
}
</style>

<script>
(function() {
    'use strict';
    // Web Audio API Synthesizer: Tạo tiếng sáp nứt vỡ tự nhiên (Wax Crackle)
    window.mayhenPlayWaxCrackSound = function() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();

            // Tạo chuỗi xung âm thanh nứt gãy (crackle impulse)
            const bufferSize = ctx.sampleRate * 0.15;
            const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
            const data = buffer.getChannelData(0);

            for (let i = 0; i < bufferSize; i++) {
                const decay = Math.exp(-i / (ctx.sampleRate * 0.03));
                data[i] = (Math.random() * 2 - 1) * decay;
            }

            const noise = ctx.createBufferSource();
            noise.buffer = buffer;

            // Bộ lọc dải âm cao đặc trưng của chất liệu sáp vỡ
            const filter = ctx.createBiquadFilter();
            filter.type = 'highpass';
            filter.frequency.value = 1800;

            const gain = ctx.createGain();
            gain.gain.setValueAtTime(0.4, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.14);

            noise.connect(filter);
            filter.connect(gain);
            gain.connect(ctx.destination);

            noise.start();
        } catch (e) {
            // Ignore if audio context not permitted
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.mayhen_wax_seal_component.is-interactive').forEach(function(seal) {
            seal.addEventListener('click', function() {
                if (this.getAttribute('data-sound') === 'true') {
                    window.mayhenPlayWaxCrackSound();
                }
            });
        });
    });
})();
</script>

