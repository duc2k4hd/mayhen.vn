@extends('client.layouts.master')

@php
    $groomName = $wedding->groom_name ?? 'Chú Rể';
    $brideName = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = 'Trợ Lý Đón Tiếp VIP: ' . $guest->name . ' | Hôn Lễ ' . $groomName . ' & ' . $brideName . ' | ' . config('app.name');
    $venueName = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? 'Trung Tâm Tiệc Cưới'))));
    $venueAddress = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
    $mapUrl = trim((string) ($extra['event_party_map_url'] ?? ($extra['google_map_url'] ?? '')));
    $partyTime = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '18:00')));
    $partyDateFormatted = $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d/m/Y') : 'Ngày cưới trọng đại';
    $lunarDate = trim((string) ($extra['event_party_lunar'] ?? ''));

    // Thông tin mừng cưới Nhà Trai & Nhà Gái
    $groomBankName = trim((string) ($extra['groom_bank_name'] ?? ''));
    $groomBankAcc = trim((string) ($extra['groom_bank_account'] ?? ''));
    $groomBankHolder = trim((string) ($extra['groom_bank_holder'] ?? ($extra['groom_account_name'] ?? $groomName)));

    $brideBankName = trim((string) ($extra['bride_bank_name'] ?? ''));
    $brideBankAcc = trim((string) ($extra['bride_bank_account'] ?? ''));
    $brideBankHolder = trim((string) ($extra['bride_bank_holder'] ?? ($extra['bride_account_name'] ?? $brideName)));

    $hasBanking = ($groomBankAcc !== '' && $groomBankName !== '') || ($brideBankAcc !== '' && $brideBankName !== '');
@endphp

@section('title', $pageTitle)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/checkin.css') }}">
@endpush

