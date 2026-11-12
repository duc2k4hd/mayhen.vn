@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quản Trị Thiệp Cưới Đa Ngôn Ngữ & Bản Dịch — Mây Hẹn';
    $coupleName = ($wedding->groom_name ?? 'Chú Rể') . ' & ' . ($wedding->bride_name ?? 'Cô Dâu');
@endphp

@section('account_content')
<div class="mayhen_lang_studio">
    {{-- Thanh điều hướng --}}
    <div class="mayhen_lang_topbar">
        <a href="{{ route('account.invitations') }}" class="mayhen_lang_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Thiệp Cưới</span>
        </a>

        <div class="mayhen_lang_top_actions">
            <a href="{{ route('invitation.show', ['slug' => $invitation->slug, 'lang' => $defaultLocale]) }}" target="_blank" class="mayhen_lang_btn mayhen_lang_btn_gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                </svg>
                <span>Xem Thiệp Bản Ngữ ({{ strtoupper($defaultLocale) }})</span>
            </a>
        </div>
    </div>

    {{-- Header giới thiệu --}}
    <header class="mayhen_lang_header">
        <div class="mayhen_lang_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="2" y1="12" x2="22" y2="12"></line>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
            </svg>
            <span>Bilingual Multi-Language Switcher & Translator (IDEA-A04 & Mục 2.10 DOCX)</span>
        </div>
        <h1 class="mayhen_lang_title">Thiệp Cưới Đa Ngôn Ngữ &amp; Bản Dịch Quốc Tế</h1>
        <p class="mayhen_lang_subtitle">
            Tự động hiển thị ngôn ngữ phù hợp với thiết bị của bạn bè quốc tế (Tiếng Anh, Tiếng Hàn, Tiếng Trung) hoặc cho phép khách chạm nhẹ để đổi ngôn ngữ tức thời. Cặp đôi có thể tùy biến văn phong bản dịch cho từng phần của thiệp.
        </p>
    </header>

    <form id="formMultilingual" class="mayhen_lang_form">
        @csrf
        {{-- Section 1: Kích Hoạt Ngôn Ngữ & Ngôn Ngữ Mặc Định --}}
        <section class="mayhen_lang_locales_section">
            <h2 class="mayhen_lang_section_title">1. Chọn Ngôn Ngữ Kích Hoạt Cho Thiệp Cưới</h2>
            <p class="mayhen_lang_section_hint">Bật các ngôn ngữ bạn muốn khách mời có thể chuyển đổi khi xem thiệp:</p>

            <div class="mayhen_lang_locales_grid">
                @foreach($allLocales as $code => $loc)
                    @php
                        $isEnabled = in_array($code, $enabledLocales, true);
                        $isDefault = ($code === $defaultLocale);
                        $isVi = ($code === 'vi');
                    @endphp
                    <div class="mayhen_lang_locale_card {{ $isEnabled ? 'is_active' : '' }}" id="card-{{ $code }}">
                        <div class="mayhen_lang_locale_top">
                            <span class="mayhen_lang_flag">{{ $loc['flag'] }}</span>
                            <div class="mayhen_lang_switch_wrap">
                                <label class="mayhen_lang_switch" title="{{ $isVi ? 'Tiếng Việt là ngôn ngữ gốc bắt buộc' : 'Bật/Tắt ngôn ngữ này' }}">
                                    <input type="checkbox" name="enabled_locales[]" value="{{ $code }}" {{ $isEnabled ? 'checked' : '' }} {{ $isVi ? 'disabled' : '' }} class="locale-toggle" data-code="{{ $code }}">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="mayhen_lang_locale_names">
                            <div class="mayhen_lang_native_name">{{ $loc['native_name'] }}</div>
                            <div class="mayhen_lang_sub_name">{{ $loc['name'] }} ({{ strtoupper($code) }})</div>
                        </div>

                        <div class="mayhen_lang_locale_default_row">
                            <label class="mayhen_lang_radio_label">
                                <input type="radio" name="default_locale" value="{{ $code }}" {{ $isDefault ? 'checked' : '' }} class="locale-default-radio">
                                <span>Đặt làm mặc định</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Section 2: Tùy Biến Bản Dịch Từng Section --}}
        <section class="mayhen_lang_editor_section">
            <div class="mayhen_lang_editor_header">
                <div>
                    <h2 class="mayhen_lang_section_title">2. Tùy Biến Lời Văn Bản Dịch Cho Từng Khung Thiệp</h2>
                    <p class="mayhen_lang_section_hint">Hệ thống đã có sẵn từ điển văn phong cưới Quiet Luxury chuẩn mực. Bạn có thể gõ đè bản dịch riêng nếu muốn câu từ cá nhân hóa hơn.</p>
                </div>

                {{-- Tabs chọn ngôn ngữ biên tập --}}
                <div class="mayhen_lang_edit_tabs">
                    <button type="button" class="mayhen_lang_edit_tab active" data-tab="en">🇬🇧 Tiếng Anh</button>
                    <button type="button" class="mayhen_lang_edit_tab" data-tab="ko">🇰🇷 Tiếng Hàn</button>
                    <button type="button" class="mayhen_lang_edit_tab" data-tab="zh">🇨🇳 Tiếng Trung</button>
                </div>
            </div>

            {{-- Các Pane biên tập theo từng Locale --}}
            @foreach(['en' => 'Tiếng Anh', 'ko' => 'Tiếng Hàn', 'zh' => 'Tiếng Trung'] as $locCode => $locTitle)
                <div class="mayhen_lang_pane {{ $locCode === 'en' ? 'active' : '' }}" id="pane-{{ $locCode }}">
                    <div class="mayhen_lang_pane_banner">
                        <span>Đang tùy biến bản dịch: <strong>{{ $locTitle }} ({{ strtoupper($locCode) }})</strong></span>
                        <a href="{{ route('invitation.show', ['slug' => $invitation->slug, 'lang' => $locCode]) }}" target="_blank" class="mayhen_lang_btn_text">
                            Xem thử trên thiệp &rarr;
                        </a>
                    </div>

                    @foreach($sectionGroups as $secKey => $sec)
                        <div class="mayhen_lang_group_card">
                            <div class="mayhen_lang_group_head">
                                <span class="mayhen_lang_group_icon">✦</span>
                                <h3>{{ $sec['title'] }}</h3>
                            </div>

                            <div class="mayhen_lang_fields_grid">
                                @foreach($sec['keys'] as $key => $label)
                                    @php
                                        $defaultText = $dictionaries[$locCode][$key] ?? '';
                                        $viText = $dictionaries['vi'][$key] ?? '';
                                        $savedVal = $overrides[$locCode][$key] ?? '';
                                    @endphp
                                    <div class="mayhen_lang_field_row">
                                        <div class="mayhen_lang_field_meta">
                                            <div class="mayhen_lang_field_label">{{ $label }}</div>
                                            <div class="mayhen_lang_field_vi">Gốc (VI): <em>"{{ $viText }}"</em></div>
                                        </div>

                                        <div class="mayhen_lang_field_input_wrap">
                                            <input type="text" 
                                                   name="overrides[{{ $locCode }}][{{ $key }}]" 
                                                   value="{{ $savedVal }}" 
                                                   placeholder="{{ $defaultText }}" 
                                                   class="mayhen_lang_input">
                                            <div class="mayhen_lang_field_hint">Mặc định: {{ $defaultText }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            {{-- Thanh nút hành động Lưu --}}
            <div class="mayhen_lang_submit_bar">
                <button type="button" id="btnResetAll" class="mayhen_lang_btn mayhen_lang_btn_text">
                    Xóa bản dịch tùy biến &amp; về mặc định
                </button>

                <button type="submit" id="btnSaveConfig" class="mayhen_lang_btn mayhen_lang_btn_primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu Cài Đặt Đa Ngôn Ngữ</span>
                </button>
            </div>
        </section>
    </form>
</div>

<style>
/* CSS Định dạng BEM mayhen_lang_* Chuẩn Mây Hẹn Quiet Luxury */
.mayhen_lang_studio {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 20px 80px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #2c2824;
}

.mayhen_lang_topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_lang_back_link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #8c7355;
    font-weight: 500;
    font-size: 14px;
    text-decoration: none;
    transition: color 0.2s ease;
}

