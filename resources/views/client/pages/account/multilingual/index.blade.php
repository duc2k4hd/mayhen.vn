@extends('client.pages.account.layout')

@php
    $pageTitle = 'Cài Đặt Đa Ngôn Ngữ Thiệp Cưới';
@endphp

@section('account_breadcrumb', 'Đa Ngôn Ngữ Thiệp Cưới')

@section('account_content')
    <div style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9C7A4A; font-weight: 700; margin-bottom: 6px;">
            <span>✦</span>
            <span>Mục 2.10 DOCX & Section 24, 29 PRD V3</span>
        </div>
        <h2 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1F1D1A; margin: 0 0 6px 0;">
            Đa Ngôn Ngữ Thiệp Cưới (Multilingual Invitation Suite)
        </h2>
        <p style="font-size: 13px; color: #7A726A; margin: 0; line-height: 1.5; max-width: 800px;">
            Chào đón bạn bè và đối tác quốc tế với thiệp cưới được dịch thuật chuẩn xác văn phong cưới Quiet Luxury (Tiếng Việt, Tiếng Anh, Tiếng Hàn, Tiếng Trung). Khách mời có thể chủ động chuyển đổi ngôn ngữ hoặc mở liên kết riêng biệt theo ngôn ngữ bản địa.
        </p>
    </div>

    @if(session('success'))
        <div style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('account.multilingual.update', $invitation->id) }}" method="POST">
        @csrf

        <div style="display: flex; flex-direction: column; gap: 24px;">
            {{-- Khối 1: Chọn ngôn ngữ kích hoạt & Ngôn ngữ mặc định --}}
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 4px 0;">
                    1. Ngôn Ngữ Được Hỗ Trợ Trên Thiệp Cưới
                </h3>
                <p style="font-size: 12px; color: #7A726A; margin: 0 0 16px 0;">
                    Khi kích hoạt từ 2 ngôn ngữ trở lên, nút chuyển đổi ngôn ngữ (Floating Switcher) sẽ tự động hiển thị tinh tế trên thiệp cưới.
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                    @foreach($supportedLocales as $code => $data)
                        @php
                            $isEnabled = in_array($code, $i18nConfig['enabled_locales'] ?? ['vi', 'en'], true);
                            $isDefault = ($code === ($i18nConfig['default_locale'] ?? 'vi'));
                            $isMandatoryVi = ($code === 'vi');
                        @endphp
                        <div style="border: 1.5px solid {{ $isEnabled ? '#C5A880' : '#EAE3D9' }}; border-radius: 12px; padding: 14px; background: {{ $isEnabled ? '#FAF7F2' : '#FFFFFF' }}; display: flex; flex-direction: column; justify-content: space-between; gap: 12px;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 26px;">{{ $data['flag'] }}</span>
                                    <div>
                                        <strong style="font-size: 14px; color: #1F1D1A; display: block;">{{ $data['native_name'] }}</strong>
                                        <span style="font-size: 12px; color: #7A726A;">{{ $data['name'] }} ({{ strtoupper($code) }})</span>
                                    </div>
                                </div>

                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: {{ $isMandatoryVi ? 'not-allowed' : 'pointer' }}; font-size: 12px;">
                                    <input 
                                        type="checkbox" 
                                        name="enabled_locales[]" 
                                        value="{{ $code }}" 
                                        {{ $isEnabled ? 'checked' : '' }}
                                        {{ $isMandatoryVi ? 'disabled' : '' }}
                                        style="accent-color: #9C7A4A; width: 16px; height: 16px;"
                                    >
                                    @if($isMandatoryVi)
                                        <input type="hidden" name="enabled_locales[]" value="vi">
                                        <span style="font-size: 10px; background: #EFE8DD; color: #6B5E4B; padding: 2px 6px; border-radius: 4px; font-weight: 600;">Gốc</span>
                                    @endif
                                </label>
                            </div>

                            <div style="border-top: 1px dashed #EAE3D9; padding-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                                <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 12px; color: #4A433C;">
                                    <input 
                                        type="radio" 
                                        name="default_locale" 
                                        value="{{ $code }}" 
                                        {{ $isDefault ? 'checked' : '' }}
                                        style="accent-color: #9C7A4A;"
                                    >
                                    <span>Đặt làm mặc định</span>
                                </label>

                                @if($isDefault)
                                    <span style="font-size: 11px; background: #FEF3C7; color: #92400E; padding: 2px 8px; border-radius: 12px; font-weight: 600;">Mặc Định</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Khối 2: Liên kết chia sẻ riêng biệt cho từng ngôn ngữ --}}
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 4px 0;">
                    2. Liên Kết Thiệp Theo Ngôn Ngữ Dành Cho Khách Quốc Tế
                </h3>
                <p style="font-size: 12px; color: #7A726A; margin: 0 0 16px 0;">
                    Gửi trực tiếp liên kết tương ứng cho sếp, đối tác hoặc bạn bè nước ngoài để họ mở thiệp bằng ngôn ngữ quen thuộc ngay từ giây đầu tiên.
                </p>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($localizedLinks as $code => $link)
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 14px; background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 10px; min-width: 160px;">
                                <span style="font-size: 20px;">{{ $link['flag'] }}</span>
                                <div>
                                    <strong style="font-size: 13px; color: #1F1D1A;">{{ $link['native_name'] }}</strong>
                                    <span style="font-size: 11px; color: #7A726A;">({{ $link['name'] }})</span>
                                </div>
                            </div>

                            <div style="flex: 1; min-width: 260px; font-family: monospace; font-size: 12px; color: #9C7A4A; background: #FFF; padding: 6px 10px; border-radius: 6px; border: 1px solid #EFE8DD; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $link['url'] }}
                            </div>

                            <button 
                                type="button" 
                                class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm"
                                style="padding: 5px 12px;"
                                onclick="navigator.clipboard.writeText('{{ $link['url'] }}'); this.innerText='Đã chép!'; setTimeout(() => this.innerText='Chép Link', 2000);"
                            >
                                Chép Link
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Khối 3: Tùy biến thông điệp song ngữ nếu muốn ghi đè --}}
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 4px 0;">
                            3. Tùy Biến Thông Điệp Đa Ngôn Ngữ Riêng Biệt (Tùy chọn)
                        </h3>
                        <p style="font-size: 12px; color: #7A726A; margin: 0;">
                            Hệ thống đã tự động dịch toàn bộ 10 section tiêu chuẩn. Bạn chỉ cần điền nếu muốn ghi đè thông điệp đặc biệt bằng Tiếng Anh.
                        </p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #4A433C; margin-bottom: 4px;">
                            Lời Mời Trân Trọng (Tiếng Anh - Cordially Invites):
                        </label>
                        <input 
                            type="text" 
                            name="overrides[en][hero.cordially_invite]" 
                            value="{{ $i18nConfig['overrides']['en']['hero.cordially_invite'] ?? '' }}"
                            placeholder="Mặc định: Together with their families, cordially invite you to celebrate their wedding"
                            style="width: 100%; padding: 10px 14px; border: 1px solid #D9D2C7; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                        >
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #4A433C; margin-bottom: 4px;">
                            Châm Ngôn Tình Yêu / Quote (Tiếng Anh - Love Story Quote):
                        </label>
                        <textarea 
                            name="overrides[en][story.quote_default]" 
                            rows="2"
                            placeholder="Mặc định: True love is looking in the same direction and walking hand in hand through the years."
                            style="width: 100%; padding: 10px 14px; border: 1px solid #D9D2C7; border-radius: 8px; font-size: 13px; box-sizing: border-box; font-family: inherit;"
                        >{{ $i18nConfig['overrides']['en']['story.quote_default'] ?? '' }}</textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #4A433C; margin-bottom: 4px;">
                            Lưu Ý Trang Phục (Tiếng Anh - Dresscode Note):
                        </label>
                        <input 
                            type="text" 
                            name="overrides[en][dresscode.note]" 
                            value="{{ $i18nConfig['overrides']['en']['dresscode.note'] ?? '' }}"
                            placeholder="Mặc định: Your elegant presence is our greatest honor"
                            style="width: 100%; padding: 10px 14px; border: 1px solid #D9D2C7; border-radius: 8px; font-size: 13px; box-sizing: border-box;"
                        >
                    </div>
                </div>
            </div>

            {{-- Thanh nút hành động --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 10px;">
                <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline">
                    <span>Quay Lại</span>
                </a>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_gold" style="padding: 10px 24px;">
                    <span>Lưu Cấu Hình Đa Ngôn Ngữ</span>
                </button>
            </div>
        </div>
    </form>
@endsection

