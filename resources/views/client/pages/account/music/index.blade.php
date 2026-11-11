@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quản Trị Âm Nhạc Hôn Lễ — Mây Hẹn';
@endphp

@section('account_breadcrumb', 'Âm Nhạc Hôn Lễ')

@section('account_content')
<div class="mayhen_music_container">
    {{-- Header & Headline --}}
    <div class="mayhen_music_header">
        <div class="mayhen_music_headline">
            <div class="mayhen_music_badge">Wedding Background Music Suite</div>
            <h1 class="mayhen_music_title">Giai Điệu Hôn Lễ Quiet Luxury</h1>
            <p class="mayhen_music_subtitle">
                Tuyển chọn những thanh âm mộc mạc, sâu lắng từ đàn dương cầm, cello và guitar acoustic giúp đánh thức cảm xúc thiêng liêng của khách mời khi chạm mở thiệp cưới của <strong>{{ $invitation->wedding->groom_name ?? $invitation->groom_name }} & {{ $invitation->wedding->bride_name ?? $invitation->bride_name }}</strong>.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_music_alert mayhen_music_alert--success">
            <span class="mayhen_music_alert_icon">✓</span>
            <div class="mayhen_music_alert_text">{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_music_alert mayhen_music_alert--danger">
            <span class="mayhen_music_alert_icon">!</span>
            <div class="mayhen_music_alert_text">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Layout 2 cột: Cột trái Cấu hình chung + Upload, Cột phải Thư viện 8 bản nhạc --}}
    <div class="mayhen_music_grid">
        {{-- Cột Trái: Cấu hình phát nhạc & Upload --}}
        <div class="mayhen_music_col_settings">
            {{-- Card Trạng Thái Hiện Tại --}}
            <div class="mayhen_music_card mayhen_music_current_card">
                <div class="mayhen_music_current_top">
                    <div class="mayhen_music_vinyl_preview {{ ($musicConfig['enabled'] ?? true) ? 'is-active' : '' }}">
                        <span class="mayhen_vinyl_groove_mini"></span>
                        <span class="mayhen_vinyl_dot_mini"></span>
                    </div>
                    <div class="mayhen_music_current_info">
                        <div class="mayhen_music_current_label">Đang áp dụng cho thiệp</div>
                        <h3 class="mayhen_music_current_name">{{ $musicConfig['title'] ?? 'Canon in D' }}</h3>
                        <p class="mayhen_music_current_artist">{{ $musicConfig['artist'] ?? 'Mây Hẹn Ensemble' }}</p>
                    </div>
                </div>
                <div class="mayhen_music_current_badge">
                    <span class="mayhen_badge_dot"></span>
                    <span>{{ ($musicConfig['enabled'] ?? true) ? 'Đang bật phát nhạc' : 'Đang tạm tắt nhạc' }}</span>
                </div>
            </div>

            {{-- Form Cấu Hình Phát Nhạc --}}
            <form action="{{ route('account.music.update', $invitation->id) }}" method="POST" class="mayhen_music_card">
                @csrf
                <input type="hidden" name="track_id" id="formTrackId" value="{{ $musicConfig['track_id'] }}">
                <input type="hidden" name="title" id="formTitle" value="{{ $musicConfig['title'] }}">
                <input type="hidden" name="artist" id="formArtist" value="{{ $musicConfig['artist'] }}">
                <input type="hidden" name="url" id="formUrl" value="{{ $musicConfig['url'] }}">
                <input type="hidden" name="is_custom_upload" id="formIsCustom" value="{{ ($musicConfig['is_custom_upload'] ?? false) ? '1' : '0' }}">

                <h3 class="mayhen_music_card_heading">Thiết Lập Âm Thanh</h3>

                <div class="mayhen_music_field_group">
                    <label class="mayhen_music_switch_label">
                        <input type="checkbox" name="enabled" value="1" {{ ($musicConfig['enabled'] ?? true) ? 'checked' : '' }} class="mayhen_music_checkbox">
                        <span class="mayhen_music_switch_custom"></span>
                        <div class="mayhen_music_switch_text">
                            <strong>Bật nhạc nền trên thiệp</strong>
                            <p>Tự động sẵn sàng phát khi khách mời mở thiệp.</p>
                        </div>
                    </label>
                </div>

                <div class="mayhen_music_field_group">
                    <label class="mayhen_music_label" for="musicVolume">
                        Âm lượng mặc định: <span id="volumeValueDisplay">{{ (int)(($musicConfig['volume'] ?? 0.7) * 100) }}%</span>
                    </label>
                    <input type="range" name="volume" id="musicVolume" min="0.1" max="1.0" step="0.05" value="{{ $musicConfig['volume'] ?? 0.7 }}" class="mayhen_music_range">
                    <p class="mayhen_music_help">Nên đặt từ 60% – 80% để vừa vặn, không gây bất ngờ khi khách mở thiệp.</p>
                </div>

                <div class="mayhen_music_field_group">
                    <label class="mayhen_music_switch_label">
                        <input type="checkbox" name="fade_in" value="1" {{ ($musicConfig['fade_in'] ?? true) ? 'checked' : '' }} class="mayhen_music_checkbox">
                        <span class="mayhen_music_switch_custom"></span>
                        <div class="mayhen_music_switch_text">
                            <strong>Hiệu ứng Fade-in êm dịu (2.5 giây)</strong>
                            <p>Tăng âm lượng từ từ khi bắt đầu phát, tạo cảm giác sang trọng.</p>
                        </div>
                    </label>
                </div>

                <div class="mayhen_music_field_group">
                    <label class="mayhen_music_switch_label">
                        <input type="checkbox" name="pause_on_video" value="1" {{ ($musicConfig['pause_on_video'] ?? true) ? 'checked' : '' }} class="mayhen_music_checkbox">
                        <span class="mayhen_music_switch_custom"></span>
                        <div class="mayhen_music_switch_text">
                            <strong>Tự dừng nhạc khi xem video</strong>
                            <p>Tạm ngắt nhạc nền khi khách bấm xem video pre-wedding để không bị lẫn tiếng.</p>
                        </div>
                    </label>
                </div>

                <div class="mayhen_music_field_group">
                    <label class="mayhen_music_switch_label">
                        <input type="checkbox" name="floating_dock" value="1" {{ ($musicConfig['floating_dock'] ?? true) ? 'checked' : '' }} class="mayhen_music_checkbox">
                        <span class="mayhen_music_switch_custom"></span>
                        <div class="mayhen_music_switch_text">
                            <strong>Hiển thị nút đĩa than nổi (Floating Vinyl)</strong>
                            <p>Biểu tượng đĩa than xoay tròn tinh xảo ở góc trái màn hình.</p>
                        </div>
                    </label>
                </div>

                <div class="mayhen_music_field_group">
                    <label class="mayhen_music_switch_label">
                        <input type="checkbox" name="loop" value="1" {{ ($musicConfig['loop'] ?? true) ? 'checked' : '' }} class="mayhen_music_checkbox">
                        <span class="mayhen_music_switch_custom"></span>
                        <div class="mayhen_music_switch_text">
                            <strong>Lặp lại liên tục (Loop)</strong>
                            <p>Tự động phát lại khi kết thúc bản nhạc.</p>
                        </div>
                    </label>
                </div>

                <div class="mayhen_music_actions">
                    <button type="submit" class="mayhen_music_btn mayhen_music_btn--primary">Lưu Cấu Hình Âm Nhạc</button>
                </div>
            </form>

            {{-- Form Upload Bài Hát Riêng --}}
            <form action="{{ route('account.music.upload', $invitation->id) }}" method="POST" enctype="multipart/form-data" class="mayhen_music_card mayhen_music_card--upload">
                @csrf
                <h3 class="mayhen_music_card_heading">Tải Lên Bản Nhạc Riêng Của Cặp Đôi</h3>
                <p class="mayhen_music_card_desc">Nếu hai bạn có bài hát kỷ niệm riêng, hãy tải tệp âm thanh (MP3, M4A, WAV, tối đa 15MB).</p>

                <div class="mayhen_music_input_group">
                    <label class="mayhen_music_label" for="customTitle">Tên bài hát:</label>
                    <input type="text" name="custom_title" id="customTitle" placeholder="Ví dụ: Ánh Nắng Của Anh (Acoustic)" class="mayhen_music_input" value="{{ old('custom_title') }}">
                </div>

                <div class="mayhen_music_input_group">
                    <label class="mayhen_music_label" for="customArtist">Tên ca sĩ / Trình bày:</label>
                    <input type="text" name="custom_artist" id="customArtist" placeholder="Ví dụ: {{ $invitation->groom_name }} & {{ $invitation->bride_name }}" class="mayhen_music_input" value="{{ old('custom_artist') }}">
                </div>

                <div class="mayhen_music_input_group">
                    <label class="mayhen_music_label" for="audioFile">Tệp âm thanh:</label>
                    <input type="file" name="audio_file" id="audioFile" accept="audio/*,.mp3,.m4a,.wav,.aac,.ogg" required class="mayhen_music_file_input">
                </div>

                <button type="submit" class="mayhen_music_btn mayhen_music_btn--secondary">Tải Lên & Kích Hoạt Ngay</button>
            </form>
        </div>

        {{-- Cột Phải: Thư Viện 8 Bản Nhạc Curated Quiet Luxury --}}
        <div class="mayhen_music_col_catalog">
            <div class="mayhen_music_catalog_header">
                <div>
                    <h2 class="mayhen_music_catalog_title">Thư Viện Tuyển Chọn Quiet Luxury (8 Bản Nhạc Độc Bản)</h2>
                    <p class="mayhen_music_catalog_subtitle">Được hòa âm tinh tế, không lời, bản quyền sử dụng trọn vẹn không lo vi phạm bản quyền mạng xã hội.</p>
                </div>
            </div>

            <div class="mayhen_music_tracks_list">
                @foreach($curatedTracks as $track)
                    @php
                        $isSelected = (($musicConfig['track_id'] ?? '') === $track['id']) && !($musicConfig['is_custom_upload'] ?? false);
                    @endphp
                    <div class="mayhen_music_track_item {{ $isSelected ? 'is-selected' : '' }}" data-track-id="{{ $track['id'] }}" data-track-title="{{ $track['title'] }}" data-track-artist="{{ $track['artist'] }}" data-track-url="{{ $track['preview_url'] }}">
                        <div class="mayhen_track_left">
                            <button type="button" class="mayhen_track_play_btn" aria-label="Nghe thử" data-audio-src="{{ $track['preview_url'] }}">
                                <span class="mayhen_play_icon">▶</span>
                                <span class="mayhen_pause_icon">❚❚</span>
                            </button>
                            <div class="mayhen_track_details">
                                <div class="mayhen_track_top_row">
                                    <span class="mayhen_track_tag">{{ $track['tag'] }}</span>
                                    <span class="mayhen_track_duration">{{ $track['duration'] }}</span>
                                </div>
                                <h4 class="mayhen_track_title">{{ $track['title'] }}</h4>
                                <div class="mayhen_track_meta">{{ $track['artist'] }} • {{ $track['genre'] }}</div>
                                <p class="mayhen_track_desc">{{ $track['description'] }}</p>
                            </div>
                        </div>
                        <div class="mayhen_track_right">
                            @if($isSelected)
                                <div class="mayhen_track_selected_badge">✓ Đang Chọn</div>
                            @else
                                <button type="button" class="mayhen_music_btn mayhen_music_btn--select" onclick="selectCuratedTrack('{{ $track['id'] }}', '{{ addslashes($track['title']) }}', '{{ addslashes($track['artist']) }}', '{{ $track['preview_url'] }}')">
                                    Chọn Bản Này
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Audio Player ẩn dùng để nghe thử trong trang quản trị --}}
<audio id="mayhenPreviewAudio" preload="none"></audio>

