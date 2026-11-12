@extends('client.pages.account.layout')

@section('page_title', 'Màn Hình Chào Mừng Lễ Tân & Kiosk Sảnh Cưới | ' . config('app.name'))
@section('account_breadcrumb', 'Màn Hình Sảnh Cưới')

@section('account_content')
<div class="mayhen_welcome_kiosk_studio">
    {{-- Topbar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Màn Hình Chào Mừng Lễ Tân (Venue Welcome Kiosk)
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Tablet / iPad đặt tại bàn đón tiếp xoay về phía quan khách • Tự động chào mừng đích danh và báo số bàn tiệc khi khách vừa check-in.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('welcome_kiosk.show', $invitation->slug) }}" target="_blank" 
               class="mayhen_account_btn" 
               style="background: #1F1D1A; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                <span>Mở Màn Hình Kiosk &rarr;</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; align-items: start;">
        {{-- Form Cấu Hình --}}
        <form action="{{ route('account.welcome_kiosk.update', $invitation->id) }}" method="POST">
            @csrf

            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 22px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #EAE3D9;">
                    <div>
                        <strong style="font-size: 15px; color: #1F1D1A; display: block;">Kích Hoạt Màn Hình Kiosk Chào Mừng</strong>
                        <span style="font-size: 12.5px; color: #8C827A;">Bật/tắt hiển thị màn hình đón tiếp sảnh cưới</span>
                    </div>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="enabled" value="1" {{ $config['enabled'] ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                        <span style="position: absolute; cursor: pointer; inset: 0; background: {{ $config['enabled'] ? '#0E9F6E' : '#D1D5DB' }}; transition: .2s; border-radius: 24px;"></span>
                        <span style="position: absolute; content: ''; height: 18px; width: 18px; left: {{ $config['enabled'] ? '23px' : '3px' }}; bottom: 3px; background: white; transition: .2s; border-radius: 50%;"></span>
                    </label>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                            Tiêu đề chào đón sảnh cưới:
                        </label>
                        <input type="text" name="kiosk_title" value="{{ $config['kiosk_title'] }}" 
                               style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                                Thời gian hiện lời chào (giây):
                            </label>
                            <select name="welcome_duration_seconds" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; background: white;">
                                <option value="5" {{ $config['welcome_duration_seconds'] == 5 ? 'selected' : '' }}>5 giây (Nhanh)</option>
                                <option value="8" {{ $config['welcome_duration_seconds'] == 8 ? 'selected' : '' }}>8 giây (Khuyên dùng)</option>
                                <option value="10" {{ $config['welcome_duration_seconds'] == 10 ? 'selected' : '' }}>10 giây</option>
                                <option value="15" {{ $config['welcome_duration_seconds'] == 15 ? 'selected' : '' }}>15 giây</option>
                            </select>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                                Mã PIN cài đặt thiết bị:
                            </label>
                            <input type="text" name="access_pin" value="{{ $config['access_pin'] }}" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; font-family: monospace; font-weight: bold;">
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; background: #FAF8F5; padding: 12px 14px; border-radius: 10px; border: 1px solid #EAE3D9;">
                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="sound_enabled" value="1" {{ $config['sound_enabled'] ? 'checked' : '' }}>
                            <span style="color: #1F1D1A; font-weight: 500;">Phát tiếng chuông pha lê du dương khi khách check-in (Web Audio)</span>
                        </label>

                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="show_table_name" value="1" {{ $config['show_table_name'] ? 'checked' : '' }}>
                            <span style="color: #1F1D1A; font-weight: 500;">Hiển thị vị trí bàn tiệc nổi bật trên màn hình</span>
                        </label>

                        <label style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="show_countdown" value="1" {{ $config['show_countdown'] ? 'checked' : '' }}>
                            <span style="color: #1F1D1A; font-weight: 500;">Hiển thị đồng hồ đếm ngược giờ cử hành hôn lễ</span>
                        </label>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                            Lời tri ân hiển thị dưới tên khách mời:
                        </label>
                        <textarea name="welcome_quote" rows="2" style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">{{ $config['welcome_quote'] }}</textarea>
                    </div>
                </div>

                <div style="margin-top: 20px; text-align: right;">
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary" style="padding: 10px 24px; font-size: 13.5px; font-weight: 600;">
                        Lưu Cấu Hình Màn Hình
                    </button>
                </div>
            </div>
        </form>

        {{-- Live Preview Thu Nhỏ & Diễn Tập Thử Nghiệm --}}
        <div>
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <strong style="font-family: 'Cormorant Garamond', serif; font-size: 18px; color: #1F1D1A;">
                        Khung Mô Phỏng Tablet Sảnh Cưới
                    </strong>
                    <button type="button" onclick="simulateCheckIn()" style="background: #9C7A4A; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: 500;">
                        🔔 Bắn Check-in Giả Lập
                    </button>
                </div>

                {{-- Khung Tablet Preview --}}
                <div style="background: #1F1D1A; border-radius: 14px; padding: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); position: relative; overflow: hidden; min-height: 280px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                    <div id="preview_ambient" style="color: #FFFFFF; padding: 20px; transition: opacity 0.3s;">
                        <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #C5A880; font-weight: 600;">Chào Mừng Quan Khách</span>
                        <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; margin: 6px 0; color: #FFFFFF;">
                            {{ $initData['groom_name'] }} &amp; {{ $initData['bride_name'] }}
                        </h4>
                        <p style="font-size: 12px; color: #D8CEBD; margin: 0 0 10px 0;">
                            {{ $initData['venue_name'] }} • {{ $initData['wedding_date_formatted'] }}
                        </p>
                        <div style="display: inline-block; background: rgba(197, 168, 128, 0.15); border: 1px solid rgba(197, 168, 128, 0.3); padding: 4px 12px; border-radius: 20px; font-size: 11px; color: #C5A880;">
                            ✨ Đang trong chế độ chờ quan khách...
                        </div>
                    </div>

                    {{-- Thẻ Popup Chào Mừng Demo --}}
                    <div id="preview_popup" style="display: none; position: absolute; inset: 12px; background: rgba(31, 29, 26, 0.95); border: 1.5px solid #C5A880; border-radius: 10px; padding: 16px; color: white; flex-direction: column; justify-content: center; align-items: center; animation: pulseGlow 1.5s infinite alternate;">
                        <span style="font-size: 11px; color: #C5A880; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">✨ Nhiệt Liệt Chào Đón ✨</span>
                        <h3 id="preview_guest_name" style="font-family: 'Cormorant Garamond', serif; font-size: 26px; color: #FFFFFF; margin: 4px 0 6px 0; font-weight: 700;">
                            Bác Nguyễn Văn Nam
                        </h3>
                        <div id="preview_table_badge" style="background: #9C7A4A; color: white; font-weight: 700; font-size: 12px; padding: 4px 12px; border-radius: 20px; margin-bottom: 8px;">
                            BÀN SỐ 08 (VIP)
                        </div>
                        <p style="font-size: 11.5px; color: #D8CEBD; margin: 0; line-height: 1.3;">
                            "Sự hiện diện của Quý Khách là niềm vinh hạnh lớn lao của gia đình chúng em!"
                        </p>
                    </div>
                </div>

                <div style="font-size: 12px; color: #8C827A; margin-top: 12px; text-align: center;">
                    💡 Khi tiếp tân quét QR đón khách, màn hình sảnh tiệc sẽ lập tức chuyển sang thẻ chào mừng VIP.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function simulateCheckIn() {
        const url = "{{ route('account.welcome_kiosk.simulate', $invitation->id) }}";

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const guest = data.guest;
                document.getElementById('preview_guest_name').textContent = guest.salutation + ' ' + guest.name;
                document.getElementById('preview_table_badge').textContent = 'VỊ TRÍ: ' + guest.table_name;

                // Ẩn ambient, hiện popup
                document.getElementById('preview_ambient').style.display = 'none';
                const popup = document.getElementById('preview_popup');
                popup.style.display = 'flex';

                // Phát chuông mô phỏng Web Audio API
                playCrystalChime();

                // Tự động tắt sau 6 giây
                setTimeout(() => {
                    popup.style.display = 'none';
                    document.getElementById('preview_ambient').style.display = 'block';
                }, 6000);
            }
        })
        .catch(err => alert('Không thể kết nối máy chủ.'));
    }

    // Bộ phát chuông pha lê thuần Web Audio API (Zero External MP3)
    function playCrystalChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            
            const freqs = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6 (Hợp âm Trưởng pha lê)
            freqs.forEach((freq, idx) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                
                osc.type = 'sine';
                osc.frequency.setValueAtTime(freq, now + idx * 0.12);
                
                gain.gain.setValueAtTime(0.001, now + idx * 0.12);
                gain.gain.exponentialRampToValueAtTime(0.2, now + idx * 0.12 + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + idx * 0.12 + 1.8);
                
                osc.connect(gain);
                gain.connect(ctx.destination);
                
                osc.start(now + idx * 0.12);
                osc.stop(now + idx * 0.12 + 2.0);
            });
        } catch(e) {
            console.log('Web Audio disabled or not permitted');
        }
    }
</script>
@endsection

