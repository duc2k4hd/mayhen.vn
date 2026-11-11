@php
    use App\Services\MultilingualInvitationService;

    $activeLocales = MultilingualInvitationService::getActiveLocalesForInvitation($invitation ?? null);
    $currentLocale = MultilingualInvitationService::resolveLocale(request()->query('lang', request()->cookie('mayhen_locale')), $invitation ?? null);
    $currentLocaleData = $activeLocales[$currentLocale] ?? ($activeLocales['vi'] ?? MultilingualInvitationService::SUPPORTED_LOCALES['vi']);
@endphp

@if(count($activeLocales) > 1 && !($isEmbed ?? false))
    <div id="mayhenLanguageSwitcher" class="mayhen_lang_switcher_wrapper">
        <button type="button" id="mayhenLangToggleBtn" class="mayhen_lang_toggle_btn" aria-label="Đổi ngôn ngữ thiệp cưới" title="Đổi ngôn ngữ thiệp cưới">
            <span class="mayhen_lang_flag">{{ $currentLocaleData['flag'] }}</span>
            <span class="mayhen_lang_code">{{ strtoupper($currentLocale) }}</span>
            <svg class="mayhen_lang_chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <div id="mayhenLangDropdown" class="mayhen_lang_dropdown" style="display: none;">
            <div class="mayhen_lang_dropdown_header">
                <span>Chọn ngôn ngữ / Language</span>
            </div>
            <ul class="mayhen_lang_list">
                @foreach($activeLocales as $code => $data)
                    <li>
                        <button type="button" class="mayhen_lang_item {{ $code === $currentLocale ? 'is-active' : '' }}" data-locale="{{ $code }}">
                            <span class="mayhen_lang_item_flag">{{ $data['flag'] }}</span>
                            <div class="mayhen_lang_item_text">
                                <span class="mayhen_lang_item_name">{{ $data['native_name'] }}</span>
                                <span class="mayhen_lang_item_sub">{{ $data['name'] }}</span>
                            </div>
                            @if($code === $currentLocale)
                                <svg class="mayhen_lang_check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <style>
        .mayhen_lang_switcher_wrapper {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 99998;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .mayhen_lang_toggle_btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(197, 168, 128, 0.4);
            border-radius: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
            cursor: pointer;
            transition: all 0.25s ease;
            color: #1F1D1A;
            font-size: 13px;
            font-weight: 600;
        }
        .mayhen_lang_toggle_btn:hover {
            background: #FFFFFF;
            border-color: #C5A880;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(156, 122, 74, 0.2);
        }
        .mayhen_lang_flag {
            font-size: 16px;
            line-height: 1;
        }
        .mayhen_lang_code {
            letter-spacing: 0.5px;
            color: #4A433C;
        }
        .mayhen_lang_chevron {
            transition: transform 0.2s ease;
            color: #8C827A;
        }
        .mayhen_lang_dropdown {
            position: absolute;
            bottom: calc(100% + 10px);
            right: 0;
            width: 220px;
            background: #FFFFFF;
            border: 1px solid #EAE3D9;
            border-radius: 14px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            padding: 8px;
            box-sizing: border-box;
            animation: mayhenFadeInUp 0.2s ease;
        }
        .mayhen_lang_dropdown_header {
            padding: 6px 10px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #9C7A4A;
            border-bottom: 1px solid #FAF4ED;
            margin-bottom: 4px;
        }
        .mayhen_lang_list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .mayhen_lang_item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 8px 10px;
            background: none;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: left;
            transition: background 0.15s ease;
        }
        .mayhen_lang_item:hover {
            background: #FAF8F5;
        }
        .mayhen_lang_item.is-active {
            background: #F5EFEB;
        }
        .mayhen_lang_item_flag {
            font-size: 18px;
        }
        .mayhen_lang_item_text {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .mayhen_lang_item_name {
            font-size: 13px;
            font-weight: 600;
            color: #1F1D1A;
            line-height: 1.2;
        }
        .mayhen_lang_item_sub {
            font-size: 11px;
            color: #7A726A;
        }
        .mayhen_lang_check {
            color: #9C7A4A;
        }
        @keyframes mayhenFadeInUp {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 640px) {
            .mayhen_lang_switcher_wrapper {
                bottom: 16px;
                right: 16px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.getElementById('mayhenLanguageSwitcher');
            const toggleBtn = document.getElementById('mayhenLangToggleBtn');
            const dropdown = document.getElementById('mayhenLangDropdown');
            if (!wrapper || !toggleBtn || !dropdown) return;

            toggleBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = dropdown.style.display !== 'none';
                dropdown.style.display = isOpen ? 'none' : 'block';
            });

            document.addEventListener('click', (e) => {
                if (!wrapper.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            wrapper.querySelectorAll('.mayhen_lang_item').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetLocale = btn.getAttribute('data-locale');
                    if (!targetLocale) return;

                    // 1. Lưu cookie lựa chọn ngôn ngữ
                    document.cookie = `mayhen_locale=${targetLocale};path=/;max-age=${30 * 24 * 60 * 60};SameSite=Lax`;

                    // 2. Cập nhật query param ?lang= và reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('lang', targetLocale);
                    window.location.href = url.toString();
                });
            });
        });
    </script>
@endif