<style>
/* ==========================================================================
   MÂY HẸN WEDDING MUSIC SUITE STYLES (ĐỀ ÁN 129)
   100% Pure Vanilla CSS — BEM Prefix mayhen_music_*
   ========================================================================== */
.mayhen_music_container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem 4rem;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #1F1D1A;
}

.mayhen_music_breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #7D7871;
    margin-bottom: 1rem;
}
.mayhen_music_breadcrumbs a {
    color: #9C7A4A;
    text-decoration: none;
}
.mayhen_music_breadcrumbs a:hover {
    text-decoration: underline;
}

.mayhen_music_headline {
    margin-bottom: 2rem;
}
.mayhen_music_badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(197, 168, 128, 0.15);
    color: #9C7A4A;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}
.mayhen_music_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: #1F1D1A;
}
.mayhen_music_subtitle {
    font-size: 0.95rem;
    color: #635E56;
    line-height: 1.6;
    max-width: 780px;
    margin: 0;
}

.mayhen_music_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.mayhen_music_alert--success {
    background: #F4F7ED;
    border: 1px solid #C4D7AC;
    color: #375323;
}
.mayhen_music_alert--danger {
    background: #FDF3F2;
    border: 1px solid #F5C2BE;
    color: #8C2E24;
}
.mayhen_music_alert_icon {
    font-weight: bold;
    font-size: 1.1rem;
}

