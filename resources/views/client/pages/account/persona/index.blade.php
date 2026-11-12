@extends('client.pages.account.layout')

@php
    $pageTitle = 'Studio Cá Nhân Hóa Lời Ngỏ Theo Nhóm Khách — Mây Hẹn';
    $groomName = $wedding->groom_name ?? 'Chú Rể';
    $brideName = $wedding->bride_name ?? 'Cô Dâu';
    $coupleName = $groomName . ' & ' . $brideName;
    $weddingDateStr = $wedding && $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : date('d/m/Y');
    $firstGroupKey = array_key_first($groups) ?? 'Bạn Bè & Bạn Thân';
@endphp

@section('account_content')
<div class="mayhen_persona_studio">
    {{-- Thanh điều hướng trên cùng --}}
    <div class="mayhen_persona_topbar">
        <a href="{{ route('account.invitations') }}" class="mayhen_persona_back_link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Về Danh Sách Thiệp Cưới</span>
        </a>

        <div class="mayhen_persona_top_actions">
            <a href="{{ route('invitation.show', ['slug' => $invitation->slug, 'rehearsal' => 1, 'group' => $firstGroupKey]) }}" target="_blank" id="rehearsalLinkBtn" class="mayhen_persona_btn mayhen_persona_btn_gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <span>Xem Thử Nhóm Này Ngoài Trình Duyệt</span>
            </a>
        </div>
    </div>

    {{-- Header giới thiệu Studio --}}
    <header class="mayhen_persona_header">
        <div class="mayhen_persona_badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Tailored Guest Persona Messages • IDEA-A01 &amp; Mục 2.3 DOCX</span>
        </div>
        <h1 class="mayhen_persona_title">Studio Cá Nhân Hóa Lời Ngỏ Theo Nhóm Khách</h1>
        <p class="mayhen_persona_subtitle">
            Gửi thiệp cho bạn thân với văn phong hóm hỉnh gần gũi, nhưng gửi cho đối tác và họ hàng với lời mời cung kính trang trọng trên cùng một mẫu thiệp cưới. Khi khách mở thiệp, hệ thống tự động nhận diện và hiển thị đúng thông điệp dành riêng cho họ.
        </p>
    </header>

    @if(session('status'))
        <div class="mayhen_persona_alert_success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- 4 Thẻ KPI thống kê mức độ bao phủ --}}
    <div class="mayhen_persona_kpi_grid">
        <div class="mayhen_persona_kpi_card">
            <div class="mayhen_persona_kpi_label">Tỷ Lệ Phủ Sóng Cá Nhân Hóa</div>
            <div class="mayhen_persona_kpi_value">{{ $stats['coverage_percent'] }}%</div>
            <div class="mayhen_persona_kpi_hint">Khách mời được nhận lời ngỏ riêng biệt</div>
        </div>

        <div class="mayhen_persona_kpi_card">
            <div class="mayhen_persona_kpi_label">Nhóm Đã Có Lời Ngỏ Riêng</div>
            <div class="mayhen_persona_kpi_value">{{ $stats['customized_groups_count'] }} <span class="mayhen_persona_kpi_sub">/ {{ $stats['total_groups'] }} nhóm</span></div>
            <div class="mayhen_persona_kpi_hint">Các nhóm khác sẽ dùng lời ngỏ mặc định</div>
        </div>

        <div class="mayhen_persona_kpi_card">
            <div class="mayhen_persona_kpi_label">Khách Mời Thụ Hưởng</div>
            <div class="mayhen_persona_kpi_value">{{ $stats['covered_guests_count'] }} <span class="mayhen_persona_kpi_sub">/ {{ $stats['total_guests'] }} khách</span></div>
            <div class="mayhen_persona_kpi_hint">Dựa trên danh sách khách mời thực tế</div>
        </div>

        <div class="mayhen_persona_kpi_card">
            <div class="mayhen_persona_kpi_label">Trạng Thái Toàn Cục</div>
            <div class="mayhen_persona_kpi_value">
                @if($isEnabled)
                    <span class="mayhen_persona_status_pill mayhen_persona_status_active">Đang Bật</span>
                @else
                    <span class="mayhen_persona_status_pill mayhen_persona_status_inactive">Đang Tắt</span>
                @endif
            </div>
            <div class="mayhen_persona_kpi_hint">Quyết định việc hiển thị lời ngỏ trên thiệp</div>
        </div>
    </div>

    {{-- Khối Làm Việc Chính 2 Cột: Editor & Live Phone Simulator --}}
    <div class="mayhen_persona_workspace">
        {{-- CỘT TRÁI: BỘ SOẠN THẢO PERSONA THEO TỪNG NHÓM --}}
        <div class="mayhen_persona_editor_col">
            <form action="{{ route('account.persona.update', $invitation->id) }}" method="POST" id="formPersonaStudio">
                @csrf

                {{-- Khối Bật/Tắt Toàn Cục --}}
                <div class="mayhen_persona_toggle_box">
                    <div class="mayhen_persona_toggle_info">
                        <strong class="mayhen_persona_toggle_title">Kích Hoạt Cá Nhân Hóa Lời Ngỏ Cho Thiệp Này</strong>
                        <p class="mayhen_persona_toggle_desc">Khi bật, khách mời có liên kết cá nhân thuộc từng nhóm sẽ thấy đúng lời ngỏ tương ứng. Khách vãng lai sẽ thấy lời ngỏ chung của thiệp.</p>
                    </div>
                    <label class="mayhen_persona_switch">
                        <input type="checkbox" name="enabled" value="1" {{ $isEnabled ? 'checked' : '' }} id="personaEnabledToggle">
                        <span class="slider round"></span>
                    </label>
                </div>

                {{-- Danh sách Tabs Nhóm Khách --}}
                <div class="mayhen_persona_groups_section">
                    <div class="mayhen_persona_tabs_head">
                        <span class="mayhen_persona_tabs_title">Chọn Nhóm Khách Để Biên Tập:</span>
                        <span class="mayhen_persona_tabs_count">({{ count($groups) }} nhóm)</span>
                    </div>

                    <div class="mayhen_persona_tabs_nav" role="tablist">
                        @foreach($groups as $gName => $group)
                            @php
                                $isActive = ($gName === $firstGroupKey);
                            @endphp
                            <button type="button" 
                                    class="mayhen_persona_tab_btn {{ $isActive ? 'active' : '' }} {{ $group['is_customized'] ? 'has_custom' : '' }}" 
                                    data-group-name="{{ $gName }}"
                                    data-guest-count="{{ $group['guest_count'] }}"
                                    data-title="{{ $group['title'] }}"
                                    data-subtitle="{{ $group['subtitle'] }}"
                                    data-content="{{ $group['content'] }}"
                                    role="tab"
                                    aria-selected="{{ $isActive ? 'true' : 'false' }}">
                                <span class="mayhen_persona_tab_name">{{ $gName }}</span>
                                <span class="mayhen_persona_tab_badge" title="{{ $group['guest_count'] }} khách mời trong nhóm này">{{ $group['guest_count'] }} khách</span>
                                @if($group['is_customized'])
                                    <span class="mayhen_persona_custom_indicator" title="Đã có lời ngỏ riêng">●</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Khung Biên Tập Chi Tiết Của Nhóm Đang Chọn --}}
                <div class="mayhen_persona_panel_card">
                    <div class="mayhen_persona_panel_head">
                        <div>
                            <span class="mayhen_persona_panel_tag">Đang Biên Tập Cho Nhóm:</span>
                            <h3 class="mayhen_persona_panel_group_title" id="currentGroupTitle">{{ $firstGroupKey }}</h3>
                        </div>
                        <div class="mayhen_persona_panel_actions">
                            <button type="button" id="btnResetGroup" class="mayhen_persona_btn_text_danger" title="Xóa thông điệp riêng và dùng thông điệp mặc định">
                                Đặt Lại Mặc Định
                            </button>
                        </div>
                    </div>

                    {{-- Form Fields Nhóm Hiện Tại (Sẽ đồng bộ qua JS cho input hidden của các nhóm) --}}
                    @foreach($groups as $gName => $group)
                        <div class="group-inputs-container" id="group-container-{{ md5($gName) }}" style="{{ $gName === $firstGroupKey ? 'display: block;' : 'display: none;' }}">
                            <div class="mayhen_persona_field">
                                <label class="mayhen_persona_label" for="title_{{ md5($gName) }}">
                                    Tiêu Đề Lời Ngỏ (Heading)
                                    <span class="mayhen_persona_label_hint">Ví dụ: Gửi Những Người Bạn Tuyệt Vời Của Tụi Mình</span>
                                </label>
                                <input type="text" 
                                       name="groups[{{ $gName }}][title]" 
                                       id="title_{{ md5($gName) }}" 
                                       value="{{ $group['title'] }}" 
                                       class="mayhen_persona_input persona-field-title" 
                                       data-target-group="{{ $gName }}"
                                       placeholder="Tiêu đề trang trọng hoặc gần gũi...">
                            </div>

                            <div class="mayhen_persona_field">
                                <label class="mayhen_persona_label" for="subtitle_{{ md5($gName) }}">
                                    Phụ Đề Lời Ngỏ (Subtitle / Kicker)
                                    <span class="mayhen_persona_label_hint">Ví dụ: Kỷ Niệm Thanh Xuân &amp; Đồng Hành</span>
                                </label>
                                <input type="text" 
                                       name="groups[{{ $gName }}][subtitle]" 
                                       id="subtitle_{{ md5($gName) }}" 
                                       value="{{ $group['subtitle'] }}" 
                                       class="mayhen_persona_input persona-field-subtitle" 
                                       data-target-group="{{ $gName }}"
                                       placeholder="Phụ đề nhẹ nhàng...">
                            </div>

                            <div class="mayhen_persona_field">
                                <label class="mayhen_persona_label" for="content_{{ md5($gName) }}">
                                    Nội Dung Lời Nhắn Gửi &amp; Tâm Sự
                                    <span class="mayhen_persona_label_hint">Có thể dùng các từ khóa: <code>{guest_name}</code>, <code>{groom_name}</code>, <code>{bride_name}</code>, <code>{wedding_date}</code></span>
                                </label>
                                <textarea name="groups[{{ $gName }}][content]" 
                                          id="content_{{ md5($gName) }}" 
                                          rows="6" 
                                          class="mayhen_persona_textarea persona-field-content" 
                                          data-target-group="{{ $gName }}"
                                          placeholder="Nhập tâm sự, lời nhắn gửi riêng dành cho khách thuộc nhóm này...">{{ $group['content'] }}</textarea>
                            </div>
                        </div>
                    @endforeach

                    {{-- Khay Nút Chèn Nhanh Placeholder --}}
                    <div class="mayhen_persona_placeholders_bar">
                        <span class="mayhen_persona_bar_label">Chèn Nhanh Từ Khóa Tự Động:</span>
                        <div class="mayhen_persona_tags">
                            <button type="button" class="btn-insert-tag" data-tag="{guest_name}" title="Tên riêng của khách mời">{guest_name} (Tên khách)</button>
                            <button type="button" class="btn-insert-tag" data-tag="{groom_name}" title="Tên chú rể">{groom_name}</button>
                            <button type="button" class="btn-insert-tag" data-tag="{bride_name}" title="Tên cô dâu">{bride_name}</button>
                            <button type="button" class="btn-insert-tag" data-tag="{wedding_date}" title="Ngày cưới">{wedding_date}</button>
                        </div>
                    </div>

                    {{-- Thư Viện Văn Mẫu Quiet Luxury Gợi Ý --}}
                    <div class="mayhen_persona_presets_box">
                        <div class="mayhen_persona_presets_head">
                            <span class="mayhen_persona_presets_icon">✨</span>
                            <strong>Bộ Sưu Tập Văn Mẫu Quiet Luxury Gợi Ý (Bấm để áp dụng nhanh):</strong>
                        </div>
                        <div class="mayhen_persona_presets_grid">
                            @foreach($presets as $pKey => $p)
                                <div class="mayhen_persona_preset_item" 
                                     data-preset-key="{{ $pKey }}"
                                     data-title="{{ $p['title'] }}"
                                     data-subtitle="{{ $p['subtitle'] }}"
                                     data-content="{{ $p['content'] }}">
                                    <div class="mayhen_persona_preset_top">
                                        <span class="mayhen_persona_preset_icon">{{ $p['icon'] }}</span>
                                        <strong class="mayhen_persona_preset_name">{{ $p['name'] }}</strong>
                                    </div>
                                    <p class="mayhen_persona_preset_excerpt">{{ Str::limit($p['content'], 75) }}</p>
                                    <button type="button" class="mayhen_persona_btn_use_preset">Áp Dụng Mẫu Này</button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Thanh Nút Hành Động Cuối Form --}}
                    <div class="mayhen_persona_submit_row">
                        <button type="submit" class="mayhen_persona_btn mayhen_persona_btn_save">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span>Lưu Toàn Bộ Lời Ngỏ Theo Nhóm</span>
                        </button>
                    </div>
                </div>
            </form>

            <form action="{{ route('account.persona.reset_group', $invitation->id) }}" method="POST" id="formResetGroupHidden" style="display: none;">
                @csrf
                <input type="hidden" name="group_name" id="resetGroupNameInput" value="{{ $firstGroupKey }}">
            </form>
        </div>

        {{-- CỘT PHẢI: LIVE PHONE MOCKUP SIMULATOR --}}
        <div class="mayhen_persona_simulator_col">
            <div class="mayhen_persona_simulator_sticky">
                <div class="mayhen_persona_sim_header">
                    <div>
                        <span class="mayhen_persona_sim_kicker">LIVE SIMULATOR</span>
                        <h4 class="mayhen_persona_sim_title">Mô Phỏng Trải Nghiệm Khách Mời</h4>
                    </div>
                    <span class="mayhen_persona_sim_tag" id="simCurrentGroupName">{{ $firstGroupKey }}</span>
                </div>

                <div class="mayhen_persona_phone_shell">
                    <div class="mayhen_persona_phone_speaker"></div>

                    <div class="mayhen_persona_phone_screen">
                        {{-- Bìa thiệp mô phỏng --}}
                        <div class="mayhen_persona_card_mockup">
                            <div class="mayhen_persona_mockup_crest">
                                <span>⚜️</span>
                            </div>
                            <div class="mayhen_persona_mockup_eyebrow">WEDDING INVITATION</div>
                            <h2 class="mayhen_persona_mockup_couple">{{ $coupleName }}</h2>
                            <div class="mayhen_persona_mockup_date">{{ $weddingDateStr }}</div>

                            <div class="mayhen_persona_mockup_divider">
                                <span>✦</span>
                            </div>

                            {{-- Khung Lời Ngỏ Riêng --}}
                            <div class="mayhen_persona_mockup_message_box">
                                <div class="mayhen_persona_mockup_badge">
                                    <span>THƯ GỬI RIÊNG</span>
                                </div>
                                <div class="mayhen_persona_mockup_msg_subtitle" id="mockupSubtitle">
                                    {{ $groups[$firstGroupKey]['subtitle'] ?: 'Kỷ Niệm Cùng Nhau' }}
                                </div>
                                <h3 class="mayhen_persona_mockup_msg_title" id="mockupTitle">
                                    {{ $groups[$firstGroupKey]['title'] ?: 'Gửi Người Bạn Tuyệt Vời' }}
                                </h3>
                                <div class="mayhen_persona_mockup_msg_body" id="mockupBody">
                                    @php
                                        $initialContent = $groups[$firstGroupKey]['content'] ?: 'Sự hiện diện của bạn là niềm vinh hạnh và hạnh phúc lớn lao của gia đình chúng mình!';
                                        $initialRendered = str_replace(
                                            ['{guest_name}', '{groom_name}', '{bride_name}', '{wedding_date}'],
                                            ['Nguyễn Hoàng Nam', $groomName, $brideName, $weddingDateStr],
                                            $initialContent
                                        );
                                    @endphp
                                    {{ $initialRendered }}
                                </div>

                                <div class="mayhen_persona_mockup_seal">
                                    <div class="mayhen_persona_wax_seal_circle">M &amp; H</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mayhen_persona_sim_footer">
                    <span class="mayhen_persona_sim_tip">💡 Tên khách mời mô phỏng ví dụ: <strong>Nguyễn Hoàng Nam</strong></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ==========================================================================
   QUIET LUXURY PERSONA STUDIO (MÂY HẸN • IDEA-A01 & MỤC 2.3 DOCX)
   100% Pure Vanilla CSS — BEM Prefix mayhen_persona_*
   ========================================================================== */
