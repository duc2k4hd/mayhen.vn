@extends('client.pages.account.layout')

@php
    $pageTitle = 'Hành Trình Tình Yêu — Mây Hẹn';
@endphp

@section('account_breadcrumb', 'Hành Trình Yêu')

@section('account_content')
<div class="mayhen_story_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_story_admin_header">
        <div class="mayhen_story_admin_badge">Interactive Love Story Milestones</div>
        <h1 class="mayhen_story_admin_title">Dòng Thời Gian Hành Trình Yêu</h1>
        <p class="mayhen_story_admin_subtitle">
            Kể lại câu chuyện tình yêu diệu kỳ của <strong>{{ $invitation->wedding->groom_name ?? $invitation->groom_name }} &amp; {{ $invitation->wedding->bride_name ?? $invitation->bride_name }}</strong> qua từng cột mốc thăng hoa: từ khoảnh khắc đầu tiên chạm mắt, buổi hẹn hò đầu, lời cầu hôn ngọt ngào cho đến ngày cùng nhau bước vào lễ đường hôn phối.
        </p>
    </div>

    @if(session('success'))
        <div class="mayhen_story_alert mayhen_story_alert--success">
            <span class="mayhen_story_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_story_alert mayhen_story_alert--danger">
            <span class="mayhen_story_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('account.love_story.update', $invitation->id) }}" method="POST" id="loveStoryForm">
        @csrf

        {{-- Cấu Hình Chung --}}
        <div class="mayhen_story_card" style="margin-bottom: 2rem;">
            <h3 class="mayhen_story_card_heading">Thiết Lập Chung</h3>

            <div class="mayhen_story_form_group">
                <label class="mayhen_switch_label">
                    <input type="checkbox" name="enabled" value="1" {{ ($storyConfig['enabled'] ?? true) ? 'checked' : '' }} class="mayhen_checkbox">
                    <span class="mayhen_switch_custom"></span>
                    <div class="mayhen_switch_text">
                        <strong>Hiển thị Dòng Thời Gian Hành Trình Yêu trên thiệp cưới</strong>
                        <p>Cho phép quan khách đọc các cột mốc kỷ niệm khi cuộn qua phần Love Story.</p>
                    </div>
                </label>
            </div>

            <div class="mayhen_story_grid_2col">
                <div class="mayhen_story_form_group">
                    <label class="mayhen_label" for="inputStoryTitle">Tiêu đề phần câu chuyện:</label>
                    <input type="text" name="title" id="inputStoryTitle" value="{{ old('title', $storyConfig['title'] ?? 'Hành Trình Yêu Thương') }}" required class="mayhen_input">
                </div>
                <div class="mayhen_story_form_group">
                    <label class="mayhen_label" for="inputStorySubtitle">Lời dẫn / Thông điệp phụ:</label>
                    <input type="text" name="subtitle" id="inputStorySubtitle" value="{{ old('subtitle', $storyConfig['subtitle'] ?? '') }}" class="mayhen_input" placeholder="Ví dụ: Từng cột mốc thăng hoa ghi dấu những khoảnh khắc ngọt ngào nhất...">
                </div>
            </div>
        </div>

        {{-- Danh Sách Các Cột Mốc Tình Yêu --}}
        <div class="mayhen_story_card">
            <div class="mayhen_milestones_header">
                <div>
                    <h3 class="mayhen_story_card_heading" style="margin-bottom: 4px; border-bottom: none; padding-bottom: 0;">Các Cột Mốc Kỷ Niệm (Love Milestones)</h3>
                    <p class="mayhen_card_desc">Thêm hoặc điều chỉnh các mốc thời gian đáng nhớ của hai bạn:</p>
                </div>
                <button type="button" class="mayhen_btn_add_milestone" id="btnAddMilestone">
                    + Thêm Cột Mốc Mới
                </button>
            </div>

            <div id="milestonesContainer" class="mayhen_milestones_list">
                @foreach($storyConfig['milestones'] as $index => $item)
                    <div class="mayhen_milestone_editor_item" data-index="{{ $index }}">
                        <input type="hidden" name="milestones[{{ $index }}][id]" value="{{ $item['id'] }}">

                        <div class="mayhen_milestone_item_top">
                            <span class="mayhen_milestone_badge">Cột Mốc #<span class="milestone_num">{{ $index + 1 }}</span></span>
                            <button type="button" class="mayhen_btn_remove_milestone" title="Xóa cột mốc này" onclick="removeMilestone(this)">✕ Xóa</button>
                        </div>

                        <div class="mayhen_milestone_inputs_grid">
                            {{-- Thời gian & Biểu tượng --}}
                            <div class="mayhen_story_form_group">
                                <label class="mayhen_label">Mốc Thời Gian / Năm Tháng:</label>
                                <input type="text" name="milestones[{{ $index }}][date_label]" value="{{ $item['date_label'] }}" placeholder="Ví dụ: Mùa Thu 2021 / 14-02-2022" class="mayhen_input" required>
                            </div>

                            <div class="mayhen_story_form_group">
                                <label class="mayhen_label">Biểu Tượng Mốc:</label>
                                <select name="milestones[{{ $index }}][icon]" class="mayhen_select">
                                    @foreach($availableIcons as $iKey => $iVal)
                                        <option value="{{ $iKey }}" {{ ($item['icon'] ?? 'heart') === $iKey ? 'selected' : '' }}>
                                            {{ $iVal['symbol'] }} {{ $iVal['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Tiêu đề cột mốc --}}
                            <div class="mayhen_story_form_group" style="grid-column: span 2;">
                                <label class="mayhen_label">Tiêu Đề Cột Mốc:</label>
                                <input type="text" name="milestones[{{ $index }}][title]" value="{{ $item['title'] }}" placeholder="Ví dụ: Ngày Đầu Chạm Mắt / Ngày Em Nói Đồng Ý" class="mayhen_input" required>
                            </div>

                            {{-- Nội dung chia sẻ --}}
                            <div class="mayhen_story_form_group" style="grid-column: span 2;">
                                <label class="mayhen_label">Lời Kể Kỷ Niệm (Cảm xúc / Câu chuyện):</label>
                                <textarea name="milestones[{{ $index }}][description]" rows="3" class="mayhen_textarea" placeholder="Chia sẻ cảm xúc chân thật hoặc kỷ niệm đáng nhớ của hai bạn...">{{ $item['description'] }}</textarea>
                            </div>

                            {{-- Ảnh kỷ niệm --}}
                            <div class="mayhen_story_form_group" style="grid-column: span 2;">
                                <label class="mayhen_label">Ảnh Kỷ Niệm Cột Mốc:</label>
                                <div class="mayhen_photo_uploader_row">
                                    <input type="text" name="milestones[{{ $index }}][photo_url]" id="photoInput_{{ $index }}" value="{{ $item['photo_url'] }}" placeholder="https://..." class="mayhen_input mayhen_input_photo">
                                    <label class="mayhen_btn_upload_photo">
                                        <span>📁 Tải Ảnh Lên</span>
                                        <input type="file" accept="image/*" style="display: none;" onchange="uploadMilestoneImage(this, 'photoInput_{{ $index }}', 'photoPreview_{{ $index }}')">
                                    </label>
                                </div>
                                <div class="mayhen_milestone_photo_preview" id="photoPreview_{{ $index }}">
                                    @if(!empty($item['photo_url']) && !str_contains($item['photo_url'], 'placeholder'))
                                        <img src="{{ $item['photo_url'] }}" alt="Kỷ niệm" style="max-height: 80px; border-radius: 6px; margin-top: 8px; border: 1px solid #D9D5CF;">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mayhen_story_actions" style="margin-top: 2rem;">
                <button type="submit" class="mayhen_btn_submit_story">Lưu Hành Trình Yêu Thương</button>
            </div>
        </div>
    </form>
</div>

<template id="milestoneTemplate">
    <div class="mayhen_milestone_editor_item" data-index="__INDEX__">
        <input type="hidden" name="milestones[__INDEX__][id]" value="milestone___RAND__">

        <div class="mayhen_milestone_item_top">
            <span class="mayhen_milestone_badge">Cột Mốc #<span class="milestone_num">__NUM__</span></span>
            <button type="button" class="mayhen_btn_remove_milestone" title="Xóa cột mốc này" onclick="removeMilestone(this)">✕ Xóa</button>
        </div>

        <div class="mayhen_milestone_inputs_grid">
            <div class="mayhen_story_form_group">
                <label class="mayhen_label">Mốc Thời Gian / Năm Tháng:</label>
                <input type="text" name="milestones[__INDEX__][date_label]" placeholder="Ví dụ: Mùa Hè 2023" class="mayhen_input" required>
            </div>

            <div class="mayhen_story_form_group">
                <label class="mayhen_label">Biểu Tượng Mốc:</label>
                <select name="milestones[__INDEX__][icon]" class="mayhen_select">
                    @foreach($availableIcons as $iKey => $iVal)
                        <option value="{{ $iKey }}">{{ $iVal['symbol'] }} {{ $iVal['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mayhen_story_form_group" style="grid-column: span 2;">
                <label class="mayhen_label">Tiêu Đề Cột Mốc:</label>
                <input type="text" name="milestones[__INDEX__][title]" placeholder="Ví dụ: Chuyến Du Lịch Đầu Tiên Cùng Nhau" class="mayhen_input" required>
            </div>

            <div class="mayhen_story_form_group" style="grid-column: span 2;">
                <label class="mayhen_label">Lời Kể Kỷ Niệm (Cảm xúc / Câu chuyện):</label>
                <textarea name="milestones[__INDEX__][description]" rows="3" class="mayhen_textarea" placeholder="Chia sẻ cảm xúc chân thật hoặc kỷ niệm đáng nhớ..."></textarea>
            </div>

            <div class="mayhen_story_form_group" style="grid-column: span 2;">
                <label class="mayhen_label">Ảnh Kỷ Niệm Cột Mốc:</label>
                <div class="mayhen_photo_uploader_row">
                    <input type="text" name="milestones[__INDEX__][photo_url]" id="photoInput___INDEX__" placeholder="https://..." class="mayhen_input mayhen_input_photo">
                    <label class="mayhen_btn_upload_photo">
                        <span>📁 Tải Ảnh Lên</span>
                        <input type="file" accept="image/*" style="display: none;" onchange="uploadMilestoneImage(this, 'photoInput___INDEX__', 'photoPreview___INDEX__')">
                    </label>
                </div>
                <div class="mayhen_milestone_photo_preview" id="photoPreview___INDEX__"></div>
            </div>
        </div>
    </div>
</template>

<script>
let milestoneIndexCounter = {{ count($storyConfig['milestones']) }};

document.getElementById('btnAddMilestone').addEventListener('click', function() {
    const container = document.getElementById('milestonesContainer');
    const template = document.getElementById('milestoneTemplate').innerHTML;
    const nextNum = container.children.length + 1;
    const randomId = Math.random().toString(36).substring(2, 9);

    const rendered = template
        .replace(/__INDEX__/g, milestoneIndexCounter)
        .replace(/__NUM__/g, nextNum)
        .replace(/__RAND__/g, randomId);

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = rendered.trim();
    container.appendChild(tempDiv.firstChild);

    milestoneIndexCounter++;
});

window.removeMilestone = function(btn) {
    const item = btn.closest('.mayhen_milestone_editor_item');
    if (item) {
        if (confirm('Bạn có chắc muốn xóa cột mốc này?')) {
            item.remove();
            renumberMilestones();
        }
    }
};

function renumberMilestones() {
    const items = document.querySelectorAll('#milestonesContainer .mayhen_milestone_editor_item');
    items.forEach((item, idx) => {
        const numSpan = item.querySelector('.milestone_num');
        if (numSpan) numSpan.textContent = idx + 1;
    });
}

window.uploadMilestoneImage = function(fileInput, textInputId, previewContainerId) {
    if (!fileInput.files || !fileInput.files[0]) return;
    const file = fileInput.files[0];

    const formData = new FormData();
    formData.append('photo', file);
    formData.append('_token', '{{ csrf_token() }}');

    const previewContainer = document.getElementById(previewContainerId);
    if (previewContainer) {
        previewContainer.innerHTML = '<span style="font-size: 0.8rem; color: #9C7A4A;">Đang tải ảnh lên...</span>';
    }

    fetch('{{ route("account.love_story.upload_photo", $invitation->id) }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.url) {
            document.getElementById(textInputId).value = data.url;
            if (previewContainer) {
                previewContainer.innerHTML = `<img src="${data.url}" alt="Kỷ niệm" style="max-height: 80px; border-radius: 6px; margin-top: 8px; border: 1px solid #D9D5CF;">`;
            }
        } else {
            alert(data.message || 'Lỗi khi tải ảnh lên.');
            if (previewContainer) previewContainer.innerHTML = '';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối khi tải ảnh.');
        if (previewContainer) previewContainer.innerHTML = '';
    });
};
</script>

<style>
/* ==========================================================================
   LOVE STORY STUDIO STYLES (ĐỀ ÁN 132)
   100% Pure Vanilla CSS — BEM Prefix mayhen_story_*
   ========================================================================== */
.mayhen_story_admin_container {
    max-width: 1000px;
    margin: 0 auto;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #1F1D1A;
}

.mayhen_story_admin_badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(197, 168, 128, 0.15);
    color: #9C7A4A;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 999px;
    margin-bottom: 0.5rem;
}

.mayhen_story_admin_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: #1F1D1A;
}

.mayhen_story_admin_subtitle {
    font-size: 0.95rem;
    color: #635E56;
    line-height: 1.6;
    max-width: 820px;
    margin: 0 0 2rem;
}

.mayhen_story_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}
.mayhen_story_alert--success {
    background: #F4F7ED;
    border: 1px solid #C4D7AC;
    color: #375323;
}
.mayhen_story_alert--danger {
    background: #FDF3F2;
    border: 1px solid #F5C2BE;
    color: #8C2E24;
}
.mayhen_story_alert_icon {
    font-weight: bold;
    font-size: 1.1rem;
}

.mayhen_story_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.25);
    border-radius: 12px;
    padding: 1.75rem;
    box-shadow: 0 2px 12px rgba(31, 29, 26, 0.03);
}

