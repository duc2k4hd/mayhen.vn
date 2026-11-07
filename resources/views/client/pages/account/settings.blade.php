@extends('client.pages.account.layout')

@php
    $groom = $wedding->groom_name ?? 'Chú Rể';
    $bride = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = "Cài Đặt Thiệp: {$groom} & {$bride}";
    $isLocked = !empty($config['is_locked']);
@endphp

@section('account_breadcrumb')
    <a href="{{ route('account.invitations') }}" style="color: inherit; text-decoration: none;">Thiệp cưới</a>
    <span class="mayhen_account_breadcrumbs_sep">/</span>
    <span>Cài đặt</span>
@endsection

@section('account_content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Cài Đặt Thiệp: {{ $groom }} & {{ $bride }}
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Tùy chỉnh đường dẫn URL riêng, mật mã bảo vệ, tương tác RSVP và nhạc nền cho thiệp cưới của hai bạn.
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

    @if($isLocked)
        <div class="mayhen_account_alert mayhen_account_alert_danger" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <div>
                    <strong>Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên:</strong>
                    <span>{{ $config['lock_reason'] ?? 'Vui lòng liên hệ ban quản trị để được hỗ trợ mở khóa.' }}</span>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('account.invitations.settings.update', $invitation->id) }}" method="POST">
        @csrf

        <!-- Khối 1: Đường Dẫn & Xuất Bản -->
        <div class="mayhen_account_section" style="margin-bottom: 24px;">
            <div class="mayhen_account_section_header">
                <h3 class="mayhen_account_section_title">1. Đường Dẫn Thiệp & Trạng Thái Xuất Bản</h3>
                <p class="mayhen_account_section_desc">Thiết lập đường link độc quyền mang dấu ấn riêng của hai bạn.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                <div>
                    <label class="mayhen_account_form_label" for="setting-slug">Đường dẫn thiệp cưới trực tuyến (Slug) *</label>
                    <div style="display: flex; align-items: center; gap: 0; background: #FAF8F5; border: 1px solid #D8CEBD; border-radius: 10px; overflow: hidden;">
                        <span style="padding: 10px 14px; background: #EFE8DD; color: #5A534B; font-size: 13px; font-family: monospace; border-right: 1px solid #D8CEBD; white-space: nowrap;">
                            {{ url('/thiep') }}/
                        </span>
                        <input type="text" name="slug" id="setting-slug" value="{{ old('slug', $invitation->slug) }}" required pattern="^[a-z0-9\-]+$" class="mayhen_account_form_input" style="border: none; border-radius: 0; background: transparent; font-family: monospace; font-weight: 600; color: #9C7A4A;" placeholder="vd: dat-ngoc-2026">
                    </div>
                    <span style="font-size: 12px; color: #7A726A; display: block; margin-top: 4px;">
                        Chỉ dùng chữ cái thường không dấu (a-z), chữ số (0-9) và dấu gạch nối (-). Ví dụ: <code>thanh-dat-bich-ngoc</code>
                    </span>
                    <div style="margin-top: 8px; font-size: 12px; background: #FAF6F0; border: 1px dashed #E5DCCD; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span style="color: #6E665E;">
                            ✨ Muốn có đường dẫn ngắn gọn như <code>tuanlan.mayhen.vn</code> hoặc tên miền riêng <code>tuanlan.wedding</code>?
                        </span>
                        <a href="{{ route('account.invitations.domain', $invitation->id) }}" style="color: #9C7A4A; font-weight: 600; text-decoration: none; white-space: nowrap;">
                            Cấu hình Tên Miền &rarr;
                        </a>
                    </div>
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="setting-title">Tên nhận diện mẫu thiệp (Tiêu đề nội bộ)</label>
                    <input type="text" name="title" id="setting-title" value="{{ old('title', $invitation->title ?? 'Thiệp Cưới Chính Thức') }}" class="mayhen_account_form_input" placeholder="Ví dụ: Thiệp Báo Hỷ Nhà Trai, Thiệp Tiệc Cưới Sài Gòn...">
                </div>

                <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                    <div>
                        <strong style="font-size: 14px; color: #1F1D1A; display: block;">Trạng thái xuất bản công khai</strong>
                        <span style="font-size: 12px; color: #7A726A;">Bật chế độ này để quan khách có thể truy cập xem thiệp trực tuyến qua đường link.</span>
                    </div>
                    <label style="position: relative; display: inline-flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $invitation->is_published) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} style="width: 20px; height: 20px; accent-color: #C5A880; cursor: pointer;">
                    </label>
                </div>
            </div>
        </div>

        <!-- Khối 2: Bảo Mật Riêng Tư Bằng Mật Mã -->
        <div class="mayhen_account_section" style="margin-bottom: 24px;">
            <div class="mayhen_account_section_header">
                <h3 class="mayhen_account_section_title">2. Bảo Mật Riêng Tư Bằng Mật Mã (Mã PIN)</h3>
                <p class="mayhen_account_section_desc">Dành cho các cặp đôi mong muốn thiệp cưới chỉ mở cho những người thân thiết có mật mã.</p>
            </div>

            <div>
                <label class="mayhen_account_form_label" for="setting-password">Mật mã bảo vệ (Mã PIN mở thiệp)</label>
                <div style="max-width: 320px;">
                    <input type="text" name="password" id="setting-password" value="{{ old('password', $config['password'] ?? '') }}" maxlength="20" class="mayhen_account_form_input" placeholder="Để trống nếu muốn thiệp mở tự do">
                </div>
                <span style="font-size: 12px; color: #7A726A; display: block; margin-top: 6px; line-height: 1.5;">
                    💡 <strong>Gợi ý:</strong> Bạn có thể đặt mật mã ngắn dễ nhớ (ví dụ: ngày cưới <code>2512</code> hoặc số đuôi điện thoại). Để trống nếu muốn ai có link cũng xem được thiệp.
                </span>
            </div>
        </div>

        <!-- Khối 3: Tương Tác Lời Chúc & Xác Nhận Tham Dự (RSVP) -->
        <div class="mayhen_account_section" style="margin-bottom: 24px;">
            <div class="mayhen_account_section_header">
                <h3 class="mayhen_account_section_title">3. Tương Tác Quan Khách & Hạn Chót RSVP</h3>
                <p class="mayhen_account_section_desc">Kiểm soát việc tiếp nhận lời chúc phúc và xác nhận số lượng bàn tiệc.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="allow_wishes" value="1" {{ old('allow_wishes', $config['allow_wishes'] ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #C5A880; margin-top: 2px;">
                    <div>
                        <strong style="font-size: 14px; color: #1F1D1A; display: block;">Cho phép khách mời gửi lời chúc phúc (Sổ Lưu Bút)</strong>
                        <span style="font-size: 12px; color: #7A726A;">Khách mời có thể gửi những thông điệp tình cảm ngọt ngào trực tiếp trên trang thiệp cưới.</span>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="allow_rsvp" value="1" {{ old('allow_rsvp', $config['allow_rsvp'] ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #C5A880; margin-top: 2px;">
                    <div>
                        <strong style="font-size: 14px; color: #1F1D1A; display: block;">Cho phép khách mời xác nhận tham dự (RSVP)</strong>
                        <span style="font-size: 12px; color: #7A726A;">Hiển thị biểu mẫu xác nhận số lượng người lớn, trẻ em và ghi chú ăn uống.</span>
                    </div>
                </label>

                <div style="margin-top: 8px; max-width: 320px;">
                    <label class="mayhen_account_form_label" for="setting-rsvp-deadline">Hạn chót tiếp nhận phản hồi RSVP</label>
                    <input type="date" name="rsvp_deadline" id="setting-rsvp-deadline" value="{{ old('rsvp_deadline', $config['rsvp_deadline'] ?? '') }}" class="mayhen_account_form_input">
                    <span style="font-size: 12px; color: #7A726A; display: block; margin-top: 4px;">Khách mở thiệp sau ngày này sẽ thấy thông báo đã kết thúc thời gian xác nhận.</span>
                </div>
            </div>
        </div>

        <!-- Khối 4: Âm Nhạc Nền Thiệp Cưới -->
        <div class="mayhen_account_section" style="margin-bottom: 24px;">
            <div class="mayhen_account_section_header">
                <h3 class="mayhen_account_section_title">4. Âm Nhạc Nền Thiệp Cưới</h3>
                <p class="mayhen_account_section_desc">Giai điệu lãng mạn hòa quyện cùng từng khoảnh khắc quan khách mở phong bì thiệp.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="mayhen_account_form_label" for="setting-music-select">Chọn bài hát từ Bộ Sưu Tập Nhạc Cưới Mây Hẹn</label>
                    <select id="setting-music-select" class="mayhen_account_form_input" onchange="if(this.value) document.getElementById('setting-music-url').value = this.value;">
                        <option value="">-- Chọn bài hát gợi ý từ kho nhạc --</option>
                        @foreach($musics as $song)
                            <option value="{{ $song['url'] }}" {{ (old('music_url', $config['music_url'] ?? '') === $song['url']) ? 'selected' : '' }}>
                                {{ $song['title'] }} — {{ $song['artist'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="setting-music-url">Đường link bài hát nền (.mp3)</label>
                    <input type="url" name="music_url" id="setting-music-url" value="{{ old('music_url', $config['music_url'] ?? '') }}" class="mayhen_account_form_input" placeholder="https://... hoặc chọn bài ở trên">
                </div>

                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="music_autoplay" value="1" {{ old('music_autoplay', $config['music_autoplay'] ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #C5A880; margin-top: 2px;">
                    <div>
                        <strong style="font-size: 14px; color: #1F1D1A; display: block;">Tự động phát nhạc khi mở phong bì thiệp</strong>
                        <span style="font-size: 12px; color: #7A726A;">Nhạc sẽ êm dịu vang lên ngay khi khách mời chạm vào nút mở cánh thiệp cưới.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Khối 5: Tùy Biến Chia Sẻ Mạng Xã Hội (Social & SEO) -->
        <div class="mayhen_account_section" style="margin-bottom: 28px;">
            <div class="mayhen_account_section_header">
                <h3 class="mayhen_account_section_title">5. Tùy Biến Chia Sẻ Mạng Xã Hội (Zalo / Facebook)</h3>
                <p class="mayhen_account_section_desc">Nội dung hiển thị dưới dạng thẻ bưu thiếp khi gửi link thiệp qua tin nhắn.</p>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label class="mayhen_account_form_label" for="setting-og-title">Tiêu đề khi chia sẻ tin nhắn</label>
                    <input type="text" name="og_title" id="setting-og-title" value="{{ old('og_title', $config['og_title'] ?? '') }}" class="mayhen_account_form_input" placeholder="Ví dụ: Thiệp Cưới {{ $groom }} & {{ $bride }} — Trân trọng kính mời!">
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="setting-og-desc">Lời tựa mô tả ngắn</label>
                    <textarea name="og_description" id="setting-og-desc" rows="2" class="mayhen_account_form_input" style="height: auto; padding: 10px 14px;" placeholder="Ví dụ: Sự hiện diện của bạn là niềm vinh hạnh lớn nhất cho gia đình chúng mình trong ngày trọng đại!">{{ old('og_description', $config['og_description'] ?? '') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Khối 6: Cá Nhân Hóa Lời Chào Theo Nhóm Khách (Invitation Persona) -->
        @php
            $personaConfig = $config['persona_messages'] ?? [];
            $personaEnabled = old('persona_enabled', !empty($personaConfig['enabled']));
            $savedPersonaGroups = old('persona_messages.groups', $personaConfig['groups'] ?? []);
        @endphp
        <div class="mayhen_account_section" style="margin-bottom: 28px;">
            <div class="mayhen_account_section_header">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h3 class="mayhen_account_section_title" style="margin: 0;">6. Cá Nhân Hóa Lời Chào Theo Nhóm Khách (Invitation Persona)</h3>
                            <span style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFF; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Premium VNext</span>
                        </div>
                        <p class="mayhen_account_section_desc" style="margin-top: 4px;">Tự động hiển thị Lời ngỏ và Câu trích dẫn khác nhau phù hợp với từng đối tượng: Bạn Bè, Gia Đình, Đồng Nghiệp hay Khách VIP khi họ mở link thiệp riêng.</p>
                    </div>
                </div>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                <div>
                    <strong style="font-size: 14px; color: #1F1D1A; display: block;">Kích hoạt tính năng Cá Nhân Hóa Lời Chào (Persona)</strong>
                    <span style="font-size: 12px; color: #7A726A;">Khi bật, khách mời có mã định danh (token) sẽ thấy đúng thông điệp của nhóm mình. Khách xem link chung vẫn nhận lời ngỏ mặc định của thiệp.</span>
                </div>
                <label style="position: relative; display: inline-flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="persona_enabled" id="persona-toggle" value="1" {{ $personaEnabled ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #C5A880; cursor: pointer;">
                </label>
            </div>

            <div id="persona-groups-container" style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($availableGroups as $gIdx => $gName)
                    @php
                        $gData = $savedPersonaGroups[$gName] ?? [];
                        $gTitle = $gData['title'] ?? '';
                        $gSubtitle = $gData['subtitle'] ?? '';
                        $gContent = $gData['content'] ?? '';
                        $hasCustomContent = !empty($gTitle) || !empty($gSubtitle) || !empty($gContent);
                    @endphp
                    <div class="mayhen_account_persona_card" style="border: 1px solid #EAE3D9; border-radius: 12px; background: #FFFFFF; overflow: hidden; transition: border-color 0.2s ease;">
                        <div style="padding: 14px 18px; background: #FCFAF7; border-bottom: 1px solid #EAE3D9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #EFE8DD; color: #6B5E4B; font-size: 12px; font-weight: 600;">{{ $gIdx + 1 }}</span>
                                <strong style="font-size: 14px; color: #1F1D1A;">Nhóm: {{ $gName }}</strong>
                                @if($hasCustomContent)
                                    <span style="background: #E8F5E9; color: #2E7D32; font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 500;">Đã thiết lập</span>
                                @else
                                    <span style="background: #F5F5F5; color: #757575; font-size: 11px; padding: 2px 8px; border-radius: 10px;">Dùng lời ngỏ chung</span>
                                @endif
                            </div>
                        </div>

                        <div style="padding: 18px; display: flex; flex-direction: column; gap: 14px;">
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                                <div>
                                    <label class="mayhen_account_form_label" style="font-size: 12px;">Tiêu đề lời ngỏ cho nhóm {{ $gName }}</label>
                                    <input type="text" name="persona_messages[groups][{{ $gName }}][title]" value="{{ $gTitle }}" class="mayhen_account_form_input" style="font-size: 13px;" placeholder="Ví dụ: Gửi Những Người Bạn Tuyệt Vời">
                                </div>
                                <div>
                                    <label class="mayhen_account_form_label" style="font-size: 12px;">Câu đề tựa / Trích dẫn phụ</label>
                                    <input type="text" name="persona_messages[groups][{{ $gName }}][subtitle]" value="{{ $gSubtitle }}" class="mayhen_account_form_input" style="font-size: 13px;" placeholder="Ví dụ: Tuổi Trẻ & Kỷ Niệm Cùng Nhau">
                                </div>
                            </div>
                            <div>
                                <label class="mayhen_account_form_label" style="font-size: 12px;">Nội dung lời ngỏ thân gửi nhóm {{ $gName }} (Để trống nếu dùng mặc định)</label>
                                <textarea name="persona_messages[groups][{{ $gName }}][content]" rows="3" class="mayhen_account_form_input" style="height: auto; padding: 10px 14px; font-size: 13px; line-height: 1.5;" placeholder="Nhập tâm sự, lời nhắn gửi riêng dành cho khách thuộc nhóm {{ $gName }}...">{{ $gContent }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Nút Lưu Cài Đặt -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
            <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline">
                <span>Quay Lại</span>
            </a>
            <button type="submit" class="mayhen_account_btn mayhen_account_btn_gold" style="min-width: 180px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>Lưu Thay Đổi Cài Đặt</span>
            </button>
        </div>
    </form>

    <!-- Khu Vực Nâng Cao (Quản Lý Bản Sao & Xóa Thiệp) -->
    <div style="background: #FFFDF9; border: 1px solid #EFE4CA; border-radius: 16px; padding: 24px; margin-top: 20px;">
        <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #1F1D1A; margin: 0 0 6px 0;">
            Quản Lý Bản Sao & Thao Tác Nâng Cao
        </h4>
        <p style="font-size: 13px; color: #7A726A; margin: 0 0 18px 0;">
            Nhân bản để tạo bản sao lưu trước khi chỉnh sửa lớn, hoặc xóa mẫu thiệp không còn sử dụng.
        </p>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; border-top: 1px solid #F0E8D8; padding-top: 18px;">
            <div>
                <strong style="font-size: 14px; color: #1F1D1A; display: block;">Nhân Bản Mẫu Thiệp Này</strong>
                <span style="font-size: 12px; color: #7A726A;">Tạo một bản sao mới giữ nguyên toàn bộ nội dung, ảnh cưới và thiết kế hiện tại.</span>
            </div>
            <form action="{{ route('account.invitations.duplicate', $invitation->id) }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span>Nhân Bản Mẫu Thiệp</span>
                </button>
            </form>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; border-top: 1px solid #F0E8D8; padding-top: 18px; margin-top: 18px;">
            <div>
                <strong style="font-size: 14px; color: #C62828; display: block;">Xóa Thiệp Cưới Khỏi Tài Khoản</strong>
                <span style="font-size: 12px; color: #7A726A;">Thao tác này sẽ xóa vĩnh viễn mẫu thiệp cưới này. Khách mời sẽ không thể mở xem được nữa.</span>
            </div>
            <form action="{{ route('account.invitations.destroy', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn thiệp cưới này? Thao tác không thể hoàn tác.');" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm" style="background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>Xóa Vĩnh Viễn Thiệp</span>
                </button>
            </form>
        </div>
    </div>
@endsection

