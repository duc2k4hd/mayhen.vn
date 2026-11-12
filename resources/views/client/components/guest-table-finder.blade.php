@php
    $finderConfig = app(\App\Services\GuestTableFinderService::class)->getConfig($invitation);
    $isEnabled = (bool) ($finderConfig['is_enabled'] ?? true);
    $personalSeating = null;
    if (isset($guest) && $guest instanceof \App\Models\Guest) {
        $personalSeating = app(\App\Services\GuestTableFinderService::class)->findSeatingByGuestToken($invitation, $guest->guest_token);
    }
@endphp

@if($isEnabled)
<section class="mayhen_invitation_table_finder" id="mayhenTableFinderSection" data-sync-target="table_finder">
    <div class="mayhen_invitation_table_finder__container">
        <div class="mayhen_invitation_table_finder__header">
            <div class="mayhen_invitation_table_finder__badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polygon points="12 8 8 12 12 16 16 12 12 8"></polygon>
                </svg>
                <span>Hôn Lễ Sang Trọng & Chu Đáo</span>
            </div>
            <h2 class="mayhen_invitation_table_finder__title">{{ $finderConfig['title'] }}</h2>
            <p class="mayhen_invitation_table_finder__subtitle">{{ $finderConfig['subtitle'] }}</p>
        </div>

        @if($personalSeating && $personalSeating['is_seated'])
            {{-- THẺ VỊ TRÍ BÀN TIỆC DÀNH RIÊNG CHO QUÝ KHÁCH CÓ LINK MỜI --}}
            <div class="mayhen_invitation_table_finder__card mayhen_invitation_table_finder__card--vip">
                <div class="mayhen_invitation_table_finder__guest_name">
                    <span>Kính gửi</span>
                    <strong>{{ $personalSeating['salutation'] ? $personalSeating['salutation'] . ' ' : '' }}{{ $personalSeating['guest_name'] }}</strong>
                </div>

                <div class="mayhen_invitation_table_finder__table_box">
                    <div class="mayhen_invitation_table_finder__table_label">Vị trí an tọa của Quý Khách</div>
                    <div class="mayhen_invitation_table_finder__table_number">{{ $personalSeating['table_name'] }}</div>
                    @if($personalSeating['zone'])
                        <div class="mayhen_invitation_table_finder__table_zone">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                            <span>{{ $personalSeating['zone'] }}</span>
                        </div>
                    @endif
                </div>

                @if(!empty($personalSeating['table_notes']))
                    <div class="mayhen_invitation_table_finder__table_notes">
                        <em>* {{ $personalSeating['table_notes'] }}</em>
                    </div>
                @endif

                @if(!empty($personalSeating['table_mates']) && count($personalSeating['table_mates']) > 0)
                    <div class="mayhen_invitation_table_finder__mates">
                        <div class="mayhen_invitation_table_finder__mates_heading">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Những người bạn cùng bàn:</span>
                        </div>
                        <div class="mayhen_invitation_table_finder__mates_list">
                            @foreach($personalSeating['table_mates'] as $mate)
                                <span class="mayhen_invitation_table_finder__mate_pill">
                                    {{ $mate['name'] }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mayhen_invitation_table_finder__welcome">
                    {{ $personalSeating['welcome_message'] }}
                </div>
            </div>
        @else
            {{-- THANH TÌM KIẾM NHANH CHO KHÁCH MỞ THIỆP CHUNG HOẶC CHƯA GÁN BÀN TRƯỚC --}}
            <div class="mayhen_invitation_table_finder__search_box">
                <form id="formTableFinder" class="mayhen_invitation_table_finder__form" onsubmit="return false;">
                    <div class="mayhen_invitation_table_finder__input_wrap">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="search-icon">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="inputTableKeyword" class="mayhen_invitation_table_finder__input" 
                               placeholder="{{ $finderConfig['allow_search_by_phone'] ? 'Nhập họ tên hoặc 4 số cuối điện thoại...' : 'Nhập họ và tên của Quý Khách...' }}"
                               autocomplete="off">
                    </div>
                    <button type="button" id="btnSearchTable" class="mayhen_invitation_table_finder__btn_search">
                        <span>Tra Cứu Bàn</span>
                    </button>
                </form>

                <div id="tableFinderSpinner" class="mayhen_invitation_table_finder__spinner" style="display: none;">
                    <div class="mayhen_invitation_table_finder__loading_dots">
                        <span></span><span></span><span></span>
                    </div>
                    <p>Đang tra cứu vị trí bàn tiệc của Quý Khách...</p>
                </div>

                {{-- Khối hiển thị kết quả tìm kiếm --}}
                <div id="tableFinderResults" class="mayhen_invitation_table_finder__results" style="display: none;"></div>
            </div>
        @endif
    </div>
</section>

<style>
/* ==========================================================================
   MAYHEN QUIET LUXURY GUEST TABLE FINDER WIDGET STYLES (PURE VANILLA CSS)
   ========================================================================== */
.mayhen_invitation_table_finder {
    padding: 60px 20px;
    background: linear-gradient(180deg, rgba(250, 247, 242, 0.6) 0%, rgba(245, 239, 230, 0.9) 100%);
    position: relative;
    box-sizing: border-box;
}

.mayhen_invitation_table_finder__container {
    max-width: 680px;
    margin: 0 auto;
}

.mayhen_invitation_table_finder__header {
    text-align: center;
    margin-bottom: 32px;
}

.mayhen_invitation_table_finder__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    background: #FFFFFF;
    border: 1px solid #E6DBCF;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #9E7A52;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 12px;
}