.mayhen_story_card_heading {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.35rem;
    font-weight: 600;
    margin: 0 0 1.25rem;
    border-bottom: 1px solid rgba(197, 168, 128, 0.2);
    padding-bottom: 0.5rem;
}

.mayhen_card_desc {
    font-size: 0.85rem;
    color: #7D7871;
    margin: 0;
}

.mayhen_story_grid_2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 640px) {
    .mayhen_story_grid_2col {
        grid-template-columns: 1fr;
    }
}

.mayhen_story_form_group {
    margin-bottom: 1.25rem;
}
.mayhen_label {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
    color: #1F1D1A;
}
.mayhen_input, .mayhen_select, .mayhen_textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #D9D5CF;
    border-radius: 6px;
    font-size: 0.9rem;
    box-sizing: border-box;
    font-family: inherit;
}
.mayhen_input:focus, .mayhen_select:focus, .mayhen_textarea:focus {
    outline: none;
    border-color: #9C7A4A;
}

/* Switch */
.mayhen_switch_label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
}
.mayhen_checkbox {
    display: none;
}
.mayhen_switch_custom {
    width: 40px;
    height: 22px;
    background: #D9D5CF;
    border-radius: 11px;
    position: relative;
    flex-shrink: 0;
    transition: background 0.25s ease;
    margin-top: 2px;
}
.mayhen_switch_custom::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #FFFFFF;
    transition: transform 0.25s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}