@section('content')
<div class="mayhen_concierge_page_wrapper">
    <div class="mayhen_concierge_container">
        
        {{-- Top Brand Header --}}
        <header class="mayhen_concierge_top_brand">
            <div class="mayhen_concierge_emblem" aria-hidden="true">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"></path>
                </svg>
            </div>
            <span class="mayhen_concierge_service_badge">MÂY HẸN WEDDING VIP PASS • GUEST CONCIERGE</span>
            <h1 class="mayhen_concierge_couple_heading">{{ $groomName }} & {{ $brideName }}</h1>
            <p class="mayhen_concierge_sub_date">{{ $partyDateFormatted }}</p>
        </header>

        {{-- 1. THẺ ĐÓN TIẾP VIP & BÀN TIỆC (VIP Pass Card) --}}
        <section class="mayhen_concierge_card mayhen_concierge_card--hero">
            <div class="mayhen_concierge_guest_intro">
                <span class="mayhen_concierge_salutation">{{ $guest->salutation ?: 'Trân trọng kính mời' }}</span>
                <h2 class="mayhen_concierge_guest_name">{{ $guest->name }}</h2>
                <div class="mayhen_concierge_badges_row">
                    <span class="mayhen_concierge_tag">{{ $guest->group_name ?: 'Khách Quý' }}</span>
                    <span class="mayhen_concierge_tag">{{ $guest->expected_attendees }} Suất Mời</span>
                </div>
            </div>

            {{-- Khung bàn tiệc nổi bật --}}
            <div class="mayhen_concierge_seating_box">
                <span class="mayhen_concierge_box_label">VỊ TRÍ BÀN TIỆC DÀNH CHO BẠN</span>
                @if(!empty($guest->table_name))
                    <div class="mayhen_concierge_table_highlight">{{ $guest->table_name }}</div>
                @else
                    <div class="mayhen_concierge_table_pending">
                        Ban lễ tân sẽ hướng dẫn vị trí bàn khi quý khách tới sảnh tiệc.
                    </div>
                @endif
            </div>

            {{-- Mã QR Điểm Danh Cá Nhân --}}
            <div class="mayhen_concierge_qr_wrapper">
                <div class="mayhen_concierge_qr_frame">
                    {!! \App\Services\QrCodeService::svg(route('checkin.direct', ['token' => $guest->guest_token]), 180, '#1F1D1A', '#FAF8F5') !!}
                </div>
                <p class="mayhen_concierge_qr_tip">
                    Xuất trình mã QR này tại bàn đón tiếp để tiếp tân điểm danh và hướng dẫn vị trí bàn tiệc nhanh nhất.
                </p>
                @if($guest->checked_in_at)
                    <div class="mayhen_concierge_status_badge mayhen_concierge_status_badge--checked">
                        ✓ Đã đón tiếp lúc {{ $guest->checked_in_at->format('H:i d/m/Y') }}
                    </div>
                @endif
            </div>
        </section>

        {{-- 2. LỜI NHẮN CÁ NHÂN HÓA (Persona Message) --}}
        @if($personaMessage)
            <section class="mayhen_concierge_card mayhen_concierge_card--persona">
                <div class="mayhen_concierge_persona_icon" aria-hidden="true">💌</div>
                <h3 class="mayhen_concierge_persona_title">{{ $personaMessage['title'] ?? 'Lời Ngỏ Trân Quý' }}</h3>
                <p class="mayhen_concierge_persona_body">{{ $personaMessage['content'] ?? ($personaMessage['message'] ?? '') }}</p>
            </section>
        @endif

        {{-- 3. THỜI GIAN & LƯU VÀO LỊCH (Schedule & Add to Calendar) --}}
        <section class="mayhen_concierge_card">
            <h3 class="mayhen_concierge_section_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Thời Gian & Lịch Hẹn Hôn Lễ</span>
            </h3>

            <div class="mayhen_concierge_timeline_grid">
                <div class="mayhen_concierge_timeline_item">
                    <span class="mayhen_concierge_timeline_label">Ngày Hôn Lễ</span>
                    <strong class="mayhen_concierge_timeline_val">{{ $partyDateFormatted }}</strong>
                    @if($lunarDate !== '')
                        <span class="mayhen_concierge_timeline_sub">({{ $lunarDate }})</span>
                    @endif
                </div>
                <div class="mayhen_concierge_timeline_item">
                    <span class="mayhen_concierge_timeline_label">Giờ Khai Tiệc</span>
                    <strong class="mayhen_concierge_timeline_val" style="color: #9C7A4A;">{{ $partyTime }}</strong>
                    <span class="mayhen_concierge_timeline_sub">Đón tiếp quan khách</span>
                </div>
            </div>

            <div class="mayhen_concierge_calendar_actions">
                <a href="{{ $googleCalendarUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_checkin_btn mayhen_checkin_btn--secondary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Thêm Vào Google Calendar</span>
                </a>
                <a href="{{ route('guest.concierge.ics', ['guestToken' => $guest->guest_token]) }}" class="mayhen_checkin_btn mayhen_checkin_btn--outline">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Tải Lịch Apple Calendar (.ics)</span>
                </a>
            </div>
        </section>

        {{-- 4. ĐỊA ĐIỂM & CHỈ DẪN BÃI ĐỖ XE (Location & Parking Concierge) --}}
        <section class="mayhen_concierge_card">
            <h3 class="mayhen_concierge_section_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span>Địa Điểm & Chỉ Dẫn Sảnh Tiệc</span>
            </h3>

            <div class="mayhen_concierge_venue_info">
                <h4 class="mayhen_concierge_venue_name">{{ $venueName }}</h4>
                @if($venueAddress !== '')
                    <p class="mayhen_concierge_venue_addr">{{ $venueAddress }}</p>
                @endif

                @if($mapUrl !== '')
                    <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_checkin_btn mayhen_checkin_btn--primary" style="margin-top: 12px; display: inline-flex;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                        </svg>
                        <span>Mở Google Maps Chỉ Đường</span>
                    </a>
                @endif
            </div>

            {{-- Thông tin Bãi đỗ xe thông minh (Parking Concierge) --}}
            @if(!empty($parkingGuide['car_parking']) || !empty($parkingGuide['moto_parking']) || !empty($parkingGuide['elevator_guide']))
                <div class="mayhen_concierge_parking_box">
                    <h5 class="mayhen_concierge_parking_title">🚗 Chỉ Dẫn Bãi Đỗ Xe & Lối Vào:</h5>
                    <ul class="mayhen_concierge_parking_list">
                        @if(!empty($parkingGuide['car_parking']))
                            <li><strong>Ô tô:</strong> {{ $parkingGuide['car_parking'] }}</li>
                        @endif
                        @if(!empty($parkingGuide['moto_parking']))
                            <li><strong>Xe máy:</strong> {{ $parkingGuide['moto_parking'] }}</li>
                        @endif
                        @if(!empty($parkingGuide['parking_fee']))
                            <li><strong>Chi phí/Vé:</strong> {{ $parkingGuide['parking_fee'] }}</li>
                        @endif
                        @if(!empty($parkingGuide['elevator_guide']))
                            <li><strong>Thang máy/Lối lên sảnh:</strong> {{ $parkingGuide['elevator_guide'] }}</li>
                        @endif
                        @if(!empty($parkingGuide['navigation_tips']))
                            <li><strong>Lưu ý:</strong> {{ $parkingGuide['navigation_tips'] }}</li>
                        @endif
                    </ul>
                </div>
            @endif
        </section>

        {{-- 5. XÁC NHẬN THAM DỰ NHANH (Fast RSVP) --}}
        <section class="mayhen_concierge_card">
            <h3 class="mayhen_concierge_section_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>Xác Nhận Tham Dự (RSVP)</span>
            </h3>

            @if(session('rsvp_success'))
                <div class="mayhen_concierge_alert mayhen_concierge_alert--success">
                    ✓ Cảm ơn {{ $guest->name }}! Lời phản hồi và chúc phúc của bạn đã được gửi tới đôi uyên ương.
                </div>
            @endif

            @if(session('rsvp_error'))
                <div class="mayhen_concierge_alert mayhen_concierge_alert--danger">
                    ⚠️ {{ session('rsvp_error') }}
                </div>
            @endif

            <form action="{{ route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]) }}" method="POST" class="mayhen_concierge_rsvp_form">
                @csrf
                <div class="mayhen_concierge_radio_group">
                    <label class="mayhen_concierge_radio_label">
                        <input type="radio" name="attending_status" value="attending" {{ ($existingResponse && $existingResponse->attending_status === 'attending') || ! $existingResponse ? 'checked' : '' }}>
                        <span class="radio_btn_text">🎉 Chắc Chắn Tham Dự</span>
                    </label>
                    <label class="mayhen_concierge_radio_label">
                        <input type="radio" name="attending_status" value="uncertain" {{ $existingResponse && ($existingResponse->attending_status === 'uncertain' || $existingResponse->attending_status === 'tentative') ? 'checked' : '' }}>
                        <span class="radio_btn_text">⏳ Chưa Chắc Chắn</span>
                    </label>
                    <label class="mayhen_concierge_radio_label">
                        <input type="radio" name="attending_status" value="declined" {{ $existingResponse && $existingResponse->attending_status === 'declined' ? 'checked' : '' }}>
                        <span class="radio_btn_text">💌 Rất Tiếc Không Thể Đến</span>
                    </label>
                </div>

                <div class="mayhen_concierge_form_row">
                    <label for="concierge_attendees" class="mayhen_concierge_input_label">Số người tham dự (người lớn):</label>
                    <input type="number" id="concierge_attendees" name="adults_count" min="1" max="10" value="{{ $existingResponse ? $existingResponse->adults_count : ($guest->expected_attendees ?: 1) }}" class="mayhen_concierge_input">
                </div>

                <div class="mayhen_concierge_form_row">
                    <label for="concierge_wishes" class="mayhen_concierge_input_label">Lời chúc phúc gửi tới đôi uyên ương:</label>
                    <textarea id="concierge_wishes" name="wishes" rows="3" placeholder="Gửi những lời chúc tốt đẹp nhất tới cô dâu & chú rể..." class="mayhen_concierge_textarea">{{ $existingResponse ? $existingResponse->wishes : '' }}</textarea>
                </div>

                <input type="hidden" name="guest_name" value="{{ $guest->name }}">
                <input type="hidden" name="phone" value="{{ $guest->phone ?? '' }}">

                <button type="submit" class="mayhen_checkin_btn mayhen_checkin_btn--primary mayhen_checkin_btn--full">
                    Gửi Xác Nhận Tham Dự
                </button>
            </form>
        </section>

        {{-- 6. MỪNG CƯỚI PHƯƠNG XA (VietQR Gift Box) --}}
        @if($hasBanking)
            <section class="mayhen_concierge_card">
                <h3 class="mayhen_concierge_section_title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <span>Hộp Mừng Cưới & Chúc Phúc</span>
                </h3>
                <p style="font-size: 13px; color: #7A726A; margin: 0 0 16px 0;">
                    Dành cho quan khách muốn gửi lời chúc và món quà mừng từ phương xa qua VietQR:
                </p>

                <div class="mayhen_concierge_banking_grid">
                    @if($groomBankAcc !== '' && $groomBankName !== '')
                        <div class="mayhen_concierge_bank_col">
                            <span class="mayhen_concierge_bank_tag">Nhà Trai (Chú Rể)</span>
                            <div class="mayhen_concierge_bank_qr">
                                <img src="https://img.vietqr.io/image/{{ strtolower($groomBankName) }}-{{ preg_replace('/\s+/', '', $groomBankAcc) }}-compact2.png?amount=0&addInfo={{ urlencode(Str::slug($guest->name, ' ') . ' mung cuoi ' . Str::slug($groomName, ' ')) }}" alt="VietQR Nhà Trai" class="mayhen_concierge_qr_img" loading="lazy">
                            </div>
                            <div class="mayhen_concierge_bank_details">
                                <div><strong>{{ $groomBankName }}</strong></div>
                                <div>STK: <code>{{ $groomBankAcc }}</code></div>
                                <div>Chủ TK: {{ $groomBankHolder }}</div>
                                <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--outline" style="padding: 4px 10px; font-size: 11px; margin-top: 6px;" onclick="navigator.clipboard.writeText('{{ $groomBankAcc }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Sao chép STK', 2000);">
                                    Sao chép STK
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($brideBankAcc !== '' && $brideBankName !== '')
                        <div class="mayhen_concierge_bank_col">
                            <span class="mayhen_concierge_bank_tag">Nhà Gái (Cô Dâu)</span>
                            <div class="mayhen_concierge_bank_qr">
                                <img src="https://img.vietqr.io/image/{{ strtolower($brideBankName) }}-{{ preg_replace('/\s+/', '', $brideBankAcc) }}-compact2.png?amount=0&addInfo={{ urlencode(Str::slug($guest->name, ' ') . ' mung cuoi ' . Str::slug($brideName, ' ')) }}" alt="VietQR Nhà Gái" class="mayhen_concierge_qr_img" loading="lazy">
                            </div>
                            <div class="mayhen_concierge_bank_details">
                                <div><strong>{{ $brideBankName }}</strong></div>
                                <div>STK: <code>{{ $brideBankAcc }}</code></div>
                                <div>Chủ TK: {{ $brideBankHolder }}</div>
                                <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--outline" style="padding: 4px 10px; font-size: 11px; margin-top: 6px;" onclick="navigator.clipboard.writeText('{{ $brideBankAcc }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Sao chép STK', 2000);">
                                    Sao chép STK
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- 7. NÚT MỞ THIỆP CƯỚI NGHỆ THUẬT (Full Invitation) --}}
        <div class="mayhen_concierge_footer_actions">
            <a href="{{ route('invitation.show', ['slug' => $invitation->slug, 'token' => $guest->guest_token]) }}" class="mayhen_checkin_btn mayhen_checkin_btn--primary mayhen_checkin_btn--full" style="font-size: 15px; padding: 14px;">
                💌 Mở Thiệp Cưới Nghệ Thuật (Xem Toàn Bộ Ảnh & Âm Nhạc)
            </a>
            <button type="button" class="mayhen_checkin_btn mayhen_checkin_btn--text" onclick="window.print()" style="margin-top: 8px;">
                🖨️ Lưu hoặc In Thẻ Khách VIP A4
            </button>
        </div>

    </div>
</div>
@endsection