/* Grid Layout */
.mayhen_music_grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 992px) {
    .mayhen_music_grid {
        grid-template-columns: 1fr;
    }
}

/* Cards */
.mayhen_music_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.25);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 12px rgba(31, 29, 26, 0.03);
}

.mayhen_music_card_heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0 0 1rem;
    color: #1F1D1A;
    border-bottom: 1px solid rgba(197, 168, 128, 0.2);
    padding-bottom: 0.5rem;
}

.mayhen_music_card_desc {
    font-size: 0.85rem;
    color: #7D7871;
    line-height: 1.5;
    margin-top: -0.5rem;
    margin-bottom: 1rem;
}

/* Current Card */
.mayhen_music_current_card {
    background: linear-gradient(135deg, #FAF7F2 0%, #FFFFFF 100%);
    border: 1px solid #D4AF37;
}
.mayhen_music_current_top {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 1rem;
}
.mayhen_music_vinyl_preview {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: radial-gradient(circle, #2C2825 0%, #1A1816 70%, #0D0C0B 100%);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
}
.mayhen_vinyl_groove_mini {
    position: absolute;
    inset: 6px;
    border-radius: 50%;
    border: 1px dashed rgba(255, 255, 255, 0.15);
}
.mayhen_vinyl_dot_mini {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D4AF37, #AA771C);
    border: 2px solid #1A1816;
}
.mayhen_music_vinyl_preview.is-active {
    animation: mayhenVinylSpin 3s linear infinite;
}
.mayhen_music_current_label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9C7A4A;
    font-weight: 600;
}
.mayhen_music_current_name {
    font-size: 1.1rem;
    font-weight: 600;
    margin: 2px 0 2px;
    color: #1F1D1A;
}
.mayhen_music_current_artist {
    font-size: 0.85rem;
    color: #635E56;
    margin: 0;
}
.mayhen_music_current_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: #4A4641;
    background: rgba(197, 168, 128, 0.15);
    padding: 3px 10px;
    border-radius: 999px;
}
.mayhen_badge_dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #375323;
}

