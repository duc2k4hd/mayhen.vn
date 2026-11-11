@php
    $invitationModel = $invitation ?? null;
    $musicConfig = \App\Services\WeddingMusicService::resolveMusicConfig($invitationModel);
@endphp

@if(($musicConfig['enabled'] ?? true) && ($musicConfig['floating_dock'] ?? true) && !($isEmbed ?? false) && !empty($musicConfig['url']))
<div id="mayhenMusicDock" class="mayhen_invitation_music_dock is-paused" role="button" tabindex="0" aria-label="Bật hoặc tạm dừng nhạc nền hôn lễ" title="{{ $musicConfig['title'] }} — {{ $musicConfig['artist'] }}">
    <div class="mayhen_invitation_vinyl_disc">
        <div class="mayhen_invitation_vinyl_groove"></div>
        <div class="mayhen_invitation_vinyl_groove mayhen_groove_inner"></div>
        <div class="mayhen_invitation_vinyl_center">
            <span class="mayhen_invitation_vinyl_dot"></span>
        </div>
    </div>
    <div class="mayhen_invitation_sound_waves" aria-hidden="true">
        <span class="mayhen_wave_bar bar_1"></span>
        <span class="mayhen_wave_bar bar_2"></span>
        <span class="mayhen_wave_bar bar_3"></span>
    </div>
    <div class="mayhen_invitation_music_tooltip" aria-hidden="true">
        <span class="mayhen_tooltip_note">♪</span>
        <span class="mayhen_tooltip_text">{{ $musicConfig['title'] }}</span>
    </div>

    <audio id="mayhenInvitationAudio" class="mayhen_invitation_audio" preload="auto" {{ ($musicConfig['loop'] ?? true) ? 'loop' : '' }}>
        <source src="{{ $musicConfig['url'] }}" type="audio/mpeg">
        Trình duyệt của bạn không hỗ trợ thẻ phát âm thanh HTML5.
    </audio>
</div>

<style>
/* ==========================================================================
   QUIET LUXURY FLOATING VINYL MUSIC DOCK (ĐỀ ÁN 129 — MÂY HẸN)
   100% Pure Vanilla CSS — BEM Prefix mayhen_invitation_*
   ========================================================================== */
.mayhen_invitation_music_dock {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px 6px 6px;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(197, 168, 128, 0.35);
    border-radius: 999px;
    box-shadow: 0 4px 20px rgba(31, 29, 26, 0.08);
    cursor: pointer;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, background 0.3s ease;
}

.mayhen_invitation_music_dock:hover {
    transform: scale(1.04);
    box-shadow: 0 6px 24px rgba(31, 29, 26, 0.12);
    background: rgba(255, 255, 255, 0.95);
}