.mayhen_persona_studio {
    font-family: 'Inter', -apple-system, sans-serif;
    color: #1F1D1A;
    max-width: 1240px;
    margin: 0 auto;
    padding: 0.5rem 0.5rem 3rem;
}

.mayhen_persona_topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.mayhen_persona_back_link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #4A453E;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: color 0.2s ease;
}
.mayhen_persona_back_link:hover {
    color: #C5A880;
}

.mayhen_persona_btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.2s ease;
}
.mayhen_persona_btn_gold {
    background: #C5A880;
    color: #FFFFFF;
}
.mayhen_persona_btn_gold:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}
.mayhen_persona_btn_save {
    background: #1F1D1A;
    color: #FFFDF9;
    padding: 0.9rem 2rem;
    font-size: 0.95rem;
    width: 100%;
}
.mayhen_persona_btn_save:hover {
    background: #9C7A4A;
}

.mayhen_persona_header {
    background: #FFFDF9;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 2rem 2.25rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 20px rgba(31, 29, 26, 0.03);
}
.mayhen_persona_badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(197, 168, 128, 0.12);
    color: #9C7A4A;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin-bottom: 0.8rem;
}
.mayhen_persona_title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 2rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 0.5rem 0;
    line-height: 1.2;
}
.mayhen_persona_subtitle {
    color: #4A453E;
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 0;
    max-width: 900px;
}

