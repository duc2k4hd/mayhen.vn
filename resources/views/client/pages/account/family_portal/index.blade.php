@extends('client.pages.account.layout')

@section('page_title', 'Cổng Phụ Huynh & Phân Quyền Gia Đình Hai Bên | ' . config('app.name'))
@section('account_breadcrumb', 'Cổng Phụ Huynh & Gia Đình')

@section('account_content')
<div class="mayhen_family_portal_studio">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Cổng Phụ Huynh & Phân Quyền Gia Đình
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Ủy quyền an toàn cho bố mẹ hai bên tự quản lý khách mời của mình • Giao diện chữ to, đơn giản, cách ly dữ liệu hai họ tuyệt đối.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_account_btn" style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 10px 16px; border-radius: 8px; font-size: 13px; text-decoration: none; font-weight: 500;">
                Sổ Khách Mời Tổng Thể &rarr;
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ route('account.family_portal.update', $invitation->id) }}" method="POST">
        @csrf

        {{-- Bật / Tắt Tổng Thể --}}
        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <strong style="font-size: 16px; color: #1F1D1A; display: block; margin-bottom: 4px;">
                    Kích Hoạt Cổng Gia Đình & Đồng Chủ Trì (Family Co-Host Portal)
                </strong>
                <span style="font-size: 13px; color: #8C827A;">
                    Cho phép bố mẹ hai bên truy cập cổng riêng bằng mã PIN hoặc đường link bảo mật để thêm khách và xem danh sách.
                </span>
            </div>
            <label style="position: relative; display: inline-block; width: 48px; height: 26px; cursor: pointer;">
                <input type="checkbox" name="enabled" value="1" {{ $config['enabled'] ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="togglePortalOptions(this.checked)">
                <span style="position: absolute; cursor: pointer; inset: 0; background: {{ $config['enabled'] ? '#0E9F6E' : '#D1D5DB' }}; transition: .2s; border-radius: 26px;" id="portal_toggle_slider"></span>
                <span style="position: absolute; content: ''; height: 20px; width: 20px; left: {{ $config['enabled'] ? '25px' : '3px' }}; bottom: 3px; background: white; transition: .2s; border-radius: 50%;" id="portal_toggle_knob"></span>
            </label>
        </div>

        {{-- 2 Cột Quản Trị: Họ Nhà Trai & Họ Nhà Gái --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 28px;">
            {{-- Cột Nhà Trai --}}
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <span style="background: #FAF8F5; border: 1px solid #EAE3D9; color: #9C7A4A; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                            👑 Cổng Họ Nhà Trai
                        </span>
                        <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="groom[enabled]" value="1" {{ $config['groom']['enabled'] ? 'checked' : '' }}>
                            <span style="color: #1F1D1A; font-weight: 500;">Bật cổng</span>
                        </label>
                    </div>

                    {{-- Thống kê nhanh --}}
                    <div style="background: #FAF8F5; border-radius: 10px; padding: 14px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; text-align: center;">
                        <div>
                            <div style="font-size: 11px; color: #8C827A;">Tổng khách</div>
                            <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #1F1D1A;">{{ $groomStats['total_guests'] }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #046C4E;">Đã nhận lời</div>
                            <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #046C4E;">{{ $groomStats['attending_count'] }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #8C827A;">Chưa phản hồi</div>
                            <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #8C827A;">{{ $groomStats['pending_count'] }}</div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 5px;">
                                Người quản lý đại diện (Phụ huynh / Bác trưởng):
                            </label>
                            <input type="text" name="groom[manager_name]" value="{{ $config['groom']['manager_name'] }}" 
                                   placeholder="Ví dụ: Bố Chú Rể (Bác Nam)" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 5px;">
                                Mã PIN bảo mật (4-6 số dễ nhớ):
                            </label>
                            <input type="text" name="groom[pin]" value="{{ $config['groom']['pin'] }}" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; font-family: monospace; letter-spacing: 2px; font-weight: 700;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 5px;">
                                Nhóm mặc định khi phụ huynh thêm khách:
                            </label>
                            <input type="text" name="groom[default_group]" value="{{ $config['groom']['default_group'] }}" 
                                   placeholder="Nhà Trai" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                        </div>

                        {{-- Link truy cập 1-chạm --}}
                        <div style="background: #FDFBF7; border: 1px dashed #D8CEBD; border-radius: 10px; padding: 12px; margin-top: 6px;">
                            <label style="display: block; font-size: 11px; text-transform: uppercase; font-weight: 600; color: #9C7A4A; margin-bottom: 4px;">
                                Link 1-chạm gửi Bố Mẹ (Không cần gõ PIN):
                            </label>
                            @php
                                $groomUrl = route('family_portal.show', [
                                    'slug' => $invitation->slug,
                                    'side' => 'nha-trai',
                                    'token' => $config['groom']['token'],
                                ]);
                            @endphp
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="groom_portal_url" value="{{ $groomUrl }}" readonly 
                                       style="width: 100%; background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 6px; padding: 7px 10px; font-size: 11.5px; color: #555;">
                                <button type="button" onclick="copyToClipboard('groom_portal_url', 'Đã sao chép link Cổng Nhà Trai')" 
                                        style="background: #9C7A4A; color: #FFFFFF; border: none; border-radius: 6px; padding: 7px 12px; font-size: 12px; cursor: pointer; white-space: nowrap; font-weight: 500;">
                                    Sao chép
                                </button>
                            </div>
                            <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <a href="{{ $groomUrl }}" target="_blank" style="font-size: 12px; color: #9C7A4A; text-decoration: underline;">
                                    Mở xem thử giao diện &nearr;
                                </a>
                                <button type="button" onclick="regenerateSideToken('nha-trai', 'groom_portal_url')" style="background: none; border: none; color: #8C827A; font-size: 11px; cursor: pointer; text-decoration: underline;">
                                    Đổi mã truy cập mới
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cột Nhà Gái --}}
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <span style="background: #FAF8F5; border: 1px solid #EAE3D9; color: #C55353; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                            🌸 Cổng Họ Nhà Gái
                        </span>
                        <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; cursor: pointer;">
                            <input type="checkbox" name="bride[enabled]" value="1" {{ $config['bride']['enabled'] ? 'checked' : '' }}>
                            <span style="color: #1F1D1A; font-weight: 500;">Bật cổng</span>
                        </label>
                    </div>

                    {{-- Thống kê nhanh --}}
                    <div style="background: #FAF8F5; border-radius: 10px; padding: 14px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; text-align: center;">
                        <div>
                            <div style="font-size: 11px; color: #8C827A;">Tổng khách</div>
                            <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #1F1D1A;">{{ $brideStats['total_guests'] }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #046C4E;">Đã nhận lời</div>
                            <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #046C4E;">{{ $brideStats['attending_count'] }}</div>
                        </div>
                        <div>
                            <div style="font-size: 11px; color: #8C827A;">Chưa phản hồi</div>
                            <div style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 700; color: #8C827A;">{{ $brideStats['pending_count'] }}</div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 5px;">
                                Người quản lý đại diện (Phụ huynh / Mẹ Cô Dâu):
                            </label>
                            <input type="text" name="bride[manager_name]" value="{{ $config['bride']['manager_name'] }}" 
                                   placeholder="Ví dụ: Mẹ Cô Dâu (Bác Lan)" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 5px;">
                                Mã PIN bảo mật (4-6 số dễ nhớ):
                            </label>
                            <input type="text" name="bride[pin]" value="{{ $config['bride']['pin'] }}" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; font-family: monospace; letter-spacing: 2px; font-weight: 700;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 5px;">
                                Nhóm mặc định khi phụ huynh thêm khách:
                            </label>
                            <input type="text" name="bride[default_group]" value="{{ $config['bride']['default_group'] }}" 
                                   placeholder="Nhà Gái" 
                                   style="width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px;">
                        </div>

                        {{-- Link truy cập 1-chạm --}}
                        <div style="background: #FDFBF7; border: 1px dashed #D8CEBD; border-radius: 10px; padding: 12px; margin-top: 6px;">
                            <label style="display: block; font-size: 11px; text-transform: uppercase; font-weight: 600; color: #C55353; margin-bottom: 4px;">
                                Link 1-chạm gửi Mẹ (Không cần gõ PIN):
                            </label>
                            @php
                                $brideUrl = route('family_portal.show', [
                                    'slug' => $invitation->slug,
                                    'side' => 'nha-gai',
                                    'token' => $config['bride']['token'],
                                ]);
                            @endphp
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="bride_portal_url" value="{{ $brideUrl }}" readonly 
                                       style="width: 100%; background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 6px; padding: 7px 10px; font-size: 11.5px; color: #555;">
                                <button type="button" onclick="copyToClipboard('bride_portal_url', 'Đã sao chép link Cổng Nhà Gái')" 
                                        style="background: #C55353; color: #FFFFFF; border: none; border-radius: 6px; padding: 7px 12px; font-size: 12px; cursor: pointer; white-space: nowrap; font-weight: 500;">
                                    Sao chép
                                </button>
                            </div>
                            <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center;">
                                <a href="{{ $brideUrl }}" target="_blank" style="font-size: 12px; color: #C55353; text-decoration: underline;">
                                    Mở xem thử giao diện &nearr;
                                </a>
                                <button type="button" onclick="regenerateSideToken('nha-gai', 'bride_portal_url')" style="background: none; border: none; color: #8C827A; font-size: 11px; cursor: pointer; text-decoration: underline;">
                                    Đổi mã truy cập mới
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Nút Lưu Cấu Hình --}}
        <div style="text-align: right; background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 16px 20px;">
            <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary" style="padding: 10px 24px; font-size: 14px; font-weight: 600;">
                Lưu Thay Đổi &amp; Cập Nhật Cổng Gia Đình
            </button>
        </div>
    </form>
</div>

<script>
    function togglePortalOptions(isChecked) {
        const slider = document.getElementById('portal_toggle_slider');
        const knob = document.getElementById('portal_toggle_knob');
        if (isChecked) {
            slider.style.background = '#0E9F6E';
            knob.style.left = '25px';
        } else {
            slider.style.background = '#D1D5DB';
            knob.style.left = '3px';
        }
    }

    function copyToClipboard(elementId, successMsg) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            alert(successMsg);
        }).catch(() => {
            document.execCommand('copy');
            alert(successMsg);
        });
    }

    function regenerateSideToken(side, targetInputId) {
        if (!confirm('Bạn có chắc chắn muốn tạo mã truy cập mới? Link cũ đã gửi cho phụ huynh sẽ bị vô hiệu hóa.')) {
            return;
        }

        const url = "{{ route('account.family_portal.regenerate_token', ['id' => $invitation->id, 'side' => '__SIDE__']) }}".replace('__SIDE__', side);

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
                document.getElementById(targetInputId).value = data.url;
                alert(data.message);
            } else {
                alert(data.message || 'Có lỗi xảy ra.');
            }
        })
        .catch(() => alert('Không thể kết nối máy chủ.'));
    }
</script>
@endsection