.mayhen_invitation_vinyl_disc {
    position: relative;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: radial-gradient(circle, #2C2825 0%, #1A1816 70%, #0D0C0B 100%);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.15), 0 2px 6px rgba(0, 0, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: transform 0.5s ease;
}

.mayhen_invitation_vinyl_groove {
    position: absolute;
    inset: 4px;
    border-radius: 50%;
    border: 1px dashed rgba(255, 255, 255, 0.12);
    pointer-events: none;
}

.mayhen_invitation_vinyl_groove.mayhen_groove_inner {
    inset: 8px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.mayhen_invitation_vinyl_center {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4AF37 0%, #AA771C 100%);
    border: 1.5px solid #1A1816;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mayhen_invitation_vinyl_dot {
    width: 3.5px;
    height: 3.5px;
    border-radius: 50%;
    background: #1A1816;
}

/* 33 RPM Vinyl Spin Animation */
@keyframes mayhenVinylSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.mayhen_invitation_music_dock.is-playing .mayhen_invitation_vinyl_disc {
    animation: mayhenVinylSpin 2.4s linear infinite;
}

.mayhen_invitation_music_dock.is-paused .mayhen_invitation_vinyl_disc {
    animation-play-state: paused;
    filter: grayscale(0.4);
    opacity: 0.85;
}

/* Sound Waves Bars */
.mayhen_invitation_sound_waves {
    display: flex;
    align-items: flex-end;
    gap: 2.5px;
    height: 14px;
    padding: 0 2px;
}

.mayhen_wave_bar {
    width: 2.5px;
    background: #C5A880;
    border-radius: 1px;
    transition: height 0.2s ease;
}

.bar_1 { height: 6px; }
.bar_2 { height: 12px; }
.bar_3 { height: 8px; }

@keyframes mayhenSoundPulse {
    0%, 100% { height: 4px; }
    50% { height: 14px; }
}

.mayhen_invitation_music_dock.is-playing .bar_1 {
    animation: mayhenSoundPulse 0.8s ease-in-out infinite alternate;
}
.mayhen_invitation_music_dock.is-playing .bar_2 {
    animation: mayhenSoundPulse 0.6s ease-in-out 0.2s infinite alternate;
}
.mayhen_invitation_music_dock.is-playing .bar_3 {
    animation: mayhenSoundPulse 0.7s ease-in-out 0.4s infinite alternate;
}

.mayhen_invitation_music_dock.is-paused .mayhen_wave_bar {
    height: 3px !important;
    animation: none !important;
    background: #9E9992;
}

/* Tooltip popup nhỏ gọn */
.mayhen_invitation_music_tooltip {
    max-width: 140px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-family: 'Inter', -apple-system, sans-serif;
    font-size: 11px;
    font-weight: 500;
    color: #4A4641;
    display: flex;
    align-items: center;
    gap: 4px;
}

.mayhen_tooltip_note {
    color: #9C7A4A;
    font-size: 12px;
}

.mayhen_tooltip_text {
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 640px) {
    .mayhen_invitation_music_dock {
        top: 14px;
        left: 14px;
        padding: 5px 8px 5px 5px;
    }
    .mayhen_invitation_music_tooltip {
        max-width: 90px;
        font-size: 10px;
    }
    .mayhen_invitation_vinyl_disc {
        width: 30px;
        height: 30px;
    }
}
</style>

<script>
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const dock = document.getElementById('mayhenMusicDock');
        const audio = document.getElementById('mayhenInvitationAudio');
        if (!dock || !audio) return;

        const targetVolume = {{ (float) ($musicConfig['volume'] ?? 0.7) }};
        const shouldAutoplay = {{ ($musicConfig['autoplay'] ?? true) ? 'true' : 'false' }};
        const fadeIn = {{ ($musicConfig['fade_in'] ?? true) ? 'true' : 'false' }};
        const fadeDuration = {{ (int) ($musicConfig['fade_duration'] ?? 2500) }};
        const pauseOnVideo = {{ ($musicConfig['pause_on_video'] ?? true) ? 'true' : 'false' }};

        let isPlaying = false;
        let fadeInterval = null;

        audio.volume = fadeIn ? 0 : targetVolume;

        function fadeInVolume() {
            if (!fadeIn) {
                audio.volume = targetVolume;
                return;
            }
            if (fadeInterval) clearInterval(fadeInterval);
            audio.volume = 0;
            const steps = 25;
            const stepTime = fadeDuration / steps;
            const stepVol = targetVolume / steps;

            fadeInterval = setInterval(function() {
                if (audio.volume + stepVol >= targetVolume) {
                    audio.volume = targetVolume;
                    clearInterval(fadeInterval);
                } else {
                    audio.volume = Math.min(targetVolume, audio.volume + stepVol);
                }
            }, stepTime);
        }

        function playMusic() {
            audio.play().then(function() {
                isPlaying = true;
                dock.classList.remove('is-paused');
                dock.classList.add('is-playing');
                fadeInVolume();
            }).catch(function(err) {
                isPlaying = false;
                dock.classList.remove('is-playing');
                dock.classList.add('is-paused');
            });
        }

        function pauseMusic() {
            if (fadeInterval) clearInterval(fadeInterval);
            audio.pause();
            isPlaying = false;
            dock.classList.remove('is-playing');
            dock.classList.add('is-paused');
        }

        dock.addEventListener('click', function(e) {
            e.stopPropagation();
            if (isPlaying) {
                pauseMusic();
            } else {
                playMusic();
            }
        });

        // Bắt sự kiện cử chỉ người dùng đầu tiên (User Gesture) để tự động phát nhạc vượt rào cản Autoplay Policy
        if (shouldAutoplay) {
            const unlockUserGesture = function() {
                if (!isPlaying) {
                    playMusic();
                }
                document.removeEventListener('click', unlockUserGesture);
                document.removeEventListener('touchstart', unlockUserGesture);
            };

            document.addEventListener('click', unlockUserGesture, { once: true, passive: true });
            document.addEventListener('touchstart', unlockUserGesture, { once: true, passive: true });

            // Bắt sự kiện bấm mở bì thư / curtain
            const openBtns = document.querySelectorAll('.mayhen_invitation_open_btn, .mayhen_curtain_btn, [data-action="open-invitation"]');
            openBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (!isPlaying) playMusic();
                });
            });
        }

        // Tự động tạm dừng nhạc nền khi có video HTML5 hoặc iframe phát
        if (pauseOnVideo) {
            const videos = document.querySelectorAll('video');
            videos.forEach(function(video) {
                video.addEventListener('play', function() {
                    if (isPlaying) pauseMusic();
                });
                video.addEventListener('ended', function() {
                    if (!isPlaying && shouldAutoplay) playMusic();
                });
            });
        }
    });
})();
</script>
@endif