.mayhen_persona_alert_success {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #F0F7F0;
    border: 1px solid #C2E2C2;
    color: #2D6A2E;
    padding: 0.9rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    font-weight: 500;
}

/* 4 KPI Grid */
.mayhen_persona_kpi_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.mayhen_persona_kpi_card {
    background: #FFFDF9;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 1.25rem;
    box-shadow: 0 2px 10px rgba(31, 29, 26, 0.02);
}
.mayhen_persona_kpi_label {
    font-size: 0.78rem;
    color: #4A453E;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.4rem;
    font-weight: 600;
}
.mayhen_persona_kpi_value {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.85rem;
    font-weight: 700;
    color: #1F1D1A;
    line-height: 1.1;
    margin-bottom: 0.35rem;
}
.mayhen_persona_kpi_sub {
    font-size: 0.9rem;
    font-weight: 400;
    color: #7A756D;
}
.mayhen_persona_kpi_hint {
    font-size: 0.75rem;
    color: #7A756D;
    line-height: 1.4;
}
.mayhen_persona_status_pill {
    display: inline-block;
    padding: 0.2rem 0.65rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
}
.mayhen_persona_status_active {
    background: #E8F5E9;
    color: #2E7D32;
}
.mayhen_persona_status_inactive {
    background: #FFEBEE;
    color: #C62828;
}

