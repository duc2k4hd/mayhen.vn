@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quét Thiệp Giấy Cưới Bằng AI';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/paper_scan.css') }}?v={{ time() }}">
@endpush

@section('account_breadcrumb', 'Quét Thiệp Giấy AI')

@section('account_content')
    <div class="mayhen_paperscan_header">
        <div class="mayhen_paperscan_eyebrow">
            <span>✦</span>
            <span>Đề Án 77 • AI Đọc Thiệp Giấy (Mục 2.9 DOCX)</span>
        </div>
        <h2 class="mayhen_paperscan_title">
            Trích Xuất Thông Tin Từ Ảnh Thiệp Cưới Giấy
        </h2>
        <p class="mayhen_paperscan_desc">
            Bạn đã in thiệp cưới truyền thống hoặc nhận được bản thiết kế từ xưởng in? Hãy tải lên ảnh chụp thiệp giấy, AI của Mây Hẹn sẽ tự động bóc tách tên cô dâu chú rể, cha mẹ hai bên, ngày giờ, địa điểm tổ chức để điền tự động vào thiệp cưới online sau bước rà soát thủ công.
        </p>
    </div>

    @if(session('success'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E7D32; color: #1B5E20; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #FDEDEC; border-left: 4px solid #E74C3C; color: #922B21; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            <strong>Không thể xử lý ảnh:</strong>
            <ul style="margin: 6px 0 0 18px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="max-width: 720px; margin: 0 auto;">
        <form action="{{ route('account.paper_scan.store', $invitation->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="mayhen_scan_file_input" id="mayhen_scan_dropzone" class="mayhen_paperscan_dropzone">
                <input 
                    type="file" 
                    id="mayhen_scan_file_input" 
                    name="image" 
                    accept="image/jpeg,image/png,image/webp,image/jpg" 
                    style="display: none;" 
                    required
                >
                <div class="mayhen_paperscan_icon">📷</div>
                <h3 class="mayhen_paperscan_drop_title">Chụp Hoặc Chọn Tệp Ảnh Thiệp Cưới Của Bạn</h3>
                <p class="mayhen_paperscan_drop_hint">
                    Hỗ trợ định dạng JPG, PNG, WebP (Tối đa 10MB). Hãy chụp rõ nét chữ để AI nhận diện chuẩn xác nhất.
                </p>
                <span class="mayhen_account_btn mayhen_account_btn_gold" style="pointer-events: none;">
                    Chọn Ảnh Từ Thiết Bị
                </span>

                <div id="mayhen_scan_preview_box" class="mayhen_paperscan_preview_box">
                    <img id="mayhen_scan_preview_img" src="" alt="Xem trước thiệp giấy" class="mayhen_paperscan_preview_img">
                </div>
            </label>

            <div style="text-align: center; margin-bottom: 36px;">
                <button 
                    type="submit" 
                    id="mayhen_scan_submit_btn" 
                    class="mayhen_account_btn mayhen_account_btn_primary"
                    style="padding: 12px 32px; font-size: 0.95rem; opacity: 0.5;"
                    disabled
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                    </svg>
                    <span>Bắt Đầu Quét & Bóc Tách Dữ Liệu AI</span>
                </button>
            </div>
        </form>

        @if($recentScans->isNotEmpty())
            <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 20px;">
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 1.25rem; margin: 0 0 14px; color: #1F1D1A;">
                    Lịch Sử Quét Thiệp Gần Đây
                </h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($recentScans as $recent)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px; border: 1px solid #EAE3D9; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <img src="{{ $recent->image_url }}" alt="Scan thumbnail" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;">
                                <div>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: #1F1D1A;">
                                        Độ tin cậy: <span style="color: #9C7A4A;">{{ $recent->confidence_score }}%</span>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #7A726A;">
                                        {{ $recent->created_at->format('d/m/Y H:i') }} • Trạng thái: <strong>{{ $recent->status_label }}</strong>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('account.paper_scan.review', [$invitation->id, $recent->id]) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                                Xem & Rà Soát
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/paper_scan.js') }}?v={{ time() }}"></script>
@endpush

