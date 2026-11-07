@extends('client.layouts.master')

@section('title', 'Gửi Ảnh Chúc Mừng: ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/photo-drop.css') }}">
@endpush

@section('content')
<div class="mayhen_drop_wrapper" id="mayhen_drop_app" data-upload-url="{{ route('photo_drop.upload', $invitation->slug) }}" data-slug="{{ $invitation->slug }}">
    <div class="mayhen_drop_container">
        {{-- Hero Header --}}
        <header class="mayhen_drop_header">
            <span class="mayhen_drop_badge">LIVE GUEST PHOTO DROP</span>
            <h1 class="mayhen_drop_title">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
            <p class="mayhen_drop_subtitle">
                Chia sẻ những bức ảnh tự nhiên, chân thật và rạng rỡ nhất bạn đã chụp cùng đôi uyên ương trong ngày hạnh phúc hôm nay.
            </p>
        </header>

        @if($isClosed)
            <div class="mayhen_drop_closed_card">
                <div class="closed_icon">🔒</div>
                <h2 class="closed_title">Hộp Nhận Ảnh Đã Tạm Đóng</h2>
                <p class="closed_desc">
                    Cô dâu và chú rể đã tạm dừng nhận thêm ảnh mới. Bạn vẫn có thể ngắm nhìn những khoảnh khắc tuyệt vời phía dưới nhé!
                </p>
            </div>
        @else
            {{-- Upload Interactive Box --}}
            <div class="mayhen_drop_upload_card">
                <form id="form_photo_drop" enctype="multipart/form-data">
                    @csrf
                    @if(isset($guest) && $guest)
                        <input type="hidden" name="guest_token" value="{{ $guest->guest_token }}">
                    @endif

                    {{-- Dropzone Area --}}
                    <div class="mayhen_dropzone" id="mayhen_dropzone">
                        <input type="file" id="input_drop_files" name="photos[]" multiple accept="image/jpeg,image/png,image/webp,image/heic,image/heif" class="dropzone_input">
                        
                        <div class="dropzone_prompt" id="dropzone_prompt">
                            <div class="dropzone_icon">
                                <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.4">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <strong class="dropzone_title">Chạm để chụp hoặc chọn ảnh từ máy</strong>
                            <p class="dropzone_hint">Hỗ trợ JPG, PNG, WebP, HEIC &bull; Tối đa 10 ảnh / lần</p>
                        </div>

                        {{-- Previews Grid --}}
                        <div class="dropzone_previews" id="dropzone_previews" style="display: none;"></div>
                    </div>

                    {{-- Form Inputs --}}
                    <div class="mayhen_drop_fields">
                        <div class="form_field">
                            <label class="form_label" for="uploader_name">Tên của bạn *</label>
                            <input 
                                type="text" 
                                name="uploader_name" 
                                id="uploader_name" 
                                class="form_input" 
                                required 
                                maxlength="150"
                                placeholder="Ví dụ: Bạn Linh Đan (Bàn 08), Anh Tuấn..."
                                value="{{ isset($guest) && $guest ? ($guest->salutation ? $guest->salutation . ' ' : '') . $guest->name : '' }}"
                            >
                        </div>

                        <div class="form_field">
                            <label class="form_label" for="caption">Lời nhắn kèm theo ảnh (tùy chọn)</label>
                            <input 
                                type="text" 
                                name="caption" 
                                id="caption" 
                                class="form_input" 
                                maxlength="255"
                                placeholder="Ví dụ: Chúc hai bạn mãi ngọt ngào như lúc này nhé!"
                            >
                        </div>
                    </div>

                    {{-- Upload Progress Track --}}
                    <div class="mayhen_drop_progress" id="upload_progress_wrap" style="display: none;">
                        <div class="progress_bar_wrap">
                            <div class="progress_bar_fill" id="upload_progress_fill" style="width: 0%;"></div>
                        </div>
                        <span class="progress_label" id="upload_progress_label">Đang tải ảnh lên... 0%</span>
                    </div>

                    {{-- Submit Action --}}
                    <div class="mayhen_drop_action_wrap">
                        <button type="submit" class="mayhen_drop_btn mayhen_drop_btn--primary" id="btn_submit_drop">
                            ✨ Gửi Ảnh Tặng Đôi Uyên Ương
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- Community Moments Stream --}}
        @if($recentPhotos->isNotEmpty())
            <section class="mayhen_drop_stream_section">
                <div class="stream_header">
                    <div>
                        <span class="stream_badge">KHOẢNH KHẮC MỚI NHẤT</span>
                        <h2 class="stream_title">Ảnh Do Quan Khách Vừa Gửi</h2>
                    </div>
                    <span class="stream_counter">
                        Đã có <strong>{{ $totalPhotosCount }}</strong> bức ảnh
                    </span>
                </div>

                <div class="mayhen_drop_stream_grid" id="stream_grid">
                    @foreach($recentPhotos as $photo)
                        <div class="stream_photo_card">
                            <img src="{{ $photo->photo_url }}" alt="Khoảnh khắc từ {{ $photo->uploader_name }}" class="stream_img" loading="lazy">
                            <div class="stream_overlay">
                                <span class="stream_uploader">{{ $photo->uploader_name }}</span>
                                @if($photo->caption)
                                    <p class="stream_caption">"{{ $photo->caption }}"</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Navigation Footer --}}
        <footer class="mayhen_drop_footer">
            <a href="{{ route('invitation.show', $invitation->slug) }}" class="mayhen_drop_back_link">
                ← Mở Thiệp Cưới Điện Tử
            </a>
            <p class="mayhen_drop_brand">
                Mây Hẹn (mayhen.vn) &bull; Live Guest Album
            </p>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/photo-drop.js') }}"></script>
@endpush