/* Switches & Inputs */
.mayhen_music_field_group {
    margin-bottom: 1.25rem;
}
.mayhen_music_switch_label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
}
.mayhen_music_checkbox {
    display: none;
}
.mayhen_music_switch_custom {
    width: 38px;
    height: 22px;
    background: #D9D5CF;
    border-radius: 11px;
    position: relative;
    flex-shrink: 0;
    transition: background 0.25s ease;
    margin-top: 2px;
}
.mayhen_music_switch_custom::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    transition: transform 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}
.mayhen_music_checkbox:checked + .mayhen_music_switch_custom {
    background: #9C7A4A;
}
.mayhen_music_checkbox:checked + .mayhen_music_switch_custom::after {
    transform: translateX(16px);
}
.mayhen_music_switch_text strong {
    display: block;
    font-size: 0.9rem;
    font-weight: 500;
    color: #1F1D1A;
}
.mayhen_music_switch_text p {
    font-size: 0.78rem;
    color: #7D7871;
    margin: 2px 0 0;
    line-height: 1.4;
}

.mayhen_music_label {
    display: block;
    font-size: 0.88rem;
    font-weight: 500;
    margin-bottom: 0.4rem;
    color: #1F1D1A;
}
.mayhen_music_help {
    font-size: 0.75rem;
    color: #7D7871;
    margin: 4px 0 0;
}
.mayhen_music_range {
    width: 100%;
    accent-color: #9C7A4A;
    cursor: pointer;
}
.mayhen_music_input_group {
    margin-bottom: 1rem;
}
.mayhen_music_input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #D9D5CF;
    border-radius: 6px;
    font-size: 0.88rem;
    box-sizing: border-box;
}
.mayhen_music_input:focus {
    outline: none;
    border-color: #9C7A4A;
}
.mayhen_music_file_input {
    width: 100%;
    font-size: 0.85rem;
    padding: 6px 0;
}

/* Buttons */
.mayhen_music_btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    font-size: 0.88rem;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    text-decoration: none;
}
.mayhen_music_btn--primary {
    width: 100%;
    background: #1F1D1A;
    color: #FFFFFF;
}
.mayhen_music_btn--primary:hover {
    background: #38342E;
}
.mayhen_music_btn--secondary {
    width: 100%;
    background: #FAF7F2;
    border: 1px solid #C5A880;
    color: #9C7A4A;
}
.mayhen_music_btn--secondary:hover {
    background: #F3ECE1;
}
.mayhen_music_btn--select {
    background: transparent;
    border: 1px solid #C5A880;
    color: #9C7A4A;
    padding: 6px 14px;
    font-size: 0.82rem;
}
.mayhen_music_btn--select:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}

