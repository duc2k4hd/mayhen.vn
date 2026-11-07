@extends('client.layouts.master')

@section('title', 'Kỷ Niệm Ngày Cưới: ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/memory-vault.css') }}">
@endpush

@section('content')
<div class="mayhen_vault_wrapper">
    {{-- Top Owner Ribbon (chỉ hiện khi là chủ sở hữu thiệp) --}}
    @if($isOwner)
        <aside class="mayhen_vault_owner_bar" aria-label="Thanh quản trị kỷ niệm">
            <div class="mayhen_vault_owner_container">
                <span class="owner_badge">⚜️ CHẾ ĐỘ QUẢN LÝ KỶ NIỆM</span>
                <div class="owner_actions">
                    <a href="{{ route('memory.settings', $invitation->id) }}" class="mayhen_vault_btn mayhen_vault_btn--sm">
                        ⚙️ Cài Đặt Két Kỷ Niệm
                    </a>
                    <a href="{{ route('memory.keepsake_book', $invitation->id) }}" target="_blank" class="mayhen_vault_btn mayhen_vault_btn--sm mayhen_vault_btn--gold">
                        📖 In / Tải Sách Kỷ Yếu (PDF)
                    </a>
                    <a href="{{ route('account.wishes.index', $invitation->id) }}" class="mayhen_vault_btn mayhen_vault_btn--sm mayhen_vault_btn--outline">
                        Sổ Lưu Bút
                    </a>
                </div>
            </div>
        </aside>
    @endif

    {{-- Hero Memorial Header --}}
    <header class="mayhen_vault_hero">
        <div class="mayhen_vault_hero_inner">
            <span class="mayhen_vault_pretitle">BẢO TÀNG KỶ NIỆM NGÀY HẠNH PHÚC</span>
            <h1 class="mayhen_vault_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
            <p class="mayhen_vault_wedding_date">
                {{ $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d \t\h\á\n\g m, Y') : 'Ngày Hôn Lễ Trọng Đại' }}
            </p>

            {{-- Anniversary Counter Milestone --}}
            @if($anniversaryDays !== null)
                <div class="mayhen_vault_anniversary_pill">
                    <span class="anniversary_sparkle">✨</span>
                    @if($isAnniversaryPassed)
                        <span class="anniversary_text">
                            Đã cùng nhau đi qua <strong>{{ number_format($anniversaryDays) }} ngày</strong> yêu thương & gắn kết trọn đời
                        </span>
                    @else
                        <span class="anniversary_text">
                            Đếm ngược <strong>{{ number_format($anniversaryDays) }} ngày</strong> tới khoảnh khắc bước vào lễ đường
                        </span>
                    @endif
                </div>
            @endif

            {{-- Optional Hero Photo Frame --}}
            @if(!empty($coverImage))
                <div class="mayhen_vault_photo_frame">
                    <img src="{{ $coverImage }}" alt="Kỷ niệm ngày cưới {{ $wedding->groom_name }} & {{ $wedding->bride_name }}" class="mayhen_vault_photo">
                </div>
            @endif
        </div>
    </header>

    <div class="mayhen_vault_container">
        {{-- Emotional Thank-You Letter from Couple --}}
        <section class="mayhen_vault_letter_card">
            <div class="letter_emblem">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#C5A880" stroke-width="1.2">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path>
                </svg>
            </div>
            <span class="letter_label">TÂM THƯ TRI ÂN TỪ ĐÔI UYÊN ƯƠNG</span>
            <h2 class="letter_title">Lời Cảm Ơn Chân Thành</h2>
            <div class="letter_content">
                <p>{{ $thankYouLetter }}</p>
            </div>
            <div class="letter_signatures">
                <span class="sig_text">{{ $wedding->groom_name }}</span>
                <span class="sig_amp">&</span>
                <span class="sig_text">{{ $wedding->bride_name }}</span>
            </div>
        </section>

        {{-- Milestones Statistics Bar --}}
        <div class="mayhen_vault_stats_grid">
            <div class="mayhen_vault_stat_card">
                <span class="stat_label">LỜI CHÚC YÊU THƯƠNG</span>
                <div class="stat_value">{{ number_format($totalWishesCount) }}</div>
                <span class="stat_desc">Lời chúc tốt lành từ người thân, bạn bè</span>
            </div>
            <div class="mayhen_vault_stat_card">
                <span class="stat_label">QUAN KHÁCH ĐÃ CHUNG VUI</span>
                <div class="stat_value">{{ number_format($totalGuestsAttended) }}</div>
                <span class="stat_desc">Người đã có mặt chứng kiến giây phút hạnh phúc</span>
            </div>
            <div class="mayhen_vault_stat_card">
                <span class="stat_label">LƯU TRỮ VĨNH CỬU</span>
                <div class="stat_value">Trọn Đời</div>
                <span class="stat_desc">Không gian kỷ niệm số luôn vẹn nguyên theo năm tháng</span>
            </div>
        </div>

        {{-- Guestbook Wishes Collection --}}
        <section class="mayhen_vault_wishes_section">
            <div class="mayhen_vault_sec_head">
                <div>
                    <span class="sec_badge">SỔ LƯU BÚT TÌNH YÊU</span>
                    <h2 class="sec_title">Những Lời Chúc Phúc Đẹp Nhất</h2>
                </div>
                <div class="sec_actions">
                    <button type="button" class="mayhen_vault_btn mayhen_vault_btn--outline" id="btn_share_vault">
                        🔗 Sao Chép Link Kỷ Niệm
                    </button>
                    @if($isOwner)
                        <a href="{{ route('memory.keepsake_book', $invitation->id) }}" target="_blank" class="mayhen_vault_btn mayhen_vault_btn--gold">
                            📖 Xuất Sách Kỷ Yếu
                        </a>
                    @endif
                </div>
            </div>

            <div class="mayhen_vault_wishes_grid">
                @forelse($wishes as $w)
                    <article class="mayhen_vault_wish_card">
                        <div class="wish_card_top">
                            <span class="wish_sender">{{ $w->name }}</span>
                            <time class="wish_time" datetime="{{ $w->created_at ? $w->created_at->toIso8601String() : '' }}">
                                {{ $w->created_at ? $w->created_at->translatedFormat('d/m/Y') : '' }}
                            </time>
                        </div>
                        <p class="wish_text">"{{ $w->message }}"</p>
                        @if($w->reply)
                            <div class="wish_reply_box">
                                <span class="reply_badge">Lời cảm ơn từ Cô Dâu & Chú Rể:</span>
                                <p class="reply_text">{{ $w->reply }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="mayhen_vault_empty">
                        Chưa có lời chúc nào được hiển thị trong két kỷ niệm.
                    </div>
                @endforelse
            </div>

            {{-- Pagination Links --}}
            @if($wishes->hasPages())
                <div class="mayhen_vault_pagination">
                    {{ $wishes->links() }}
                </div>
            @endif
        </section>

        {{-- Back To Wedding Invitation Link --}}
        <footer class="mayhen_vault_footer">
            <a href="{{ route('invitation.show', $invitation->slug) }}" class="mayhen_vault_back_link">
                ← Mở lại thiệp cưới điện tử ban đầu
            </a>
            <p class="mayhen_vault_copyright">
                Mây Hẹn (mayhen.vn) &bull; Nền Tảng Lưu Giữ Kỷ Niệm Cưới Số Cao Cấp
            </p>
        </footer>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/memory-vault.js') }}"></script>
@endpush
