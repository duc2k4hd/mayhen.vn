@extends('client.pages.account.layout')

@php
    $pageTitle = 'Thiết Lập Thiệp Thông Minh Theo Nhóm Khách';
@endphp

@section('account_breadcrumb', 'Thiệp Theo Nhóm Khách')

@section('account_content')
    <div style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9C7A4A; font-weight: 700; margin-bottom: 6px;">
                    <span>✦</span>
                    <span>Mục 2.3 DOCX & Section 28, 29 PRD V3</span>
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1F1D1A; margin: 0 0 6px 0;">
                    Thiệp Thông Minh Theo Nhóm Khách (Smart Group Router)
                </h2>
                <p style="font-size: 13px; color: #7A726A; margin: 0; line-height: 1.5; max-width: 800px;">
                    Phân tuyến thông minh nội dung thiệp cưới cho từng đối tượng quan khách. Khách dự Lễ Gia Tiên sẽ thấy lịch trình bái đường buổi sáng; đồng nghiệp và bạn bè chỉ thấy giờ đón khách tiệc tối, tránh nhầm lẫn giờ giấc trọng đại.
                </p>
            </div>

            <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <span>Về Sổ Khách Mời</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #E8F5E9; border: 1px solid #A5D6A7; color: #2E7D32; padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('account.smart_groups.update', $invitation->id) }}" method="POST">
        @csrf

        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach($presetGroups as $key => $group)
                @php
                    $grpConfig = $smartGroupsConfig[$key] ?? [];
                    $greetingVal = $grpConfig['greeting'] ?? $group['default_greeting'];
                    $titleVal = $grpConfig['title'] ?? ('Thư Mời — ' . $group['name']);
                    $allowedEventIds = $grpConfig['allowed_event_ids'] ?? [];
                @endphp
                <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 24px;">{{ $group['icon'] }}</span>
                            <div>
                                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 2px 0;">
                                    {{ $group['name'] }}
                                </h3>
                                <p style="font-size: 12px; color: #7A726A; margin: 0;">{{ $group['description'] }}</p>
                            </div>
                        </div>

                        {{-- Nút xem thử Rehearsal cho nhóm này --}}
                        <a 
                            href="{{ route('invitation.show', ['slug' => $invitation->slug, 'rehearsal' => 1, 'rehearsal_group' => $group['name']]) }}" 
                            target="_blank" 
                            class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm"
                            style="font-size: 12px;"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <span>Xem Thử Góc Nhìn Nhóm Này</span>
                        </a>
                    </div>

                    {{-- 1. Phân Tuyến Sự Kiện (Events Scope) --}}
                    <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px; margin-bottom: 14px;">
                        <span style="display: block; font-size: 12px; font-weight: 700; color: #4A433C; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                            Sự Kiện Hiển Thị Cho Nhóm Này (Event Scope):
                        </span>

                        @if($events->isNotEmpty())
                            <div style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach($events as $ev)
                                    @php
                                        // Mặc định nếu chưa cấu hình thì check hết
                                        $isChecked = empty($allowedEventIds) || in_array((int) $ev->id, array_map('intval', $allowedEventIds), true);
                                    @endphp
                                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1F1D1A; cursor: pointer;">
                                        <input 
                                            type="checkbox" 
                                            name="smart_groups[{{ $key }}][allowed_event_ids][]" 
                                            value="{{ $ev->id }}"
                                            {{ $isChecked ? 'checked' : '' }}
                                            style="accent-color: #9C7A4A; width: 16px; height: 16px;"
                                        >
                                        <span>
                                            <strong>{{ $ev->name }}</strong>
                                            <span style="color: #7A726A; font-size: 12px;">({{ $ev->event_time ?: 'Chưa đặt giờ' }} tại {{ $ev->venue_name ?: 'Sảnh tiệc' }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div style="font-size: 12px; color: #8C827A; font-style: italic;">
                                Chưa có sự kiện con nào được tạo. Nhóm này sẽ hiển thị thông tin ngày tiệc chính của đám cưới.
                            </div>
                        @endif
                    </div>

                    {{-- 2. Lời Mời Tùy Biến Cho Nhóm --}}
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                            <label style="font-size: 12px; font-weight: 600; color: #4A433C;">
                                Lời Chúc / Thông Điệp Riêng Gửi Nhóm Này:
                            </label>
                            <span style="font-size: 11px; color: #9C7A4A;">Hỗ trợ: {guest_name}, {salutation}, {couple_name}</span>
                        </div>
                        <input 
                            type="text" 
                            name="smart_groups[{{ $key }}][title]" 
                            value="{{ $titleVal }}"
                            placeholder="Tiêu đề thông điệp..."
                            style="width: 100%; padding: 8px 12px; border: 1px solid #D9D2C7; border-radius: 6px; font-size: 12px; margin-bottom: 6px; box-sizing: border-box;"
                        >
                        <textarea 
                            name="smart_groups[{{ $key }}][greeting]" 
                            rows="2"
                            style="width: 100%; padding: 8px 12px; border: 1px solid #D9D2C7; border-radius: 6px; font-size: 12px; box-sizing: border-box; font-family: inherit; line-height: 1.4;"
                        >{{ $greetingVal }}</textarea>
                    </div>
                </div>
            @endforeach

            {{-- Nút Lưu Cấu Hình --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 10px;">
                <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline">
                    <span>Quay Lại</span>
                </a>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_gold" style="padding: 10px 24px;">
                    <span>Lưu Cấu Hình Phân Tuyến Nhóm</span>
                </button>
            </div>
        </div>
    </form>
@endsection