.mayhen_checkbox:checked + .mayhen_switch_custom {
    background: #9C7A4A;
}
.mayhen_checkbox:checked + .mayhen_switch_custom::after {
    transform: translateX(18px);
}
.mayhen_switch_text strong {
    display: block;
    font-size: 0.92rem;
    font-weight: 600;
}
.mayhen_switch_text p {
    font-size: 0.8rem;
    color: #7D7871;
    margin: 2px 0 0;
}

/* Milestones List */
.mayhen_milestones_header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_btn_add_milestone {
    padding: 8px 16px;
    background: #FAF7F2;
    border: 1px dashed #C5A880;
    color: #9C7A4A;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_btn_add_milestone:hover {
    background: #9C7A4A;
    color: #FFFFFF;
    border-style: solid;
}

.mayhen_milestones_list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.mayhen_milestone_editor_item {
    background: #FAF7F2;
    border: 1px solid rgba(197, 168, 128, 0.35);
    border-radius: 10px;
    padding: 1.25rem;
    position: relative;
}

.mayhen_milestone_item_top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
    border-bottom: 1px solid rgba(197, 168, 128, 0.2);
    padding-bottom: 0.5rem;
}
.mayhen_milestone_badge {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9C7A4A;
    background: rgba(197, 168, 128, 0.15);
    padding: 3px 8px;
    border-radius: 4px;
}
.mayhen_btn_remove_milestone {
    background: transparent;
    border: none;
    color: #8C2E24;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    padding: 2px 6px;
}
.mayhen_btn_remove_milestone:hover {
    text-decoration: underline;
}

.mayhen_milestone_inputs_grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
@media (max-width: 640px) {
    .mayhen_milestone_inputs_grid {
        grid-template-columns: 1fr;
    }
}

.mayhen_photo_uploader_row {
    display: flex;
    gap: 8px;
}
.mayhen_input_photo {
    flex: 1;
}
.mayhen_btn_upload_photo {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    background: #FFFFFF;
    border: 1px solid #D9D5CF;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #4A4641;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.mayhen_btn_upload_photo:hover {
    border-color: #9C7A4A;
    color: #9C7A4A;
}

.mayhen_btn_submit_story {
    width: 100%;
    padding: 13px;
    background: #1F1D1A;
    color: #FFFFFF;
    font-size: 0.95rem;
    font-weight: 600;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}
.mayhen_btn_submit_story:hover {
    background: #38342E;
}
</style>
@endsection

