@extends('client.layouts.master')

@section('title', 'Quản Lý Album Ảnh Khách Mời | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/photo-drop.css') }}">
@endpush

@section('content')
<div class="mayhen_album_wrap">
    <div class="mayhen_album_container">
        {{-- Breadcrumbs --}}
        <div class="mayhen_album_breadcrumbs">
            <a href="{{ route('account.dashboard') }}">Tài khoản</a>
            <span>/</span>
            <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
            <span>/</span>
            <span>Album Ảnh Khách Mời</span>
        </div>

        @if(session('success'))
            <div class="mayhen_album_alert mayhen_album_alert--success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mayhen_album_alert mayhen_album_alert--error">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header Card --}}
        <header class="mayhen_album_header">
            <div>
                <span class="album_badge">LIVE GUEST ALBUM</span>
                <h1 class="album_title">Album Khoảnh Khắc Do Khách Mời Chụp</h1>
                <p class="album_subtitle">
                    Hôn lễ: <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> &bull;
                    Nơi lưu giữ những khoảnh khắc tự nhiên, chân thật nhất do chính bạn bè chụp tại bàn tiệc.
                </p>
            </div>
            <div class="album_header_actions">
                @if($totalPhotos > 0)
                    <a href="{{ route('photo_drop.downloadZip', $invitation->id) }}" class="mayhen_album_btn mayhen_album_btn--gold">
                        📦 Tải Xuống Toàn Bộ Ảnh (ZIP)
                    </a>
                @endif
                <a href="{{ route('photo_drop.show', $invitation->slug) }}" target="_blank" class="mayhen_album_btn mayhen_album_btn--outline">
                    👁️ Xem Trang Photo Drop
                </a>
                <a href="{{ route('memory.vault', $invitation->slug) }}" target="_blank" class="mayhen_album_btn mayhen_album_btn--outline">
                    🏛️ Bảo Tàng Kỷ Niệm
                </a>
            </div>
        </header>

        {{-- QR Standee Banner For Tables --}}
        <section class="mayhen_album_qr_banner">
            <div class="qr_banner_code">
                {!! $qrCodeSvg !!}
            </div>
            <div class="qr_banner_info">
                <span class="qr_banner_label">MÃ QR ĐẶT TẠI BÀN TIỆC</span>
                <h2 class="qr_banner_title">Mời Khách Quét Mã Tải Ảnh Tại Bàn Tiệc</h2>
                <p class="qr_banner_desc">
                    In mã QR này đặt lên từng bàn tiệc cưới hoặc chiếu lên màn hình LED. Khách chỉ cần mở camera điện thoại quét mã là có thể gửi ngay ảnh đẹp tặng hai bạn!
                </p>
                <div class="qr_banner_links">
                    <span class="qr_link_url">{{ $dropUrl }}</span>
                    <button type="button" class="mayhen_album_btn mayhen_album_btn--sm mayhen_album_btn--outline" onclick="window.print()">
                        🖨️ In Thẻ QR Đặt Bàn Tiệc
                    </button>
                </div>
            </div>
        </section>

        {{-- 4 Stat Metric Cards --}}
        <div class="mayhen_album_stats_grid">
            <div class="mayhen_album_stat_card">
                <span class="stat_label">TỔNG SỐ ẢNH ĐÃ NHẬN</span>
                <div class="stat_value">{{ number_format($totalPhotos) }}</div>
                <span class="stat_hint">Ảnh từ khách gửi về</span>
            </div>
            <div class="mayhen_album_stat_card">
                <span class="stat_label">ĐÃ HIỂN THỊ CÔNG KHAI</span>
                <div class="stat_value" style="color: #2E7D32;">{{ number_format($approvedCount) }}</div>
                <span class="stat_hint">Đã xuất hiện trong Kỷ Niệm</span>
            </div>
            <div class="mayhen_album_stat_card">
                <span class="stat_label">CHỜ KIỂM DUYỆT</span>
                <div class="stat_value" style="color: #E65100;">{{ number_format($pendingCount) }}</div>
                <span class="stat_hint">Đang tạm ẩn</span>
            </div>
            <div class="mayhen_album_stat_card">
                <span class="stat_label">DUNG LƯỢNG LƯU TRỮ</span>
                <div class="stat_value">{{ $totalSizeMB }} MB</div>
                <span class="stat_hint">Chất lượng ảnh gốc</span>
            </div>
        </div>

        {{-- Filter & Actions Bar --}}
        <div class="mayhen_album_toolbar">
            <div class="toolbar_filters">
                <a href="{{ route('photo_drop.manage', $invitation->id) }}" class="filter_pill {{ empty($filterStatus) ? 'is-active' : '' }}">
                    Tất cả ({{ $totalPhotos }})
                </a>
                <a href="{{ route('photo_drop.manage', ['id' => $invitation->id, 'status' => 'approved']) }}" class="filter_pill {{ $filterStatus === 'approved' ? 'is-active' : '' }}">
                    Đã duyệt ({{ $approvedCount }})
                </a>
                <a href="{{ route('photo_drop.manage', ['id' => $invitation->id, 'status' => 'pending']) }}" class="filter_pill {{ $filterStatus === 'pending' ? 'is-active' : '' }}">
                    Chờ duyệt ({{ $pendingCount }})
                </a>
            </div>
        </div>

        {{-- Photos Grid Collection --}}
        <div class="mayhen_album_photos_grid">
            @forelse($photos as $photo)
                <div class="mayhen_album_item {{ !$photo->is_approved ? 'is-pending' : '' }}">
                    <div class="album_item_thumb">
                        <img src="{{ $photo->photo_url }}" alt="Ảnh từ {{ $photo->uploader_name }}" loading="lazy">
                        @if(!$photo->is_approved)
                            <span class="thumb_pending_badge">Tạm Ẩn</span>
                        @endif
                    </div>
                    <div class="album_item_meta">
                        <strong class="item_uploader">{{ $photo->uploader_name }}</strong>
                        @if($photo->caption)
                            <p class="item_caption">"{{ Str::limit($photo->caption, 60) }}"</p>
                        @endif
                        <span class="item_time">
                            {{ $photo->created_at ? $photo->created_at->format('H:i d/m/Y') : '' }} &bull; {{ round($photo->file_size / 1024) }} KB
                        </span>
                    </div>
                    <div class="album_item_actions">
                        <form action="{{ route('photo_drop.toggleApproval', ['id' => $invitation->id, 'photoId' => $photo->id]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn_item_action {{ $photo->is_approved ? 'btn_hide' : 'btn_approve' }}">
                                {{ $photo->is_approved ? 'Ẩn' : 'Duyệt ✓' }}
                            </button>
                        </form>
                        <a href="{{ $photo->photo_url }}" target="_blank" class="btn_item_action btn_view" title="Xem ảnh gốc">
                            Xem ↗
                        </a>
                        <form action="{{ route('photo_drop.destroy', ['id' => $invitation->id, 'photoId' => $photo->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa vĩnh viễn bức ảnh này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn_item_action btn_delete" title="Xóa ảnh">
                                Xóa ✕
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="mayhen_album_empty">
                    <div class="empty_icon">📷</div>
                    <h3 class="empty_title">Chưa Có Bức Ảnh Nào Được Gửi</h3>
                    <p class="empty_desc">
                        Hãy in mã QR đặt lên bàn tiệc hoặc gửi link cho bạn bè để thu thập những khoảnh khắc tự nhiên nhất trong ngày cưới nhé!
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($photos->hasPages())
            <div class="mayhen_album_pagination">
                {{ $photos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

