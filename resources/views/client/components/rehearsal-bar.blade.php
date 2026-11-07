@php
    $isEmbedMode = ($isEmbed ?? false) || request()->boolean('embed') || in_array(request()->query('mode'), ['editor', 'showcase', 'home', 'preview_card', 'iframe']);
    $canShowRehearsal = ($isOwner ?? false) && ($isRehearsal ?? false) && !$isEmbedMode;
@endphp

@if($canShowRehearsal)
<div id="mayhen-rehearsal-topbar" class="mayhen_rehearsal_topbar" role="region" aria-label="Thanh điều khiển tổng duyệt thiệp cưới">
    <div class="mayhen_rehearsal_container">
        <div class="mayhen_rehearsal_left">
            <div class="mayhen_rehearsal_badge">
                <span class="mayhen_rehearsal_icon" aria-hidden="true">🎭</span>
                <span class="mayhen_rehearsal_badge_text">CHẾ ĐỘ TỔNG DUYỆT</span>
            </div>
            <div class="mayhen_rehearsal_group_picker">
                <label for="mayhen-rehearsal-select" class="mayhen_rehearsal_label">Góc nhìn nhóm khách:</label>
                <select id="mayhen-rehearsal-select" class="mayhen_rehearsal_select" aria-label="Chọn nhóm khách để xem trước">
                    <option value="" {{ empty($rehearsalGroup) ? 'selected' : '' }}>-- Mặc định (Chung) --</option>
                    @foreach(($availableGroups ?? []) as $grp)
                        <option value="{{ $grp }}" {{ ($rehearsalGroup === $grp) ? 'selected' : '' }}>
                            Nhóm: {{ $grp }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mayhen_rehearsal_center">
            @if(!empty($rehearsalGroup))
                <span class="mayhen_rehearsal_status">
                    Đang mô phỏng lời ngỏ & thẻ bàn của: <strong>{{ $rehearsalGroup }}</strong>
                </span>
            @else
                <span class="mayhen_rehearsal_status">
                    Đang hiển thị nội dung thiệp chung cho khách vãng lai
                </span>
            @endif
        </div>

        <div class="mayhen_rehearsal_right">
            <a href="{{ route('invitation.show', $invitation->slug) }}" class="mayhen_rehearsal_exit_btn" title="Thoát chế độ tổng duyệt">
                <span>Thoát Tổng Duyệt</span>
                <span aria-hidden="true">&times;</span>
            </a>
        </div>
    </div>
</div>

<style>
    .mayhen_rehearsal_topbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 99999;
        background: rgba(31, 29, 26, 0.96);
        color: #FAF8F5;
        border-bottom: 1px solid rgba(197, 168, 128, 0.4);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        padding: 8px 16px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .mayhen_rehearsal_container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .mayhen_rehearsal_left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .mayhen_rehearsal_badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%);
        color: #FFFFFF;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }

    .mayhen_rehearsal_label {
        font-size: 12.5px;
        color: #D8CEBD;
        margin-right: 4px;
    }

    .mayhen_rehearsal_select {
        background: #2C2824;
        color: #FAF8F5;
        border: 1px solid #C5A880;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        outline: none;
    }

    .mayhen_rehearsal_select:focus {
        border-color: #FFFFFF;
        box-shadow: 0 0 0 2px rgba(197, 168, 128, 0.4);
    }

    .mayhen_rehearsal_center {
        font-size: 12.5px;
        color: #EFE8DD;
    }

    .mayhen_rehearsal_center strong {
        color: #C5A880;
    }

    .mayhen_rehearsal_right {
        display: flex;
        align-items: center;
    }

    .mayhen_rehearsal_exit_btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: rgba(255, 255, 255, 0.12);
        color: #FAF8F5;
        text-decoration: none;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .mayhen_rehearsal_exit_btn:hover {
        background: #C5A880;
        color: #1F1D1A;
        border-color: #C5A880;
    }

    /* Đẩy toàn bộ nội dung body xuống dưới một chút khi thanh rehearsal hiển thị */
    body.mayhen_with_rehearsal_bar {
        padding-top: 50px !important;
    }

    @media (max-width: 768px) {
        .mayhen_rehearsal_topbar {
            padding: 8px 12px;
        }
        .mayhen_rehearsal_center {
            display: none;
        }
        body.mayhen_with_rehearsal_bar {
            padding-top: 58px !important;
        }
    }
</style>

<script>
    (function() {
        document.body.classList.add('mayhen_with_rehearsal_bar');
        var sel = document.getElementById('mayhen-rehearsal-select');
        if (!sel) return;

        sel.addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('rehearsal', '1');
            if (this.value) {
                url.searchParams.set('rehearsal_group', this.value);
            } else {
                url.searchParams.delete('rehearsal_group');
            }
            window.location.href = url.toString();
        });
    })();
</script>
@endif

