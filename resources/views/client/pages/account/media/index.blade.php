@extends('client.pages.account.layout')

@section('page_title', 'Kho Tệp Media & Quản Lý Dung Lượng Lưu Trữ | ' . config('app.name'))
@section('account_breadcrumb', 'Kho Tệp & Dung Lượng')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/media.css') }}">
@endpush

@section('account_content')
<div style="max-width: 1100px; margin: 0 auto;">
    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 8px;">
            <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                &larr; <span>Quay lại danh sách thiệp</span>
            </a>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
            <div>
                <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0;">
                    Kho Tệp Media & Quản Lý Dung Lượng Lưu Trữ Cưới
                </h2>
                <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                    Đám cưới: <strong style="color: #1F1D1A;">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> • 
                    Thiệp: <strong style="color: #9C7A4A;">{{ $invitation->slug }}</strong>
                </p>
            </div>
            <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_account_btn mayhen_account_btn_gold" style="text-decoration: none; font-size: 13px;">
                <span>Vào Studio Thêm Ảnh</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div style="background: #FDE8E8; border: 1px solid #FBD5D5; color: #9B1C1C; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Thước Đo Dung Lượng (Storage Quota Cockpit) --}}
    <div class="mayhen_media_card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div>
                <strong style="font-size: 16px; color: #1F1D1A;">Tình Trạng Dung Lượng Bộ Nhớ</strong>
                <span style="font-size: 13px; color: #8C827A; margin-left: 8px;">
                    (Đã dùng <strong>{{ $usage['used_mb'] }} MB</strong> / <strong>{{ $usage['limit_mb'] }} MB</strong>)
                </span>
            </div>
            <span style="font-size: 14px; font-weight: 700; color: {{ $usage['percent'] >= 90 ? '#E02424' : ($usage['percent'] >= 75 ? '#D97706' : '#9C7A4A') }};">
                {{ $usage['percent'] }}%
            </span>
        </div>

        <div class="mayhen_media_meter_track">
            <div class="mayhen_media_meter_fill {{ $usage['percent'] >= 90 ? 'mayhen_media_meter_fill--danger' : ($usage['percent'] >= 75 ? 'mayhen_media_meter_fill--warning' : '') }}" style="width: {{ $usage['percent'] }}%;"></div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #8C827A; margin-top: 6px;">
            <span>Còn trống: <strong style="color: #2E7D32;">{{ $usage['remaining_mb'] }} MB</strong></span>
            <span>Tổng số tệp: <strong>{{ $usage['total_files_count'] }} tệp</strong></span>
        </div>

        {{-- 4 Thẻ Phân Loại Nhanh --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-top: 20px; padding-top: 18px; border-top: 1px solid #FAF8F5;">
            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px;">
                <span style="font-size: 11px; text-transform: uppercase; color: #8C827A; display: block;">Ảnh Thiệp Cưới (Album)</span>
                <strong style="font-size: 18px; color: #1F1D1A; margin-top: 2px; display: block;">
                    {{ $usage['breakdown']['wedding_photos']['mb'] }} MB
                </strong>
                <span style="font-size: 12px; color: #9C7A4A;">{{ $usage['breakdown']['wedding_photos']['count'] }} tệp đã tải</span>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px;">
                <span style="font-size: 11px; text-transform: uppercase; color: #8C827A; display: block;">Ảnh Khách Chụp (PhotoDrop)</span>
                <strong style="font-size: 18px; color: #1F1D1A; margin-top: 2px; display: block;">
                    {{ $usage['breakdown']['guest_photos']['mb'] }} MB
                </strong>
                <span style="font-size: 12px; color: #0E9F6E;">{{ $usage['breakdown']['guest_photos']['count'] }} tệp từ khách mời</span>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px;">
                <span style="font-size: 11px; text-transform: uppercase; color: #8C827A; display: block;">Hạn Mức Gói Cước</span>
                <strong style="font-size: 18px; color: #1F1D1A; margin-top: 2px; display: block;">
                    {{ $usage['limit_mb'] }} MB
                </strong>
                <span style="font-size: 12px; color: #6B7280;">Hạn mức tối đa</span>
            </div>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 12px 14px;">
                <span style="font-size: 11px; text-transform: uppercase; color: #8C827A; display: block;">Chính Sách Tối Ưu</span>
                <strong style="font-size: 14px; color: #2E7D32; margin-top: 4px; display: block;">
                    ✓ Tự Động Nén WebP
                </strong>
                <span style="font-size: 12px; color: #8C827A;">Tiết kiệm 45% dung lượng</span>
            </div>
        </div>
    </div>

    {{-- Danh Sách Tệp Media --}}
    <div class="mayhen_media_card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0;">
                Danh Sách Tệp Hình Ảnh Trong Thiệp ({{ count($usage['files']) }})
            </h3>
        </div>

        @if(empty($usage['files']))
            <div style="text-align: center; padding: 48px 20px; color: #8C827A;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#D1C7BD" stroke-width="1.5" style="margin-bottom: 12px;">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <p style="font-size: 15px; margin: 0 0 14px 0;">Thiệp cưới của bạn hiện chưa có tệp ảnh nào được tải lên.</p>
                <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_account_btn mayhen_account_btn_gold" style="text-decoration: none;">
                    <span>Tải Ảnh Đầu Tiên Vào Studio</span>
                </a>
            </div>
        @else
            <div class="mayhen_media_grid">
                @foreach($usage['files'] as $file)
                    <div class="mayhen_media_item">
                        <div class="mayhen_media_thumb_box">
                            <img src="{{ $file['url'] }}" alt="{{ $file['file_name'] }}" class="mayhen_media_thumb" loading="lazy">
                            <span class="mayhen_media_badge {{ $file['type'] === 'cover' ? 'mayhen_media_badge--cover' : ($file['type'] === 'guest_photo' ? 'mayhen_media_badge--guest' : 'mayhen_media_badge--gallery') }}">
                                {{ $file['type_label'] }}
                            </span>
                        </div>
                        <div class="mayhen_media_info">
                            <div>
                                <div class="mayhen_media_filename" title="{{ $file['file_name'] }}">
                                    {{ Str::limit($file['file_name'], 25) }}
                                </div>
                                <div class="mayhen_media_meta">
                                    <span>{{ $file['size_formatted'] }}</span>
                                    <span>{{ $file['created_at'] ? \Carbon\Carbon::parse($file['created_at'])->format('d/m/Y') : '' }}</span>
                                </div>
                            </div>
                            <div class="mayhen_media_actions">
                                <a href="{{ $file['url'] }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="flex: 1; text-align: center; text-decoration: none; font-size: 11.5px;">
                                    <span>Xem Thật</span>
                                </a>
                                <form action="{{ route('account.wedding_media.destroy', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tệp này để giải phóng dung lượng? Thao tác không thể hoàn tác.');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="storage_path" value="{{ $file['storage_path'] }}">
                                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm" style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 11.5px;" title="Xóa tệp giải phóng dung lượng">
                                        Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