/* Workspace 2 Cột */
.mayhen_persona_workspace {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.75rem;
    align-items: start;
}

/* Cột Trái */
.mayhen_persona_toggle_box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    background: #FFFDF9;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
.mayhen_persona_toggle_title {
    display: block;
    font-size: 0.95rem;
    color: #1F1D1A;
    margin-bottom: 0.25rem;
}
.mayhen_persona_toggle_desc {
    margin: 0;
    font-size: 0.82rem;
    color: #4A453E;
    line-height: 1.4;
}

/* Switch iOS */
.mayhen_persona_switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 26px;
    flex-shrink: 0;
}
.mayhen_persona_switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.mayhen_persona_switch .slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #D8CEBD;
    transition: .3s;
}
.mayhen_persona_switch .slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s;
}
.mayhen_persona_switch input:checked + .slider {
    background-color: #C5A880;
}
.mayhen_persona_switch input:checked + .slider:before {
    transform: translateX(20px);
}
.mayhen_persona_switch .slider.round {
    border-radius: 34px;
}
.mayhen_persona_switch .slider.round:before {
    border-radius: 50%;
}

/* Tabs Nav */
.mayhen_persona_groups_section {
    margin-bottom: 1.25rem;
}
.mayhen_persona_tabs_head {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.65rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #4A453E;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.mayhen_persona_tabs_count {
    color: #7A756D;
}
.mayhen_persona_tabs_nav {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
    scrollbar-width: thin;
}
.mayhen_persona_tab_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: #FFFDF9;
    border: 1px solid #EFE8DD;
    border-radius: 8px;
    padding: 0.65rem 1rem;
    font-size: 0.86rem;
    font-weight: 500;
    color: #4A453E;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.mayhen_persona_tab_btn:hover {
    border-color: #C5A880;
    color: #1F1D1A;
}
.mayhen_persona_tab_btn.active {
    background: #1F1D1A;
    color: #FFFDF9;
    border-color: #1F1D1A;
}
.mayhen_persona_tab_btn.active .mayhen_persona_tab_badge {
    background: rgba(255, 255, 255, 0.2);
    color: #FFFDF9;
}
.mayhen_persona_tab_badge {
    background: #EFE8DD;
    color: #4A453E;
    padding: 0.15rem 0.45rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
}
.mayhen_persona_custom_indicator {
    color: #C5A880;
    font-size: 0.75rem;
}

