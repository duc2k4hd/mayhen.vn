@extends('admin.layouts.master')

@section('title', 'Cấu Hình Cổng Tin Nhắn Zalo ZNS & SMS')

@section('content')
<div class="mayhen_admin_notifications_cockpit">
    <!-- Header Section -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Gateway Integration</span>
                <span style="color: var(--admin-text-muted); font-size: 0.85rem;">Mục 174 PRD V3 & Mục 13.5 DOCX</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 600; color: var(--admin-charcoal); margin: 0;">
                Trung Tâm Cấu Hình Cổng Tin Nhắn Zalo ZNS và SMS
            </h1>
            <p style="margin: 0.25rem 0 0 0; color: var(--admin-text-muted); font-size: 0.9rem;">
                Quản trị kết nối viễn thông, kiểm định bảo mật token và thử nghiệm phát tin nhắn thiệp cưới thời gian thực.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.settings.platform', ['group' => 'brand_seo']) }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                Thiết Lập Nền Tảng
            </a>
            <a href="{{ route('admin.messaging.index') }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 0.85rem;">
                Chiến Dịch Phát Tin
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #2563EB;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Cổng Zalo ZNS</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ $kpis['zalo_status'] }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">
                Chế độ: <span style="font-weight: 600; text-transform: uppercase;">{{ $kpis['zalo_env'] }}</span>
            </div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #059669;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Cổng SMS Brandname</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ $kpis['sms_status'] }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">
                Nhà mạng: <span style="font-weight: 600;">{{ $kpis['sms_provider'] }}</span>
            </div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #D97706;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Lượt Thử Nghiệm (24h)</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['tests_today']) }} <span style="font-size: 0.85rem; font-weight: normal; color: var(--admin-text-muted);">lượt</span>
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Gửi kiểm thử qua Sandbox/Live</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #7C3AED;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Tỷ Lệ Phản Hồi Thành Công</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #059669; margin: 0.4rem 0 0.25rem 0;">
                {{ $kpis['success_rate'] }}%
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Không phát hiện nghẽn mạng gateway</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 0.5rem; border-bottom: 1px solid var(--admin-border-color); margin-bottom: 1.5rem;">
        <a href="{{ route('admin.settings.notifications.index', ['tab' => 'zalo']) }}" 
           class="mayhen_admin_tab {{ $activeTab === 'zalo' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $activeTab === 'zalo' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $activeTab === 'zalo' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Zalo Notification Service (ZNS)
        </a>
        <a href="{{ route('admin.settings.notifications.index', ['tab' => 'sms']) }}" 
           class="mayhen_admin_tab {{ $activeTab === 'sms' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $activeTab === 'sms' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $activeTab === 'sms' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            SMS Brandname Viễn Thông
        </a>
        <a href="{{ route('admin.settings.notifications.index', ['tab' => 'sandbox']) }}" 
           class="mayhen_admin_tab {{ $activeTab === 'sandbox' ? 'mayhen_admin_tab--active' : '' }}"
           style="padding: 0.65rem 1.25rem; font-size: 0.9rem; font-weight: 500; text-decoration: none; border-bottom: 2px solid {{ $activeTab === 'sandbox' ? 'var(--admin-gold)' : 'transparent' }}; color: {{ $activeTab === 'sandbox' ? 'var(--admin-gold)' : 'var(--admin-text-muted)' }};">
            Phòng Thử Nghiệm & Lịch Sử Gửi ({{ $testLogs->count() }})
        </a>
    </div>

    @if($activeTab === 'zalo')
    <!-- TAB 1: ZALO ZNS CONFIGURATION -->
    <div class="mayhen_admin_card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--admin-border-color);">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: var(--admin-charcoal);">Cấu Hình Kết Nối Zalo Cloud & Official Account</h3>
                <p style="margin: 0.25rem 0 0 0; color: var(--admin-text-muted); font-size: 0.85rem;">
                    Tích hợp Zalo Notification Service để gửi thiệp cưới online và mã xác thực trực tiếp vào hộp thư Zalo quan khách.
                </p>
            </div>
            <form action="{{ route('admin.settings.notifications.ping', ['gateway' => 'zalo_zns']) }}" method="POST">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                    Kiểm Tra Kết Nối (Ping Zalo)
                </button>
            </form>
        </div>

        <form action="{{ route('admin.settings.notifications.update', ['gateway' => 'zalo']) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                <!-- Toggle Kích Hoạt -->
                <div style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; background: var(--admin-bg-soft); padding: 1rem; border-radius: 6px;">
                    <div>
                        <div style="font-weight: 600; color: var(--admin-charcoal);">Kích Hoạt Zalo ZNS Gateway</div>
                        <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Cho phép hệ thống sử dụng kênh Zalo ZNS khi cô dâu chú rể gửi thiệp.</div>
                    </div>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="zalo_enabled" value="1" {{ ($configs['zalo']['zalo_enabled']['value'] ?? '0') === '1' ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                        <span class="mayhen_admin_slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: {{ ($configs['zalo']['zalo_enabled']['value'] ?? '0') === '1' ? 'var(--admin-gold)' : '#CBD5E1' }}; border-radius: 24px; transition: .3s;"></span>
                    </label>
                </div>

                <!-- Môi trường -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_env']['label'] }}
                    </label>
                    <select name="zalo_env" class="mayhen_admin_form_select" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px; background: #fff;">
                        <option value="sandbox" {{ ($configs['zalo']['zalo_env']['value'] ?? '') === 'sandbox' ? 'selected' : '' }}>Thử nghiệm (Sandbox Mode)</option>
                        <option value="production" {{ ($configs['zalo']['zalo_env']['value'] ?? '') === 'production' ? 'selected' : '' }}>Chính thức (Production Live)</option>
                    </select>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_env']['help'] }}
                    </small>
                </div>

                <!-- Zalo App ID -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_app_id']['label'] }}
                    </label>
                    <input type="text" name="zalo_app_id" value="{{ $configs['zalo']['zalo_app_id']['value'] ?? '' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_app_id']['help'] }}
                    </small>
                </div>

                <!-- Zalo Secret Key -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_secret_key']['label'] }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" name="zalo_secret_key" value="{{ $configs['zalo']['zalo_secret_key']['value'] ?? '' }}" 
                               class="mayhen_admin_form_input mayhen_password_field" style="width: 100%; padding: 0.55rem 2.2rem 0.55rem 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                        <button type="button" class="mayhen_toggle_pwd" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-text-muted); font-size: 0.8rem;">
                            Hiện
                        </button>
                    </div>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_secret_key']['help'] }} (Giữ nguyên •••••••• nếu không đổi).
                    </small>
                </div>

                <!-- Zalo OA ID -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_oa_id']['label'] }}
                    </label>
                    <input type="text" name="zalo_oa_id" value="{{ $configs['zalo']['zalo_oa_id']['value'] ?? '' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_oa_id']['help'] }}
                    </small>
                </div>

                <!-- Zalo Access Token -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_access_token']['label'] }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" name="zalo_access_token" value="{{ $configs['zalo']['zalo_access_token']['value'] ?? '' }}" 
                               class="mayhen_admin_form_input mayhen_password_field" style="width: 100%; padding: 0.55rem 2.2rem 0.55rem 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                        <button type="button" class="mayhen_toggle_pwd" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-text-muted); font-size: 0.8rem;">
                            Hiện
                        </button>
                    </div>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_access_token']['help'] }}
                    </small>
                </div>

                <!-- Zalo Refresh Token -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_refresh_token']['label'] }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" name="zalo_refresh_token" value="{{ $configs['zalo']['zalo_refresh_token']['value'] ?? '' }}" 
                               class="mayhen_admin_form_input mayhen_password_field" style="width: 100%; padding: 0.55rem 2.2rem 0.55rem 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                        <button type="button" class="mayhen_toggle_pwd" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-text-muted); font-size: 0.8rem;">
                            Hiện
                        </button>
                    </div>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_refresh_token']['help'] }}
                    </small>
                </div>

                <!-- Default Template ID -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_default_template_id']['label'] }}
                    </label>
                    <input type="text" name="zalo_default_template_id" value="{{ $configs['zalo']['zalo_default_template_id']['value'] ?? '' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_default_template_id']['help'] }}
                    </small>
                </div>

                <!-- OTP Template ID -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['zalo']['zalo_otp_template_id']['label'] }}
                    </label>
                    <input type="text" name="zalo_otp_template_id" value="{{ $configs['zalo']['zalo_otp_template_id']['value'] ?? '' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['zalo']['zalo_otp_template_id']['help'] }}
                    </small>
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--admin-border-color); display: flex; justify-content: flex-end;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem;">
                    Lưu Cấu Hình Cổng Zalo ZNS
                </button>
            </div>
        </form>
    </div>
    @endif

    @if($activeTab === 'sms')
    <!-- TAB 2: SMS BRANDNAME CONFIGURATION -->
    <div class="mayhen_admin_card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--admin-border-color);">
            <div>
                <h3 style="margin: 0; font-size: 1.15rem; color: var(--admin-charcoal);">Cấu Hình Cổng Viễn Thông SMS Brandname</h3>
                <p style="margin: 0.25rem 0 0 0; color: var(--admin-text-muted); font-size: 0.85rem;">
                    Gửi tin nhắn thương hiệu tới các mạng di động Viettel, VinaPhone, MobiFone với tên định danh hiển thị chuẩn Mây Hẹn.
                </p>
            </div>
            <form action="{{ route('admin.settings.notifications.ping', ['gateway' => 'sms_brandname']) }}" method="POST">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                    Kiểm Tra Kết Nối (Ping SMS)
                </button>
            </form>
        </div>

        <form action="{{ route('admin.settings.notifications.update', ['gateway' => 'sms']) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.25rem;">
                <!-- Toggle Kích Hoạt -->
                <div style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; background: var(--admin-bg-soft); padding: 1rem; border-radius: 6px;">
                    <div>
                        <div style="font-weight: 600; color: var(--admin-charcoal);">Kích Hoạt SMS Brandname Gateway</div>
                        <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Cho phép gửi tin nhắn thương hiệu khi quan khách không sử dụng Zalo hoặc yêu cầu SMS.</div>
                    </div>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="sms_enabled" value="1" {{ ($configs['sms']['sms_enabled']['value'] ?? '0') === '1' ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;">
                        <span class="mayhen_admin_slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: {{ ($configs['sms']['sms_enabled']['value'] ?? '0') === '1' ? 'var(--admin-gold)' : '#CBD5E1' }}; border-radius: 24px; transition: .3s;"></span>
                    </label>
                </div>

                <!-- Nhà cung cấp -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['sms']['sms_provider']['label'] }}
                    </label>
                    <select name="sms_provider" class="mayhen_admin_form_select" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px; background: #fff;">
                        <option value="esms" {{ ($configs['sms']['sms_provider']['value'] ?? '') === 'esms' ? 'selected' : '' }}>eSMS.vn Gateway</option>
                        <option value="speedsms" {{ ($configs['sms']['sms_provider']['value'] ?? '') === 'speedsms' ? 'selected' : '' }}>SpeedSMS API Service</option>
                        <option value="fptsms" {{ ($configs['sms']['sms_provider']['value'] ?? '') === 'fptsms' ? 'selected' : '' }}>FPT Telecom SMS Gateway</option>
                        <option value="mock_sandbox" {{ ($configs['sms']['sms_provider']['value'] ?? '') === 'mock_sandbox' ? 'selected' : '' }}>Cổng Giả Lập Nội Bộ (Sandbox Test)</option>
                    </select>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['sms']['sms_provider']['help'] }}
                    </small>
                </div>

                <!-- Brandname -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['sms']['sms_brandname']['label'] }}
                    </label>
                    <input type="text" name="sms_brandname" value="{{ $configs['sms']['sms_brandname']['value'] ?? '' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" maxlength="11" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['sms']['sms_brandname']['help'] }}
                    </small>
                </div>

                <!-- API Key -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['sms']['sms_api_key']['label'] }}
                    </label>
                    <input type="text" name="sms_api_key" value="{{ $configs['sms']['sms_api_key']['value'] ?? '' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['sms']['sms_api_key']['help'] }}
                    </small>
                </div>

                <!-- Secret Key -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['sms']['sms_secret_key']['label'] }}
                    </label>
                    <div style="position: relative;">
                        <input type="password" name="sms_secret_key" value="{{ $configs['sms']['sms_secret_key']['value'] ?? '' }}" 
                               class="mayhen_admin_form_input mayhen_password_field" style="width: 100%; padding: 0.55rem 2.2rem 0.55rem 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                        <button type="button" class="mayhen_toggle_pwd" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--admin-text-muted); font-size: 0.8rem;">
                            Hiện
                        </button>
                    </div>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['sms']['sms_secret_key']['help'] }} (Giữ nguyên •••••••• nếu không đổi).
                    </small>
                </div>

                <!-- Message Type -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['sms']['sms_type']['label'] }}
                    </label>
                    <select name="sms_type" class="mayhen_admin_form_select" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px; background: #fff;">
                        <option value="cskh_brandname" {{ ($configs['sms']['sms_type']['value'] ?? '') === 'cskh_brandname' ? 'selected' : '' }}>CSKH Thương Hiệu (Chăm sóc khách hàng & Lời mời)</option>
                        <option value="otp_brandname" {{ ($configs['sms']['sms_type']['value'] ?? '') === 'otp_brandname' ? 'selected' : '' }}>OTP Ưu Tiên Cao (Mã xác thực nhanh)</option>
                    </select>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['sms']['sms_type']['help'] }}
                    </small>
                </div>

                <!-- Đơn giá tham chiếu -->
                <div>
                    <label class="mayhen_admin_form_label" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.4rem; color: var(--admin-charcoal);">
                        {{ $configs['sms']['sms_cost_per_message']['label'] }}
                    </label>
                    <input type="number" name="sms_cost_per_message" value="{{ $configs['sms']['sms_cost_per_message']['value'] ?? '650' }}" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required min="0" step="50">
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        {{ $configs['sms']['sms_cost_per_message']['help'] }}
                    </small>
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--admin-border-color); display: flex; justify-content: flex-end;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 0.65rem 1.5rem; font-size: 0.9rem;">
                    Lưu Cấu Hình Cổng SMS Brandname
                </button>
            </div>
        </form>
    </div>
    @endif

    @if($activeTab === 'sandbox')
    <!-- TAB 3: SANDBOX DISPATCH VERIFICATION CONSOLE -->
    <div style="display: grid; grid-template-columns: 360px 1fr; gap: 1.5rem; align-items: flex-start;">
        <!-- Form Gửi Tin Thử Nghiệm -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: var(--admin-charcoal);">
                Gửi Tin Thử Nghiệm Trực Tiếp
            </h3>
            <p style="margin: 0 0 1.25rem 0; color: var(--admin-text-muted); font-size: 0.85rem;">
                Nhập số điện thoại để kiểm định thời gian thực luồng chuyển phát và đo độ trễ mạng viễn thông.
            </p>

            <form action="{{ route('admin.settings.notifications.send_test') }}" method="POST">
                @csrf

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                        Chọn Kênh Chuyển Phát <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="channel" class="mayhen_admin_form_select" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px; background: #fff;" required>
                        <option value="zalo_zns">Zalo ZNS (Zalo Notification Service)</option>
                        <option value="sms_brandname">SMS Brandname Viễn Thông</option>
                    </select>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                        Số Điện Thoại Nhận Tin <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="recipient" placeholder="Ví dụ: 0988123456" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">
                        Hệ thống tự động che PII (098***3456) khi lưu sổ nhật ký theo Luật 91/2025/QH15.
                    </small>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                        Mẫu Nội Dung Kiểm Thử <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="template_type" class="mayhen_admin_form_select" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px; background: #fff;" required>
                        <option value="wedding_invite">Thiệp Cưới Online: Minh Đức & Lan Chi</option>
                        <option value="rsvp_confirm">Xác Nhận Tham Dự RSVP Thành Công</option>
                        <option value="test_otp">Mã Xác Thực OTP Thử Nghiệm (123456)</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="width: 100%; padding: 0.7rem; font-size: 0.9rem; font-weight: 600;">
                    Thực Hiện Gửi Thử Nghiệm
                </button>
            </form>
        </div>

        <!-- Bảng Nhật Ký Kiểm Thử Gần Nhất -->
        <div class="mayhen_admin_card" style="padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--admin-charcoal);">
                    Sổ Nhật Ký Kiểm Thử Gần Nhất ({{ $testLogs->count() }})
                </h3>
                <span style="font-size: 0.8rem; color: var(--admin-text-muted);">
                    Lưu trữ minh bạch các lượt xác minh Gateway
                </span>
            </div>

            <div class="mayhen_admin_table_container" style="overflow-x: auto;">
                <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--admin-border-color); text-align: left; background: var(--admin-bg-soft);">
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Thời Gian</th>
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Kênh / Gateway</th>
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Người Nhận</th>
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Mẫu Tin</th>
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Trạng Thái</th>
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Độ Trễ</th>
                            <th style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted); font-weight: 600;">Người Thử</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($testLogs as $log)
                        <tr style="border-bottom: 1px solid var(--admin-border-color);">
                            <td style="padding: 0.65rem 0.75rem; white-space: nowrap; color: var(--admin-text-muted);">
                                {{ $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                            </td>
                            <td style="padding: 0.65rem 0.75rem; font-weight: 600;">
                                <span class="mayhen_admin_badge {{ $log->channel === 'zalo_zns' ? 'mayhen_admin_badge--primary' : 'mayhen_admin_badge--gold' }}" style="font-size: 0.75rem;">
                                    {{ $log->channel_label }}
                                </span>
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 2px;">
                                    {{ strtoupper($log->provider) }}
                                </div>
                            </td>
                            <td style="padding: 0.65rem 0.75rem; font-family: monospace; font-weight: 600; color: var(--admin-charcoal);">
                                {{ $log->recipient }}
                            </td>
                            <td style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted);">
                                {{ $log->template_code ?: 'Mặc định' }}
                            </td>
                            <td style="padding: 0.65rem 0.75rem;">
                                <span class="mayhen_admin_badge {{ $log->status_badge_color }}" style="font-size: 0.75rem;">
                                    {{ $log->status_label }}
                                </span>
                            </td>
                            <td style="padding: 0.65rem 0.75rem; white-space: nowrap; color: {{ $log->latency_ms < 100 ? '#059669' : '#D97706' }}; font-weight: 600;">
                                {{ $log->latency_ms }} ms
                            </td>
                            <td style="padding: 0.65rem 0.75rem; color: var(--admin-text-muted);">
                                {{ $log->dispatcher?->name ?: 'Hệ thống' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 2.5rem; text-align: center; color: var(--admin-text-muted);">
                                Chưa có nhật ký thử nghiệm nào được ghi nhận. Hãy gửi tin kiểm thử đầu tiên ở form bên cạnh.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Xử lý bật/tắt hiển thị mật khẩu
    document.querySelectorAll('.mayhen_toggle_pwd').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const input = this.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = 'Ẩn';
            } else {
                input.type = 'password';
                this.textContent = 'Hiện';
            }
        });
    });
});
</script>
@endsection