.mayhen_lang_back_link:hover { color: #5c4b37; }

.mayhen_lang_header {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
}

.mayhen_lang_badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fbf9f5;
    border: 1px solid #e2d7c5;
    color: #9c7a4a;
    padding: 6px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 16px;
}

.mayhen_lang_title {
    font-size: 28px;
    font-weight: 700;
    color: #1f1d1a;
    margin: 0 0 10px;
}

.mayhen_lang_subtitle {
    font-size: 15px;
    line-height: 1.6;
    color: #6a645a;
    margin: 0;
    max-width: 850px;
}

/* Section 1: Locales Grid */
.mayhen_lang_locales_section {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 32px;
}

.mayhen_lang_section_title {
    font-size: 18px;
    font-weight: 700;
    color: #1f1d1a;
    margin: 0 0 6px;
}

.mayhen_lang_section_hint {
    font-size: 14px;
    color: #6a645a;
    margin: 0 0 20px;
}

.mayhen_lang_locales_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.mayhen_lang_locale_card {
    background: #faf8f5;
    border: 1px solid #efe8dd;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: all 0.2s ease;
}

.mayhen_lang_locale_card.is_active {
    border-color: #b8935d;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(184, 147, 93, 0.12);
}

.mayhen_lang_locale_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mayhen_lang_flag { font-size: 28px; line-height: 1; }