.mayhen_invitation_table_finder__title {
    font-family: inherit;
    font-size: 1.65rem;
    font-weight: 600;
    color: #2D2A26;
    margin: 0 0 10px 0;
    letter-spacing: -0.01em;
}

.mayhen_invitation_table_finder__subtitle {
    font-size: 0.92rem;
    color: #7D756D;
    line-height: 1.6;
    margin: 0;
}

/* Thẻ VIP Cá nhân hóa */
.mayhen_invitation_table_finder__card {
    background: #FFFFFF;
    border: 1px solid #EAE2D8;
    border-radius: 20px;
    padding: 32px 28px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(158, 122, 82, 0.08);
    position: relative;
    overflow: hidden;
}

.mayhen_invitation_table_finder__card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #9E7A52 0%, #D4AF37 50%, #9E7A52 100%);
}

.mayhen_invitation_table_finder__guest_name {
    font-size: 1.05rem;
    color: #6A625A;
    margin-bottom: 20px;
}

.mayhen_invitation_table_finder__guest_name strong {
    color: #2D2A26;
    font-size: 1.25rem;
    display: block;
    margin-top: 4px;
}

.mayhen_invitation_table_finder__table_box {
    background: #FAF7F2;
    border: 1px dashed #D9CFB8;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 18px;
}

.mayhen_invitation_table_finder__table_label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #8C827A;
    margin-bottom: 6px;
}

.mayhen_invitation_table_finder__table_number {
    font-size: 1.85rem;
    font-weight: 700;
    color: #9E7A52;
    margin-bottom: 6px;
}

.mayhen_invitation_table_finder__table_zone {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    color: #4A443E;
    font-weight: 500;
}

.mayhen_invitation_table_finder__table_notes {
    font-size: 0.85rem;
    color: #8C827A;
    margin-bottom: 16px;
}

.mayhen_invitation_table_finder__mates {
    border-top: 1px solid #F0ECE6;
    padding-top: 18px;
    margin-bottom: 20px;
    text-align: left;
}

.mayhen_invitation_table_finder__mates_heading {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6A625A;
    margin-bottom: 10px;
}

.mayhen_invitation_table_finder__mates_list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.mayhen_invitation_table_finder__mate_pill {
    background: #F4EFEA;
    color: #4A443E;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 500;
}

.mayhen_invitation_table_finder__welcome {
    font-size: 0.88rem;
    color: #7D756D;
    font-style: italic;
    line-height: 1.5;
}

/* Khối tìm kiếm */
.mayhen_invitation_table_finder__search_box {
    background: #FFFFFF;
    border: 1px solid #ECE7E1;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.mayhen_invitation_table_finder__form {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.mayhen_invitation_table_finder__input_wrap {
    flex: 1;
    min-width: 240px;
    position: relative;
    display: flex;
    align-items: center;
}

.mayhen_invitation_table_finder__input_wrap .search-icon {
    position: absolute;
    left: 14px;
    color: #A39B92;
}

.mayhen_invitation_table_finder__input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    background: #FAF8F5;
    border: 1px solid #E6E1DA;
    border-radius: 10px;
    font-size: 0.92rem;
    color: #2D2A26;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.mayhen_invitation_table_finder__input:focus {
    border-color: #9E7A52;
    background: #FFFFFF;
    box-shadow: 0 0 0 3px rgba(158, 122, 82, 0.12);
}

