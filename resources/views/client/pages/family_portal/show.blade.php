<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Khách Mời — {{ $sideConfig['manager_name'] }} | Mây Hẹn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/common/base.css') }}">
    <style>
        body {
            background: #FAF8F5;
            color: #1F1D1A;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 16px;
            box-sizing: border-box;
            font-size: 15px;
        }
        .mayhen_family_wrapper {
            max-width: 860px;
            margin: 0 auto;
            padding-bottom: 60px;
        }
        .mayhen_family_card {
            background: #FFFFFF;
            border: 1px solid #EAE3D9;
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        }
        .mayhen_family_btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: opacity 0.15s;
        }
        .mayhen_family_btn_primary {
            background: #9C7A4A;
            color: #FFFFFF;
        }
        .mayhen_family_btn_dark {
            background: #1F1D1A;
            color: #FFFFFF;
        }
        .mayhen_family_btn_outline {
            background: #FAF8F5;
            border: 1px solid #D8CEBD;
            color: #1F1D1A;
        }
        .mayhen_guest_item {
            background: #FFFFFF;
            border: 1px solid #EAE3D9;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
        }
        .mayhen_stat_box {
            background: #FAF8F5;
            border: 1px solid #EAE3D9;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="mayhen_family_wrapper">
        {{-- Header Topbar --}}
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <span style="background: {{ $side === 'nha-trai' ? '#FAF8F5' : '#FFF5F5' }}; border: 1px solid {{ $side === 'nha-trai' ? '#EAE3D9' : '#FED7D7' }}; color: {{ $side === 'nha-trai' ? '#9C7A4A' : '#C53030' }}; font-size: 13px; font-weight: 700; padding: 6px 14px; border-radius: 20px;">
                {{ $side === 'nha-trai' ? '👑 Cổng Phụ Huynh Họ Nhà Trai' : '🌸 Cổng Phụ Huynh Họ Nhà Gái' }}
            </span>

            <div style="display: flex; gap: 10px; align-items: center;">
                <a href="{{ route('family_portal.print', ['slug' => $invitation->slug, 'side' => $side]) }}" target="_blank" class="mayhen_family_btn mayhen_family_btn_outline" style="padding: 8px 14px; font-size: 13.5px;">
                    🖨️ In Danh Sách (A4)
                </a>
                <a href="{{ route('family_portal.logout', ['slug' => $invitation->slug, 'side' => $side]) }}" style="font-size: 13px; color: #8C827A; text-decoration: underline;">
                    Đăng xuất
                </a>
            </div>
        </div>

        {{-- Thẻ Tiêu Đề Cổng --}}
        <div class="mayhen_family_card" style="border-left: 5px solid {{ $side === 'nha-trai' ? '#9C7A4A' : '#C55353' }};">
            <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 30px; font-weight: 700; margin: 0 0 6px 0; color: #1F1D1A;">
                Sổ Quản Lý Khách Mời — {{ $sideConfig['manager_name'] }}
            </h1>
            <p style="font-size: 15px; color: #666; margin: 0;">
                Hôn lễ: <strong style="color: #1F1D1A;">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong>
                @if($wedding->wedding_date)
                    • Ngày cưới: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong>
                @endif
            </p>
        </div>

        {{-- Flash Thông Báo --}}
        @if(session('success'))
            <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 15px; font-weight: 500;">
                {{ session('success') }}
            </div>
        @endif

        {{-- 3 Chỉ Số Quan Khách Lớn --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div class="mayhen_stat_box">
                <div style="font-size: 13px; color: #8C827A; text-transform: uppercase; font-weight: 600;">Tổng Khách Mời</div>
                <div style="font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 700; color: #1F1D1A; line-height: 1.2;">
                    {{ $stats['total_guests'] }} <span style="font-size: 15px; font-family: 'Inter'; font-weight: 500;">người</span>
                </div>
                <div style="font-size: 12px; color: #8C827A;">Dự kiến: {{ $stats['total_expected'] }} suất cỗ</div>
            </div>

            <div class="mayhen_stat_box" style="border-color: #BCF0DA; background: #F3FAF7;">
                <div style="font-size: 13px; color: #046C4E; text-transform: uppercase; font-weight: 600;">Đã Nhận Lời Tham Dự</div>
                <div style="font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 700; color: #046C4E; line-height: 1.2;">
                    {{ $stats['attending_count'] }} <span style="font-size: 15px; font-family: 'Inter'; font-weight: 500;">khách</span>
                </div>
                <div style="font-size: 12px; color: #046C4E;">Chắc chắn đi: <strong>{{ $stats['attending_attendees'] }} suất</strong></div>
            </div>

            <div class="mayhen_stat_box" style="border-color: #F8B4B4; background: #FDF2F2;">
                <div style="font-size: 13px; color: #9B1C1C; text-transform: uppercase; font-weight: 600;">Chưa Phản Hồi / Báo Bận</div>
                <div style="font-family: 'Cormorant Garamond', serif; font-size: 34px; font-weight: 700; color: #9B1C1C; line-height: 1.2;">
                    {{ $stats['pending_count'] }} <span style="font-size: 15px; font-family: 'Inter'; font-weight: 500;">chưa báo</span>
                </div>
                <div style="font-size: 12px; color: #9B1C1C;">Không đi: {{ $stats['declined_count'] }} người</div>
            </div>
        </div>

        {{-- Khối Thêm Khách Mới Nhanh --}}
        <div class="mayhen_family_card">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; margin: 0 0 14px 0; color: #1F1D1A;">
                ➕ Thêm Khách Mời Mới Vào Danh Sách
            </h3>

            <form action="{{ route('family_portal.store_guest', ['slug' => $invitation->slug, 'side' => $side]) }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 5px;">Họ và tên khách mời: *</label>
                        <input type="text" name="name" required placeholder="Ví dụ: Nguyễn Văn Nam" 
                               style="width: 100%; box-sizing: border-box; padding: 11px 14px; border: 1.5px solid #D8CEBD; border-radius: 8px; font-size: 15px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 5px;">Danh xưng kính gửi:</label>
                        <select name="salutation" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border: 1.5px solid #D8CEBD; border-radius: 8px; font-size: 15px; background: white;">
                            <option value="Bác">Bác</option>
                            <option value="Cô">Cô</option>
                            <option value="Chú">Chú</option>
                            <option value="Dì">Dì</option>
                            <option value="Anh">Anh</option>
                            <option value="Chị">Chị</option>
                            <option value="Bạn">Bạn</option>
                            <option value="Em">Em</option>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 5px;">Số điện thoại (nếu có):</label>
                        <input type="text" name="phone" placeholder="0901234567" 
                               style="width: 100%; box-sizing: border-box; padding: 11px 14px; border: 1.5px solid #D8CEBD; border-radius: 8px; font-size: 15px;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 5px;">Số suất mời dự kiến:</label>
                        <select name="expected_attendees" style="width: 100%; box-sizing: border-box; padding: 11px 14px; border: 1.5px solid #D8CEBD; border-radius: 8px; font-size: 15px; background: white;">
                            <option value="1">1 người</option>
                            <option value="2" selected>2 người (Vợ chồng / Kèm người thương)</option>
                            <option value="3">3 người (Cả gia đình)</option>
                            <option value="4">4 người</option>
                        </select>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="mayhen_family_btn mayhen_family_btn_primary" style="padding: 12px 28px;">
                        Lưu Khách Mời &rarr;
                    </button>
                </div>
            </form>
        </div>

        {{-- Tìm Kiếm & Lọc Danh Sách --}}
        <div style="margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap;">
            <form action="{{ route('family_portal.show', ['slug' => $invitation->slug, 'side' => $side]) }}" method="GET" style="display: flex; gap: 10px; flex-grow: 1;">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="🔍 Tìm tên khách mời hoặc số điện thoại..." 
                       style="flex-grow: 1; padding: 11px 14px; border: 1.5px solid #D8CEBD; border-radius: 8px; font-size: 15px; background: white;">

                <select name="status" style="padding: 11px 14px; border: 1.5px solid #D8CEBD; border-radius: 8px; font-size: 14px; background: white;">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="attending" {{ ($status ?? '') === 'attending' ? 'selected' : '' }}>Đã nhận lời đi</option>
                    <option value="declined" {{ ($status ?? '') === 'declined' ? 'selected' : '' }}>Báo bận không đi</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Chưa phản hồi</option>
                </select>

                <button type="submit" class="mayhen_family_btn mayhen_family_btn_dark" style="padding: 11px 18px;">
                    Lọc
                </button>
                @if($search || $status)
                    <a href="{{ route('family_portal.show', ['slug' => $invitation->slug, 'side' => $side]) }}" class="mayhen_family_btn mayhen_family_btn_outline" style="padding: 11px 14px;">
                        Xóa lọc
                    </a>
                @endif
            </form>
        </div>

        {{-- Danh Sách Khách Mời Chữ To --}}
        <div>
            @forelse($guests as $guest)
                @php
                    $guestLink = route('invitation.show', [
                        'slug' => $invitation->slug,
                        'token' => $guest->guest_token,
                    ]);
                    $salutation = $guest->salutation ?: 'Quý khách';
                    $coupleName = $wedding->groom_name . ' & ' . $wedding->bride_name;
                    $zaloMsg = "Kính gửi {$salutation} {$guest->name}! Gia đình chúng tôi trân trọng kính mời {$salutation} tới dự lễ cưới của hai cháu {$coupleName}. Kính gửi thiệp mời trân trọng tại: {$guestLink}";
                @endphp
                <div class="mayhen_guest_item">
                    <div>
                        <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
                            <strong style="font-size: 18px; color: #1F1D1A;">{{ $guest->salutation }} {{ $guest->name }}</strong>
                            @if($guest->phone)
                                <span style="font-size: 14px; color: #8C827A;">📞 {{ $guest->phone }}</span>
                            @endif
                        </div>

                        <div style="font-size: 13.5px; color: #666; margin-top: 4px;">
                            Nhóm: <strong>{{ $guest->group_name }}</strong> 
                            @if($guest->table_name)
                                • Bàn: <strong>{{ $guest->table_name }}</strong>
                            @endif
                            • Suất mời: <strong>{{ $guest->expected_attendees }} người</strong>
                        </div>

                        {{-- Trạng thái RSVP --}}
                        <div style="margin-top: 8px;">
                            @if($guest->response && $guest->response->attending_status === 'attending')
                                <span style="background: #DEF7EC; border: 1px solid #31C48D; color: #03543F; font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                    ✓ Đã nhận lời tham dự ({{ $guest->response->adults_count ?? 1 }} người)
                                </span>
                            @elseif($guest->response && $guest->response->attending_status === 'declined')
                                <span style="background: #FDE8E8; border: 1px solid #F98080; color: #9B1C1C; font-size: 13px; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                    ✕ Báo bận không tham dự
                                </span>
                            @else
                                <span style="background: #FAF8F5; border: 1px solid #D8CEBD; color: #8C827A; font-size: 13px; padding: 4px 10px; border-radius: 6px;">
                                    ⏳ Chưa có phản hồi
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Thao tác nhanh cho phụ huynh --}}
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        {{-- Nút cập nhật nhanh khi người quen gọi điện --}}
                        <form action="{{ route('family_portal.update_rsvp', ['slug' => $invitation->slug, 'side' => $side, 'guestId' => $guest->id]) }}" method="POST" style="display: inline-flex; gap: 6px;">
                            @csrf
                            @if(!$guest->response || $guest->response->attending_status !== 'attending')
                                <input type="hidden" name="attending_status" value="attending">
                                <button type="submit" class="mayhen_family_btn" style="background: #0E9F6E; color: white; padding: 8px 12px; font-size: 13px;">
                                    Báo Có Đi
                                </button>
                            @else
                                <input type="hidden" name="attending_status" value="declined">
                                <button type="submit" class="mayhen_family_btn" style="background: #F05252; color: white; padding: 8px 12px; font-size: 13px;">
                                    Báo Bận
                                </button>
                            @endif
                        </form>

                        {{-- Nút Gửi Zalo --}}
                        <button type="button" onclick="copyZaloText('{{ addslashes($zaloMsg) }}')" class="mayhen_family_btn mayhen_family_btn_outline" style="padding: 8px 14px; font-size: 13px;" title="Sao chép lời nhắn gửi qua Zalo">
                            📲 Gửi Zalo
                        </button>
                    </div>
                </div>
            @empty
                <div class="mayhen_family_card" style="text-align: center; padding: 50px 20px;">
                    <div style="font-size: 36px; margin-bottom: 10px;">📋</div>
                    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 6px 0;">
                        Chưa Có Khách Mời Nào
                    </h3>
                    <p style="font-size: 14px; color: #8C827A; margin: 0;">
                        Quý phụ huynh hãy nhập thông tin khách mời ở ô phía trên để lưu danh sách.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function copyZaloText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Đã sao chép lời mời và link thiệp riêng của khách! Quý phụ huynh chỉ cần mở Zalo và dán (Paste) để gửi.');
            }).catch(() => {
                const temp = document.createElement('textarea');
                temp.value = text;
                document.body.appendChild(temp);
                temp.select();
                document.execCommand('copy');
                document.body.removeChild(temp);
                alert('Đã sao chép lời mời và link thiệp riêng của khách! Quý phụ huynh chỉ cần mở Zalo và dán (Paste) để gửi.');
            });
        }
    </script>
</body>
</html>