/* Panel Card */
.mayhen_persona_panel_card {
    background: #FFFDF9;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 1.75rem 2rem;
    box-shadow: 0 4px 15px rgba(31, 29, 26, 0.02);
}
.mayhen_persona_panel_head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #EFE8DD;
    margin-bottom: 1.5rem;
}
.mayhen_persona_panel_tag {
    font-size: 0.76rem;
    color: #7A756D;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    display: block;
    margin-bottom: 0.2rem;
}
.mayhen_persona_panel_group_title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0;
}
.mayhen_persona_btn_text_danger {
    background: none;
    border: none;
    color: #A03232;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    padding: 0.3rem 0.6rem;
    border-radius: 4px;
    transition: background 0.2s;
}
.mayhen_persona_btn_text_danger:hover {
    background: #FFEBEE;
}

/* Fields */
.mayhen_persona_field {
    margin-bottom: 1.25rem;
}
.mayhen_persona_label {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    color: #1F1D1A;
    margin-bottom: 0.4rem;
}
.mayhen_persona_label_hint {
    display: block;
    font-size: 0.76rem;
    font-weight: 400;
    color: #7A756D;
    margin-top: 0.15rem;
}
.mayhen_persona_label_hint code {
    background: #EFE8DD;
    padding: 0.1rem 0.35rem;
    border-radius: 4px;
    font-size: 0.76rem;
    color: #1F1D1A;
}
.mayhen_persona_input,
.mayhen_persona_textarea {
    width: 100%;
    background: #FFFFFF;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.92rem;
    color: #1F1D1A;
    font-family: inherit;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.mayhen_persona_input:focus,
.mayhen_persona_textarea:focus {
    outline: none;
    border-color: #C5A880;
    box-shadow: 0 0 0 3px rgba(197, 168, 128, 0.2);
}
.mayhen_persona_textarea {
    line-height: 1.6;
    resize: vertical;
}

/* Tags Insert Bar */
.mayhen_persona_placeholders_bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    background: #FAF8F5;
    border: 1px dashed #D8CEBD;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 1.5rem;
}
.mayhen_persona_bar_label {
    font-size: 0.78rem;
    font-weight: 600;
    color: #4A453E;
}
.mayhen_persona_tags {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}
.btn-insert-tag {
    background: #FFFDF9;
    border: 1px solid #D8CEBD;
    border-radius: 6px;
    padding: 0.25rem 0.6rem;
    font-size: 0.76rem;
    font-weight: 500;
    color: #1F1D1A;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-insert-tag:hover {
    border-color: #C5A880;
    background: #C5A880;
    color: #FFFFFF;
}