.mayhen_invitation_table_finder__btn_search {
    background: #9E7A52;
    color: #FFFFFF;
    border: none;
    border-radius: 10px;
    padding: 12px 24px;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.mayhen_invitation_table_finder__btn_search:hover {
    background: #846541;
}

.mayhen_invitation_table_finder__spinner {
    text-align: center;
    padding: 24px;
    color: #7D756D;
    font-size: 0.88rem;
}

.mayhen_invitation_table_finder__loading_dots span {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #9E7A52;
    margin: 0 4px;
    animation: tableDotBounce 1.2s infinite ease-in-out both;
}

.mayhen_invitation_table_finder__loading_dots span:nth-child(1) { animation-delay: -0.32s; }
.mayhen_invitation_table_finder__loading_dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes tableDotBounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1.0); }
}

.mayhen_invitation_table_finder__results {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.mayhen_invitation_table_finder__item {
    background: #FAF8F5;
    border: 1px solid #ECE7E1;
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.mayhen_invitation_table_finder__item_guest {
    font-size: 1rem;
    font-weight: 600;
    color: #2D2A26;
}

.mayhen_invitation_table_finder__item_salutation {
    font-size: 0.85rem;
    color: #8C827A;
    margin-right: 4px;
}

.mayhen_invitation_table_finder__item_badge {
    background: #9E7A52;
    color: #FFFFFF;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.mayhen_invitation_table_finder__empty_note {
    text-align: center;
    padding: 20px;
    color: #8C827A;
    font-size: 0.9rem;
    background: #FAF8F5;
    border-radius: 10px;
    border: 1px dashed #E6E1DA;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnSearch = document.getElementById('btnSearchTable');
    const inputKeyword = document.getElementById('inputTableKeyword');
    const spinner = document.getElementById('tableFinderSpinner');
    const resultsBox = document.getElementById('tableFinderResults');

    if (!btnSearch || !inputKeyword) return;

    async function performSearch() {
        const keyword = inputKeyword.value.trim();
        if (keyword.length < 2) {
            alert('Vui lòng nhập tối thiểu 2 ký tự tên hoặc 4 số cuối điện thoại.');
            inputKeyword.focus();
            return;
        }

        btnSearch.disabled = true;
        if (spinner) spinner.style.display = 'block';
        if (resultsBox) {
            resultsBox.style.display = 'none';
            resultsBox.innerHTML = '';
        }

        try {
            const res = await fetch("{{ route('invitation.table_finder.search', $invitation->slug) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ keyword: keyword })
            });

            const data = await res.json();
            if (spinner) spinner.style.display = 'none';

            if (resultsBox) {
                resultsBox.style.display = 'flex';
                if (data.found && data.results && data.results.length > 0) {
                    let html = '';
                    data.results.forEach(item => {
                        html += `
                            <div class="mayhen_invitation_table_finder__item">
                                <div>
                                    <div class="mayhen_invitation_table_finder__item_guest">
                                        <span class="mayhen_invitation_table_finder__item_salutation">${item.salutation ? item.salutation : ''}</span>
                                        ${item.guest_name}
                                    </div>
                                    <div style="font-size: 0.82rem; color: #7D756D; margin-top: 2px;">
                                        ${item.zone ? 'Khu vực: ' + item.zone : ''}
                                        ${item.table_notes ? ' • ' + item.table_notes : ''}
                                    </div>
                                </div>
                                <div class="mayhen_invitation_table_finder__item_badge">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span>${item.table_name || 'Bàn Tiệc'}</span>
                                </div>
                            </div>
                        `;
                    });
                    resultsBox.innerHTML = html;
                } else {
                    resultsBox.innerHTML = `
                        <div class="mayhen_invitation_table_finder__empty_note">
                            ${data.message || 'Chưa tìm thấy thông tin sắp bàn. Quý khách vui lòng liên hệ Ban Lễ Tân tại sảnh.'}
                        </div>
                    `;
                }
            }
        } catch (err) {
            if (spinner) spinner.style.display = 'none';
            if (resultsBox) {
                resultsBox.style.display = 'flex';
                resultsBox.innerHTML = '<div class="mayhen_invitation_table_finder__empty_note">Lỗi kết nối máy chủ. Vui lòng thử lại.</div>';
            }
        } finally {
            btnSearch.disabled = false;
        }
    }

    btnSearch.addEventListener('click', performSearch);
    inputKeyword.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });
});
</script>
@endif