/* Catalog Column */
.mayhen_music_catalog_header {
    margin-bottom: 1.25rem;
}
.mayhen_music_catalog_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.3rem;
}
.mayhen_music_catalog_subtitle {
    font-size: 0.88rem;
    color: #635E56;
    margin: 0;
}

.mayhen_music_tracks_list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.mayhen_music_track_item {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.25);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    transition: all 0.2s ease;
}
.mayhen_music_track_item:hover {
    border-color: #C5A880;
    box-shadow: 0 4px 16px rgba(31, 29, 26, 0.05);
}
.mayhen_music_track_item.is-selected {
    border: 1.5px solid #9C7A4A;
    background: #FDFBF8;
}
.mayhen_track_left {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    flex: 1;
}
.mayhen_track_play_btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #FAF7F2;
    border: 1px solid #C5A880;
    color: #9C7A4A;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}
.mayhen_track_play_btn:hover {
    background: #9C7A4A;
    color: #FFFFFF;
    transform: scale(1.06);
}
.mayhen_track_play_btn .mayhen_pause_icon {
    display: none;
    font-size: 0.75rem;
}
.mayhen_track_play_btn.is-playing .mayhen_play_icon {
    display: none;
}
.mayhen_track_play_btn.is-playing .mayhen_pause_icon {
    display: inline;
}
.mayhen_track_top_row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
}
.mayhen_track_tag {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 2px 6px;
    background: rgba(197, 168, 128, 0.15);
    color: #9C7A4A;
    border-radius: 4px;
    font-weight: 600;
}
.mayhen_track_duration {
    font-size: 0.75rem;
    color: #9E9992;
}
.mayhen_track_title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 2px;
    color: #1F1D1A;
}
.mayhen_track_meta {
    font-size: 0.8rem;
    color: #635E56;
    margin-bottom: 4px;
}
.mayhen_track_desc {
    font-size: 0.8rem;
    color: #7D7871;
    margin: 0;
    line-height: 1.4;
}
.mayhen_track_selected_badge {
    font-size: 0.82rem;
    font-weight: 600;
    color: #375323;
    background: #F4F7ED;
    padding: 6px 14px;
    border-radius: 999px;
    border: 1px solid #C4D7AC;
    white-space: nowrap;
}
</style>

<script>
let currentPlayingBtn = null;
const previewAudio = document.getElementById('mayhenPreviewAudio');

// Nghe thử bài hát
document.querySelectorAll('.mayhen_track_play_btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const src = this.getAttribute('data-audio-src');
        if (currentPlayingBtn === this) {
            if (previewAudio.paused) {
                previewAudio.play();
                this.classList.add('is-playing');
            } else {
                previewAudio.pause();
                this.classList.remove('is-playing');
            }
        } else {
            if (currentPlayingBtn) {
                currentPlayingBtn.classList.remove('is-playing');
            }
            previewAudio.src = src;
            previewAudio.play().then(() => {
                this.classList.add('is-playing');
                currentPlayingBtn = this;
            }).catch(e => {
                console.log('Playback error:', e);
            });
        }
    });
});

previewAudio.addEventListener('ended', function() {
    if (currentPlayingBtn) {
        currentPlayingBtn.classList.remove('is-playing');
        currentPlayingBtn = null;
    }
});

// Điều chỉnh thanh trượt volume
const volumeInput = document.getElementById('musicVolume');
const volumeDisplay = document.getElementById('volumeValueDisplay');
if (volumeInput && volumeDisplay) {
    volumeInput.addEventListener('input', function() {
        volumeDisplay.textContent = Math.round(this.value * 100) + '%';
    });
}

// Chọn bài hát từ danh sách Curated
window.selectCuratedTrack = function(id, title, artist, url) {
    document.getElementById('formTrackId').value = id;
    document.getElementById('formTitle').value = title;
    document.getElementById('formArtist').value = artist;
    document.getElementById('formUrl').value = url;
    document.getElementById('formIsCustom').value = '0';

    // Submit form tự động
    document.querySelector('.mayhen_music_col_settings form').submit();
};
</script>
@endsection