/* Switch iOS Style */
.mayhen_lang_switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

.mayhen_lang_switch input { opacity: 0; width: 0; height: 0; }

.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #d8cebd;
    transition: .3s;
    border-radius: 24px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
    border-radius: 50%;
}

input:checked + .slider { background-color: #b8935d; }
input:checked + .slider:before { transform: translateX(20px); }
input:disabled + .slider { opacity: 0.6; cursor: not-allowed; }

.mayhen_lang_locale_names { margin-top: auto; }
.mayhen_lang_native_name { font-size: 16px; font-weight: 700; color: #1f1d1a; }
.mayhen_lang_sub_name { font-size: 12px; color: #8c8376; margin-top: 2px; }

.mayhen_lang_locale_default_row {
    border-top: 1px solid #efe8dd;
    padding-top: 10px;
}

.mayhen_lang_radio_label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #4a453e;
    cursor: pointer;
}

/* Section 2: Editor */
.mayhen_lang_editor_section {
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 16px;
    padding: 28px;
}

.mayhen_lang_editor_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_lang_edit_tabs {
    display: flex;
    gap: 6px;
    background: #fbf9f5;
    padding: 4px;
    border-radius: 8px;
    border: 1px solid #efe8dd;
}

.mayhen_lang_edit_tab {
    background: none;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13.5px;
    font-weight: 600;
    color: #6a645a;
    cursor: pointer;
    transition: all 0.2s ease;
}

.mayhen_lang_edit_tab.active {
    background: #ffffff;
    color: #1f1d1a;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.mayhen_lang_pane { display: none; }
.mayhen_lang_pane.active { display: block; }

.mayhen_lang_pane_banner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #faf8f5;
    border: 1px solid #e8decb;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13.5px;
    color: #4a453e;
    margin-bottom: 20px;
}

.mayhen_lang_group_card {
    background: #faf8f5;
    border: 1px solid #efe8dd;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.mayhen_lang_group_head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
}

.mayhen_lang_group_icon { color: #b8935d; font-size: 14px; }
.mayhen_lang_group_head h3 { font-size: 15px; font-weight: 700; color: #1f1d1a; margin: 0; }

.mayhen_lang_fields_grid {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.mayhen_lang_field_row {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 16px;
    align-items: center;
    background: #ffffff;
    border: 1px solid #efe8dd;
    border-radius: 8px;
    padding: 12px 16px;
}

.mayhen_lang_field_label { font-size: 13.5px; font-weight: 600; color: #1f1d1a; margin-bottom: 3px; }
.mayhen_lang_field_vi { font-size: 12px; color: #8c8376; }

.mayhen_lang_field_input_wrap { position: relative; }

.mayhen_lang_input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d8cebd;
    border-radius: 6px;
    font-size: 13.5px;
    background: #ffffff;
    color: #2c2824;
    box-sizing: border-box;
}

.mayhen_lang_input:focus {
    outline: none;
    border-color: #b8935d;
    box-shadow: 0 0 0 3px rgba(184, 147, 93, 0.15);
}

.mayhen_lang_field_hint { font-size: 11px; color: #9c9488; margin-top: 4px; }

/* Submit Bar */
.mayhen_lang_submit_bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #efe8dd;
    flex-wrap: wrap;
    gap: 16px;
}

.mayhen_lang_btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
}

.mayhen_lang_btn_gold { background: #b8935d; color: #ffffff; box-shadow: 0 4px 12px rgba(184, 147, 93, 0.25); }
.mayhen_lang_btn_gold:hover { background: #a37f4a; color: #ffffff; }

.mayhen_lang_btn_primary { background: #1f1d1a; color: #ffffff; }
.mayhen_lang_btn_primary:hover { background: #332f2b; }

.mayhen_lang_btn_text { background: none; border: none; color: #8c7355; font-size: 13.5px; font-weight: 500; cursor: pointer; text-decoration: none; }
.mayhen_lang_btn_text:hover { color: #5c4b37; text-decoration: underline; }

@media (max-width: 900px) {
    .mayhen_lang_locales_grid { grid-template-columns: repeat(2, 1fr); }
    .mayhen_lang_field_row { grid-template-columns: 1fr; gap: 8px; }
}

@media (max-width: 600px) {
    .mayhen_lang_locales_grid { grid-template-columns: 1fr; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Chuyển đổi Tab biên tập
    const tabs = document.querySelectorAll('.mayhen_lang_edit_tab');
    const panes = document.querySelectorAll('.mayhen_lang_pane');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const targetPane = document.getElementById('pane-' + this.getAttribute('data-tab'));
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // 2. Cập nhật thẻ khi Toggle ngôn ngữ
    const toggles = document.querySelectorAll('.locale-toggle');
    toggles.forEach(chk => {
        chk.addEventListener('change', function () {
            const code = this.getAttribute('data-code');
            const card = document.getElementById('card-' + code);
            if (card) {
                if (this.checked) {
                    card.classList.add('is_active');
                } else {
                    card.classList.remove('is_active');
                    // Nếu đang chọn ngôn ngữ này làm default mà bỏ bật, chuyển default về vi
                    const defRadio = card.querySelector('.locale-default-radio');
                    if (defRadio && defRadio.checked) {
                        const viRadio = document.querySelector('.locale-default-radio[value="vi"]');
                        if (viRadio) viRadio.checked = true;
                    }
                }
            }
        });
    });

    // 3. Xử lý lưu form cấu hình & bản dịch qua AJAX
    const form = document.getElementById('formMultilingual');
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btnSave = document.getElementById('btnSaveConfig');
            btnSave.disabled = true;
            btnSave.innerHTML = '<span>Đang lưu bản dịch...</span>';

            const formData = new FormData(form);
            const payload = {
                enabled_locales: [],
                default_locale: 'vi',
                overrides: {}
            };

            // Gom enabled_locales
            document.querySelectorAll('.locale-toggle:checked').forEach(c => {
                payload.enabled_locales.push(c.value);
            });
            // Luôn có vi
            if (!payload.enabled_locales.includes('vi')) {
                payload.enabled_locales.push('vi');
            }

            // Gom default_locale
            const checkedDefault = document.querySelector('.locale-default-radio:checked');
            if (checkedDefault) {
                payload.default_locale = checkedDefault.value;
            }

            // Gom overrides
            document.querySelectorAll('.mayhen_lang_input').forEach(inp => {
                const name = inp.getAttribute('name'); // overrides[en][hero.wedding_of]
                const val = inp.value.trim();
                if (val !== '') {
                    const match = name.match(/overrides\[([a-z]+)\]\[([^\]]+)\]/);
                    if (match) {
                        const loc = match[1];
                        const key = match[2];
                        if (!payload.overrides[loc]) payload.overrides[loc] = {};
                        payload.overrides[loc][key] = val;
                    }
                }
            });

            try {
                const res = await fetch("{{ route('account.multilingual.update', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    btnSave.innerHTML = '<span>✓ Đã lưu thành công</span>';
                    btnSave.style.background = '#2e7d32';
                    setTimeout(() => {
                        btnSave.disabled = false;
                        btnSave.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg><span>Lưu Cài Đặt Đa Ngôn Ngữ</span>`;
                        btnSave.style.background = '#1f1d1a';
                    }, 2500);
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu'));
                    btnSave.disabled = false;
                    btnSave.innerHTML = '<span>Lưu Cài Đặt Đa Ngôn Ngữ</span>';
                }
            } catch (err) {
                alert('Lỗi kết nối máy chủ');
                btnSave.disabled = false;
                btnSave.innerHTML = '<span>Lưu Cài Đặt Đa Ngôn Ngữ</span>';
            }
        });
    }

    // 4. Xóa bản dịch tùy biến & về mặc định
    const btnResetAll = document.getElementById('btnResetAll');
    if (btnResetAll) {
        btnResetAll.addEventListener('click', async function () {
            if (!confirm('Bạn có chắc chắn muốn xóa toàn bộ bản dịch tùy biến và sử dụng từ điển chuẩn của Mây Hẹn?')) {
                return;
            }
            try {
                const res = await fetch("{{ route('account.multilingual.reset', $invitation->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    document.querySelectorAll('.mayhen_lang_input').forEach(i => i.value = '');
                    alert(data.message);
                }
            } catch (err) {
                alert('Lỗi kết nối máy chủ');
            }
        });
    }
});
</script>
@endsection