/* Presets Grid */
.mayhen_persona_presets_box {
    margin-bottom: 1.75rem;
    padding-top: 1.25rem;
    border-top: 1px solid #EFE8DD;
}
.mayhen_persona_presets_head {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.86rem;
    color: #1F1D1A;
    margin-bottom: 0.85rem;
}
.mayhen_persona_presets_grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}
.mayhen_persona_preset_item {
    background: #FFFFFF;
    border: 1px solid #EFE8DD;
    border-radius: 8px;
    padding: 0.85rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mayhen_persona_preset_item:hover {
    border-color: #C5A880;
    box-shadow: 0 2px 8px rgba(197, 168, 128, 0.1);
}
.mayhen_persona_preset_top {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 0.35rem;
}
.mayhen_persona_preset_icon {
    font-size: 1rem;
}
.mayhen_persona_preset_name {
    font-size: 0.85rem;
    color: #1F1D1A;
}
.mayhen_persona_preset_excerpt {
    font-size: 0.78rem;
    color: #4A453E;
    line-height: 1.45;
    margin: 0 0 0.65rem 0;
}
.mayhen_persona_btn_use_preset {
    align-self: flex-start;
    background: #FAF8F5;
    border: 1px solid #D8CEBD;
    color: #9C7A4A;
    font-size: 0.76rem;
    font-weight: 600;
    padding: 0.25rem 0.65rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.mayhen_persona_btn_use_preset:hover {
    background: #C5A880;
    color: #FFFFFF;
    border-color: #C5A880;
}

/* CỘT PHẢI: SIMULATOR */
.mayhen_persona_simulator_sticky {
    position: sticky;
    top: 2rem;
}
.mayhen_persona_sim_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}
.mayhen_persona_sim_kicker {
    font-size: 0.72rem;
    font-weight: 700;
    color: #C5A880;
    letter-spacing: 0.08em;
    display: block;
}
.mayhen_persona_sim_title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0;
}
.mayhen_persona_sim_tag {
    background: #1F1D1A;
    color: #FFFDF9;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
}

