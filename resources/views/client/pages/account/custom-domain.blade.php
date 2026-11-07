@extends('client.pages.account.layout')

@php
    $groom = $wedding->groom_name ?? 'Chú Rể';
    $bride = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = "Tên Miền Riêng: {$groom} & {$bride}";
    $activeUrl = $domain->activeUrl();
@endphp

@section('account_breadcrumb')
    <a href="{{ route('account.invitations') }}" style="color: inherit; text-decoration: none;">Thiệp cưới</a>
    <span class="mayhen_account_breadcrumbs_sep">/</span>
    <span>Tên miền riêng</span>
@endsection

@section('account_content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Tên Miền Riêng: {{ $groom }} & {{ $bride }}
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Khẳng định dấu ấn độc bản với tên miền phụ miễn phí hoặc tên miền riêng mang tên đôi uyên ương.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Vào Studio Sửa Thiệp</span>
            </a>
            <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Xem Thiệp Trực Tiếp</span>
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="mayhen_account_alert mayhen_account_alert_success" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #F2F8F5; border: 1px solid #D2E7DC; color: #1B5E3C; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mayhen_account_alert mayhen_account_alert_danger" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #FDF4F4; border: 1px solid #F3D2D2; color: #9E2A2B; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Card Tổng Quan Trạng Thái Tên Miền -->
    <div style="background: linear-gradient(135deg, #FAF8F5 0%, #F5EFEB 100%); border: 1px solid #EAE3D9; border-radius: 16px; padding: 22px 26px; margin-bottom: 28px; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div>
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em; color: #8C827A; font-weight: 600; margin-bottom: 6px;">
                Đường Dẫn Đang Hoạt Động Ưu Tiên
            </div>
            @if($activeUrl)
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ $activeUrl }}" target="_blank" style="font-family: monospace; font-size: 17px; font-weight: 700; color: #9C7A4A; text-decoration: none;">
                        {{ $activeUrl }}
                    </a>
                    <span style="background: #E8F5E9; color: #2E7D32; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; text-transform: uppercase;">
                        @if($domain->isActive() && $domain->custom_domain) Tên Miền Riêng @else Subdomain @endif
                    </span>
                </div>
            @else
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" style="font-family: monospace; font-size: 15px; font-weight: 600; color: #5A534B; text-decoration: none;">
                        {{ route('invitation.show', $invitation->slug) }}
                    </a>
                    <span style="background: #EFE8DD; color: #6E665E; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px;">
                        Mặc Định
                    </span>
                </div>
            @endif
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            @if($activeUrl)
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="navigator.clipboard.writeText('{{ $activeUrl }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Chép Link', 2000);">
                    Chép Link
                </button>
                <a href="{{ $activeUrl }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                    Mở Trang Web
                </a>
            @else
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="navigator.clipboard.writeText('{{ route('invitation.show', $invitation->slug) }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Chép Link Mặc Định', 2000);">
                    Chép Link Mặc Định
                </button>
            @endif
        </div>
    </div>

    <!-- KHỐI 1: TÊN MIỀN PHỤ MIỄN PHÍ (VANITY SUBDOMAIN) -->
    <div class="mayhen_account_section" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 16px; padding: 26px; margin-bottom: 28px;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap;">
            <div>
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #1F1D1A; margin: 0 0 4px 0;">
                    1. Tên Miền Phụ Mây Hẹn (Vanity Subdomain)
                </h3>
                <p style="font-size: 13px; color: #7A726A; margin: 0;">
                    Kích hoạt tức thì, miễn phí trọn đời, định dạng ngắn gọn <code>[tên-cặp-đôi].mayhen.vn</code> dễ nhớ khi gửi khách mời.
                </p>
            </div>
            @if($domain->subdomain)
                <span style="background: #E8F5E9; color: #2E7D32; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                    ✓ Đang Hoạt Động
                </span>
            @endif
        </div>

        <form action="{{ route('account.invitations.domain.subdomain', $invitation->id) }}" method="POST">
            @csrf
            <div style="margin-bottom: 16px;">
                <label for="subdomain-input" style="display: block; font-size: 13px; font-weight: 500; color: #3A3530; margin-bottom: 6px;">
                    Chọn tên miền phụ mong muốn:
                </label>
                <div style="display: flex; align-items: center; border: 1px solid #D8CEBD; border-radius: 10px; overflow: hidden; background: #FAF8F5;">
                    <span style="padding: 10px 14px; background: #EFE8DD; color: #5A534B; font-size: 13px; font-family: monospace; border-right: 1px solid #D8CEBD; white-space: nowrap;">
                        https://
                    </span>
                    <input type="text" 
                        name="subdomain" 
                        id="subdomain-input" 
                        value="{{ old('subdomain', $domain->subdomain) }}" 
                        required 
                        minlength="3" 
                        maxlength="63"
                        pattern="^[a-z0-9]([a-z0-9\-]{1,61}[a-z0-9])?$"
                        placeholder="vd: tuan-lan hoặc tuanlan2026"
                        style="flex: 1; border: none; padding: 10px 14px; font-family: monospace; font-size: 14px; font-weight: 600; color: #9C7A4A; background: transparent; outline: none;">
                    <span style="padding: 10px 14px; background: #EFE8DD; color: #5A534B; font-size: 13px; font-family: monospace; border-left: 1px solid #D8CEBD; white-space: nowrap;">
                        .{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'mayhen.vn' }}
                    </span>
                </div>
                @error('subdomain')
                    <span style="display: block; color: #9E2A2B; font-size: 12px; margin-top: 6px;">{{ $message }}</span>
                @enderror
                <span style="display: block; font-size: 12px; color: #8C827A; margin-top: 6px;">
                    Chỉ dùng chữ cái thường không dấu (a-z), chữ số (0-9) và dấu gạch ngang (-). Tối thiểu 3 ký tự.
                </span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>{{ $domain->subdomain ? 'Cập Nhật Tên Miền Phụ' : 'Kích Hoạt Tên Miền Phụ' }}</span>
                </button>
            </div>
        </form>

        @if($domain->subdomain)
            <div style="margin-top: 12px; display: flex; justify-content: flex-end;">
                <form action="{{ route('account.invitations.domain.destroy', $invitation->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="type" value="subdomain">
                    <button type="submit" 
                        class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm"
                        style="color: #9E2A2B; border-color: #F3D2D2;"
                        onclick="return confirm('Bạn có chắc chắn muốn gỡ bỏ tên miền phụ này?');">
                        Gỡ Bỏ Subdomain
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- KHỐI 2: TÊN MIỀN RIÊNG ĐỘC BẢN (CUSTOM DOMAIN) -->
    <div class="mayhen_account_section" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 16px; padding: 26px; margin-bottom: 28px;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap;">
            <div>
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #1F1D1A; margin: 0 0 4px 0;">
                    2. Tên Miền Riêng Độc Bản (Custom Domain)
                </h3>
                <p style="font-size: 13px; color: #7A726A; margin: 0;">
                    Sử dụng tên miền riêng của chính hai bạn (ví dụ: <code>damcuoituanlan.vn</code> hoặc <code>tuanlan.wedding</code>) để gửi thiệp cưới.
                </p>
            </div>

            @if($domain->custom_domain)
                @if($domain->isActive())
                    <span style="background: #E8F5E9; color: #2E7D32; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                        ✓ Đã Xác Minh DNS & SSL
                    </span>
                @elseif($domain->isFailed())
                    <span style="background: #FDF4F4; color: #9E2A2B; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                        ✕ Chưa Kết Nối DNS
                    </span>
                @else
                    <span style="background: #FFF8E1; color: #F57F17; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;">
                        ⏳ Đang Chờ Trỏ DNS
                    </span>
                @endif
            @endif
        </div>

        <form action="{{ route('account.invitations.domain.custom', $invitation->id) }}" method="POST" style="margin-bottom: 24px;">
            @csrf
            <div style="margin-bottom: 16px;">
                <label for="custom-domain-input" style="display: block; font-size: 13px; font-weight: 500; color: #3A3530; margin-bottom: 6px;">
                    Nhập tên miền riêng của bạn:
                </label>
                <div style="display: flex; align-items: center; border: 1px solid #D8CEBD; border-radius: 10px; overflow: hidden; background: #FAF8F5;">
                    <span style="padding: 10px 14px; background: #EFE8DD; color: #5A534B; font-size: 13px; font-family: monospace; border-right: 1px solid #D8CEBD; white-space: nowrap;">
                        https://
                    </span>
                    <input type="text" 
                        name="custom_domain" 
                        id="custom-domain-input" 
                        value="{{ old('custom_domain', $domain->custom_domain) }}" 
                        required 
                        placeholder="vd: damcuoituanlan.vn hoặc tuanlan.wedding"
                        style="flex: 1; border: none; padding: 10px 14px; font-family: monospace; font-size: 14px; font-weight: 600; color: #9C7A4A; background: transparent; outline: none;">
                </div>
                @error('custom_domain')
                    <span style="display: block; color: #9E2A2B; font-size: 12px; margin-top: 6px;">{{ $message }}</span>
                @enderror
                <span style="display: block; font-size: 12px; color: #8C827A; margin-top: 6px;">
                    Nhập trực tiếp tên miền mà hai bạn đã đăng ký tại nhà cung cấp (Mắt Bão, PA Việt Nam, Tenten, Cloudflare, Namecheap...).
                </span>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu Tên Miền Riêng</span>
                </button>
            </div>
        </form>

        @if($domain->custom_domain)
            <div style="margin-top: -12px; margin-bottom: 20px; display: flex; justify-content: flex-end;">
                <form action="{{ route('account.invitations.domain.destroy', $invitation->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="type" value="custom_domain">
                    <button type="submit" 
                        class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm"
                        style="color: #9E2A2B; border-color: #F3D2D2;"
                        onclick="return confirm('Bạn có chắc chắn muốn gỡ bỏ tên miền riêng này?');">
                        Gỡ Bỏ Tên Miền Riêng
                    </button>
                </form>
            </div>
        @endif

        <!-- Hướng dẫn Cấu Hình DNS CNAME -->
        <div style="background: #FAF8F5; border: 1px dashed #D8CEBD; border-radius: 12px; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; flex-wrap: wrap;">
                <h4 style="font-size: 14px; font-weight: 600; color: #1F1D1A; margin: 0;">
                    Hướng Dẫn Cấu Hình Bản Ghi DNS CNAME
                </h4>
                @if($domain->custom_domain)
                    <form action="{{ route('account.invitations.domain.verify', $invitation->id) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                            </svg>
                            <span>Kiểm Tra Kết Nối DNS & Kích Hoạt</span>
                        </button>
                    </form>
                @endif
            </div>

            <p style="font-size: 13px; color: #6E665E; line-height: 1.5; margin-bottom: 14px;">
                Đăng nhập vào trang quản trị tên miền của bạn (nơi bạn mua tên miền) và thêm bản ghi CNAME như bảng dưới đây:
            </p>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1px solid #E5DCCD; color: #7A726A; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 8px 12px;">Loại (Type)</th>
                            <th style="padding: 8px 12px;">Tên (Host / Name)</th>
                            <th style="padding: 8px 12px;">Giá Trị Đích (Value / Target)</th>
                            <th style="padding: 8px 12px;">TTL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid #EFE8DD;">
                            <td style="padding: 10px 12px; font-family: monospace; font-weight: 600; color: #1F1D1A;">CNAME</td>
                            <td style="padding: 10px 12px; font-family: monospace; color: #5A534B;">@ hoặc www</td>
                            <td style="padding: 10px 12px; font-family: monospace; font-weight: 600; color: #9C7A4A;">
                                {{ $cnameTarget }}
                                <button type="button" style="background: none; border: none; cursor: pointer; color: #9C7A4A; padding: 0 4px; font-size: 12px; text-decoration: underline;" onclick="navigator.clipboard.writeText('{{ $cnameTarget }}'); this.innerText='Đã chép'; setTimeout(() => this.innerText='Chép', 2000);">
                                    Chép
                                </button>
                            </td>
                            <td style="padding: 10px 12px; color: #7A726A;">Tự động / 3600</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p style="font-size: 12px; color: #8C827A; margin-top: 12px; line-height: 1.5;">
                <em>Lưu ý:</em> Sau khi trỏ DNS, bản ghi có thể mất từ 5 phút đến 24 giờ để cập nhật trên toàn bộ mạng Internet quốc tế. Chứng chỉ bảo mật HTTPS / SSL sẽ được hệ thống Mây Hẹn tự động kích hoạt miễn phí.
            </p>
        </div>
    </div>

    <!-- KHỐI 3: HỆ SINH THÁI TÊN MIỀN ĐA TRANG -->
    <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 16px; padding: 26px;">
        <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 18px; font-weight: 600; color: #1F1D1A; margin: 0 0 8px 0;">
            Hệ Sinh Thái Đám Cưới Đa Trang Trên Tên Miền Riêng
        </h4>
        <p style="font-size: 13px; color: #6E665E; margin-bottom: 16px;">
            Khi tên miền riêng được kích hoạt, khách mời và ban tổ chức có thể truy cập toàn bộ các trang sự kiện của hai bạn ngay trên tên miền này:
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-weight: 600; font-size: 13px; color: #1F1D1A; margin-bottom: 4px;">1. Thiệp Cưới & RSVP</div>
                <div style="font-family: monospace; font-size: 12px; color: #9C7A4A;">
                    {{ $activeUrl ?: url('/thiep/' . $invitation->slug) }}
                </div>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-weight: 600; font-size: 13px; color: #1F1D1A; margin-bottom: 4px;">2. Két Lưu Trữ Kỷ Niệm Số</div>
                <div style="font-family: monospace; font-size: 12px; color: #9C7A4A;">
                    {{ $activeUrl ? $activeUrl . '/ky-niem' : url('/ky-niem/' . $invitation->slug) }}
                </div>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-weight: 600; font-size: 13px; color: #1F1D1A; margin-bottom: 4px;">3. Thu Thập Ảnh Bàn Tiệc</div>
                <div style="font-family: monospace; font-size: 12px; color: #9C7A4A;">
                    {{ $activeUrl ? $activeUrl . '/photo-drop' : url('/photo-drop/' . $invitation->slug) }}
                </div>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-weight: 600; font-size: 13px; color: #1F1D1A; margin-bottom: 4px;">4. Cổng Tiếp Tân Quét QR</div>
                <div style="font-family: monospace; font-size: 12px; color: #9C7A4A;">
                    {{ $activeUrl ? $activeUrl . '/diem-danh-tiec' : url('/diem-danh-tiec/' . $invitation->slug) }}
                </div>
            </div>
        </div>
    </div>
@endsection