/* Khung Điện Thoại iPhone */
.mayhen_persona_phone_shell {
    width: 100%;
    max-width: 380px;
    background: #1F1D1A;
    border-radius: 36px;
    padding: 12px;
    box-shadow: 0 20px 40px rgba(31, 29, 26, 0.15);
    margin: 0 auto;
    position: relative;
    border: 3px solid #D8CEBD;
}
.mayhen_persona_phone_speaker {
    width: 60px;
    height: 4px;
    background: #4A453E;
    border-radius: 999px;
    margin: 6px auto 10px;
}
.mayhen_persona_phone_screen {
    background: #FAF8F5;
    border-radius: 26px;
    min-height: 480px;
    overflow-y: auto;
    padding: 1.5rem 1.25rem 2rem;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

/* Thẻ Thiệp Mô Phỏng */
.mayhen_persona_card_mockup {
    text-align: center;
}
.mayhen_persona_mockup_crest {
    font-size: 1.4rem;
    margin-bottom: 0.4rem;
}
.mayhen_persona_mockup_eyebrow {
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    color: #C5A880;
    font-weight: 600;
    margin-bottom: 0.4rem;
}
.mayhen_persona_mockup_couple {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.6rem;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 0.25rem 0;
    line-height: 1.2;
}
.mayhen_persona_mockup_date {
    font-size: 0.76rem;
    color: #7A756D;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}
.mayhen_persona_mockup_divider {
    color: #C5A880;
    font-size: 0.8rem;
    margin-bottom: 1.25rem;
}

/* Khối Lời Ngỏ Trong Mô Phỏng */
.mayhen_persona_mockup_message_box {
    background: #FFFDF9;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 1.25rem 1rem 1.5rem;
    box-shadow: 0 4px 15px rgba(197, 168, 128, 0.08);
    position: relative;
}
.mayhen_persona_mockup_badge {
    display: inline-block;
    background: rgba(197, 168, 128, 0.15);
    color: #9C7A4A;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}
.mayhen_persona_mockup_msg_subtitle {
    font-size: 0.72rem;
    color: #C5A880;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.mayhen_persona_mockup_msg_title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.25rem;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 0.65rem 0;
    line-height: 1.3;
}
.mayhen_persona_mockup_msg_body {
    font-size: 0.82rem;
    line-height: 1.6;
    color: #2C2824;
    text-align: justify;
    text-justify: inter-word;
    margin-bottom: 1rem;
}
.mayhen_persona_mockup_seal {
    display: flex;
    justify-content: center;
}
.mayhen_persona_wax_seal_circle {
    width: 36px;
    height: 36px;
    background: radial-gradient(circle at 30% 30%, #D8CEBD, #9C7A4A);
    border-radius: 50%;
    color: #FFFDF9;
    font-family: 'Cormorant Garamond', serif;
    font-size: 0.78rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.mayhen_persona_sim_footer {
    text-align: center;
    margin-top: 0.75rem;
}
.mayhen_persona_sim_tip {
    font-size: 0.76rem;
    color: #7A756D;
}

/* Responsive */
@media (max-width: 992px) {
    .mayhen_persona_kpi_grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .mayhen_persona_workspace {
        grid-template-columns: 1fr;
    }
    .mayhen_persona_simulator_col {
        order: -1;
    }
    .mayhen_persona_simulator_sticky {
        position: static;
        max-width: 420px;
        margin: 0 auto 2rem;
    }
}
@media (max-width: 600px) {
    .mayhen_persona_kpi_grid {
        grid-template-columns: 1fr;
    }
    .mayhen_persona_presets_grid {
        grid-template-columns: 1fr;
    }
    .mayhen_persona_panel_card {
        padding: 1.25rem 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const groomName = @json($groomName);
    const brideName = @json($brideName);
    const weddingDateStr = @json($weddingDateStr);
    const demoGuestName = 'Nguyễn Hoàng Nam';
    const invitationSlug = @json($invitation->slug);

    let currentGroup = @json($firstGroupKey);

    const tabs = document.querySelectorAll('.mayhen_persona_tab_btn');
    const groupContainers = document.querySelectorAll('.group-inputs-container');
    const currentGroupTitle = document.getElementById('currentGroupTitle');
    const simCurrentGroupName = document.getElementById('simCurrentGroupName');
    const resetGroupNameInput = document.getElementById('resetGroupNameInput');
    const btnResetGroup = document.getElementById('btnResetGroup');
    const formResetGroupHidden = document.getElementById('formResetGroupHidden');
    const rehearsalLinkBtn = document.getElementById('rehearsalLinkBtn');

    const mockupTitle = document.getElementById('mockupTitle');
    const mockupSubtitle = document.getElementById('mockupSubtitle');
    const mockupBody = document.getElementById('mockupBody');

    // Hàm render text thay thế placeholder
    function replacePlaceholders(str) {
        if (!str) return '';
        return str
            .replace(/{guest_name}/g, demoGuestName)
            .replace(/{groom_name}/g, groomName)
            .replace(/{bride_name}/g, brideName)
            .replace(/{wedding_date}/g, weddingDateStr);
    }

    // Cập nhật Simulator từ input của nhóm hiện tại
    function updateSimulator() {
        const activeContainer = document.querySelector('.group-inputs-container[style*="display: block"]');
        if (!activeContainer) return;

        const titleInput = activeContainer.querySelector('.persona-field-title');
        const subtitleInput = activeContainer.querySelector('.persona-field-subtitle');
        const contentInput = activeContainer.querySelector('.persona-field-content');

        const titleVal = titleInput ? titleInput.value.trim() : '';
        const subtitleVal = subtitleInput ? subtitleInput.value.trim() : '';
        const contentVal = contentInput ? contentInput.value.trim() : '';

        mockupTitle.textContent = replacePlaceholders(titleVal) || 'Lời Ngỏ Mặc Định Của Thiệp';
        mockupSubtitle.textContent = replacePlaceholders(subtitleVal) || 'Kỷ Niệm Ngày Cưới';
        mockupBody.textContent = replacePlaceholders(contentVal) || 'Sự hiện diện của bạn là niềm vinh hạnh và hạnh phúc lớn lao của gia đình chúng mình!';
        simCurrentGroupName.textContent = currentGroup;

        // Cập nhật link rehearsal ngoài trình duyệt
        if (rehearsalLinkBtn) {
            rehearsalLinkBtn.href = `/thiep/${invitationSlug}?rehearsal=1&group=${encodeURIComponent(currentGroup)}`;
        }
    }

    // Chuyển Tab Nhóm
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            currentGroup = this.getAttribute('data-group-name');
            currentGroupTitle.textContent = currentGroup;
            resetGroupNameInput.value = currentGroup;

            // Ẩn tất cả container input, chỉ hiện container của nhóm này
            groupContainers.forEach(c => c.style.display = 'none');
            const targetContainer = document.querySelector(`.persona-field-title[data-target-group="${currentGroup}"]`)?.closest('.group-inputs-container');
            if (targetContainer) {
                targetContainer.style.display = 'block';
            }

            updateSimulator();
        });
    });

    // Lắng nghe sự kiện gõ phím trực tiếp (Live Typing)
    document.querySelectorAll('.persona-field-title, .persona-field-subtitle, .persona-field-content').forEach(input => {
        input.addEventListener('input', updateSimulator);
    });

    // Chèn Nhanh Thẻ Placeholder vào Textarea của nhóm hiện tại
    document.querySelectorAll('.btn-insert-tag').forEach(btn => {
        btn.addEventListener('click', function () {
            const tag = this.getAttribute('data-tag');
            const activeContainer = document.querySelector('.group-inputs-container[style*="display: block"]');
            if (!activeContainer) return;

            const textarea = activeContainer.querySelector('.persona-field-content');
            if (textarea) {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                textarea.value = text.substring(0, start) + tag + text.substring(end);
                textarea.focus();
                textarea.selectionStart = textarea.selectionEnd = start + tag.length;
                updateSimulator();
            }
        });
    });

    // Áp Dụng Preset Văn Mẫu
    document.querySelectorAll('.mayhen_persona_preset_item').forEach(item => {
        const useBtn = item.querySelector('.mayhen_persona_btn_use_preset');
        if (!useBtn) return;

        useBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const pTitle = item.getAttribute('data-title');
            const pSubtitle = item.getAttribute('data-subtitle');
            const pContent = item.getAttribute('data-content');

            const activeContainer = document.querySelector('.group-inputs-container[style*="display: block"]');
            if (!activeContainer) return;

            const titleInput = activeContainer.querySelector('.persona-field-title');
            const subtitleInput = activeContainer.querySelector('.persona-field-subtitle');
            const contentInput = activeContainer.querySelector('.persona-field-content');

            if (titleInput) titleInput.value = pTitle;
            if (subtitleInput) subtitleInput.value = pSubtitle;
            if (contentInput) contentInput.value = pContent;

            updateSimulator();
        });
    });

    // Đặt lại nhóm về mặc định
    if (btnResetGroup && formResetGroupHidden) {
        btnResetGroup.addEventListener('click', function () {
            if (confirm(`Bạn có chắc muốn đặt lại lời ngỏ của nhóm "${currentGroup}" về mặc định?`)) {
                formResetGroupHidden.submit();
            }
        });
    }

    // Khởi tạo ban đầu
    updateSimulator();
});
</script>
@endsection

