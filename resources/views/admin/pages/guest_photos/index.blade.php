@extends('admin.layouts.master')

@section('title', 'Kiểm Duyệt Ảnh Khách Gửi & Tường Ảnh Trực Tiếp')

@section('content')
<div class="mayhen_admin_guest_photos" style="padding: 24px; max-width: 1440px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px;">
                    Kiểm Duyệt Ảnh Khách Gửi & Tường Ảnh Trực Tiếp
                </h1>
                <span style="font-size: 11px; font-weight: 700; background: #fdf2f8; color: #be185d; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    Live Guest Stream
                </span>
            </div>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Kiểm duyệt khoảnh khắc quan khách tải lên, tường ảnh tương tác & trình chiếu Slideshow LED sảnh tiệc theo Mục 217, 218 PRD V3.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.guest_photos.slideshow', request()->only('wedding_id')) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 8px; background: #be185d; color: #ffffff; font-size: 13px; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(190, 24, 93, 0.2); transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect><line x1="7" y1="2" x2="7" y2="22"></line><line x1="17" y1="2" x2="17" y2="22"></line><line x1="2" y1="12" x2="22" y2="12"></line><line x1="2" y1="7" x2="7" y2="7"></line><line x1="2" y1="17" x2="7" y2="17"></line><line x1="17" y1="17" x2="22" y2="17"></line><line x1="17" y1="7" x2="22" y2="7"></line></svg>
                Trình Chiếu Live Slideshow
            </a>
            <a href="{{ route('admin.guest_photos.export', request()->all()) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                Xuất Báo Cáo CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tổng Khoảnh Khắc
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #1e293b;">
                    {{ number_format($stats['total_photos']) }}
                </div>
                <div style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Toàn hệ thống
                </div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #b45309; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đang Chờ Kiểm Duyệt
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #d97706;">
                    {{ number_format($stats['pending_photos']) }}
                </div>
                <div style="background: #fffbeb; color: #b45309; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Cần duyệt
                </div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đã Duyệt Công Khai
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #059669;">
                    {{ number_format($stats['approved_photos']) }}
                </div>
                <div style="background: #ecfdf5; color: #065f46; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Đang chiếu LED
                </div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #4338ca; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Dung Lượng Ảnh Lưu Trữ
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #4f46e5;">
                    {{ $stats['total_size_mb'] }} <span style="font-size: 15px; font-weight: 500;">MB</span>
                </div>
                <div style="background: #eef2ff; color: #4338ca; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Storage CDN
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <form method="GET" action="{{ route('admin.guest_photos.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 220px; position: relative;">
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo người gửi, chú thích, đám cưới..." style="width: 100%; padding: 8px 12px 8px 36px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit;">
                <svg style="position: absolute; left: 12px; top: 10px; color: #94a3b8;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>

            <div style="min-width: 200px;">
                <select name="wedding_id" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; background: #fff; font-family: inherit;">
                    <option value="">-- Tất cả đám cưới --</option>
                    @foreach($weddings as $w)
                        <option value="{{ $w->id }}" {{ (string)$filters['wedding_id'] === (string)$w->id ? 'selected' : '' }}>
                            #{{ $w->id }} - {{ $w->groom_name }} & {{ $w->bride_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="min-width: 160px;">
                <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; background: #fff; font-family: inherit;">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>⏳ Chờ kiểm duyệt</option>
                    <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>✅ Đã duyệt công khai</option>
                </select>
            </div>

            <button type="submit" style="padding: 8px 16px; border-radius: 8px; background: #0f172a; color: #ffffff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                Lọc dữ liệu
            </button>

            @if(!empty($filters['q']) || !empty($filters['wedding_id']) || $filters['status'] !== 'all')
                <a href="{{ route('admin.guest_photos.index') }}" style="padding: 8px 14px; border-radius: 8px; background: #f1f5f9; color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600;">
                    Đặt lại
                </a>
            @endif
        </form>
    </div>

    <!-- Batch Actions Bar -->
    <div id="batchActionBar" style="display: none; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 18px; margin-bottom: 20px; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <input type="checkbox" id="selectAllCheckbox" style="cursor: pointer; width: 16px; height: 16px;">
            <span style="font-size: 13px; font-weight: 600; color: #334155;">
                Đã chọn <span id="selectedCount" style="color: #be185d;">0</span> ảnh
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="submitBatchAction('approve')" style="padding: 7px 14px; border-radius: 6px; background: #059669; color: #ffffff; border: none; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Duyệt hiển thị hàng loạt
            </button>
            <button type="button" onclick="submitBatchAction('delete')" style="padding: 7px 14px; border-radius: 6px; background: #ef4444; color: #ffffff; border: none; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                Xóa vĩnh viễn hàng loạt
            </button>
        </div>
    </div>

    <!-- Hidden Batch Forms -->
    <form id="batchApproveForm" method="POST" action="{{ route('admin.guest_photos.batch_approve') }}" style="display: none;">
        @csrf
        <div id="batchApproveInputs"></div>
    </form>
    <form id="batchDeleteForm" method="POST" action="{{ route('admin.guest_photos.batch_delete') }}" style="display: none;">
        @csrf
        <div id="batchDeleteInputs"></div>
    </form>

    <!-- Photos Grid / Table View -->
    @if($photos->isEmpty())
        <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 12px; padding: 48px 20px; text-align: center;">
            <svg style="margin: 0 auto 12px; color: #94a3b8;" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            <div style="font-size: 16px; font-weight: 600; color: #334155; margin-bottom: 4px;">Chưa có ảnh nào của quan khách</div>
            <p style="font-size: 13px; color: #64748b; margin: 0;">Khi khách dự tiệc quét mã QR thiệp hoặc tải ảnh trực tiếp, hình ảnh sẽ xuất hiện tại đây để kiểm duyệt.</p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; margin-bottom: 24px;">
            @foreach($photos as $photo)
                <div class="mayhen_admin_guest_photos_card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s; position: relative;">
                    <!-- Top Image Area -->
                    <div style="position: relative; width: 100%; height: 210px; background: #f8fafc; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img src="{{ $photo->photo_url }}" alt="Guest photo" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;" onclick="openLightbox('{{ $photo->photo_url }}', '{{ addslashes($photo->uploader_name) }}', '{{ addslashes($photo->caption ?? '') }}')">
                        
                        <!-- Checkbox Select -->
                        <div style="position: absolute; top: 10px; left: 10px; z-index: 2;">
                            <input type="checkbox" class="photoCheckbox" value="{{ $photo->id }}" style="width: 18px; height: 18px; cursor: pointer; accent-color: #be185d;">
                        </div>

                        <!-- Status Badge -->
                        <div style="position: absolute; top: 10px; right: 10px; z-index: 2;">
                            @if($photo->is_approved)
                                <span style="background: rgba(16, 185, 129, 0.9); backdrop-filter: blur(4px); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Đã Duyệt
                                </span>
                            @else
                                <span style="background: rgba(245, 158, 11, 0.9); backdrop-filter: blur(4px); color: #ffffff; font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Chờ Duyệt
                                </span>
                            @endif
                        </div>

                        <!-- View Zoom Button -->
                        <button type="button" onclick="openLightbox('{{ $photo->photo_url }}', '{{ addslashes($photo->uploader_name) }}', '{{ addslashes($photo->caption ?? '') }}')" style="position: absolute; bottom: 10px; right: 10px; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); color: #ffffff; border: none; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>
                        </button>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 14px 16px; flex: 1; display: flex; flex-direction: column;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                            <div style="font-size: 14px; font-weight: 700; color: #1e293b;">
                                {{ $photo->uploader_name }}
                            </div>
                            <span style="font-size: 11px; color: #94a3b8;">
                                {{ $photo->created_at ? $photo->created_at->format('H:i d/m') : 'N/A' }}
                            </span>
                        </div>

                        <div style="font-size: 12px; color: #475569; margin-bottom: 10px; line-height: 1.4; min-height: 34px;">
                            @if(!empty($photo->caption))
                                <em>"{{ Str::limit($photo->caption, 80) }}"</em>
                            @else
                                <span style="color: #94a3b8; font-style: italic;">Không có chú thích</span>
                            @endif
                        </div>

                        <div style="margin-top: auto; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 4px; font-size: 11px; color: #64748b;">
                            <div>
                                <strong>Đám cưới:</strong> {{ $photo->wedding ? ($photo->wedding->groom_name . ' & ' . $photo->wedding->bride_name) : 'N/A' }}
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span><strong>Dung lượng:</strong> {{ $photo->file_size ? round($photo->file_size / 1024, 1) . ' KB' : 'N/A' }}</span>
                                <span><strong>IP:</strong> {{ $photo->ip_address ?? '127.0.0.1' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div style="padding: 10px 16px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                        <form method="POST" action="{{ route('admin.guest_photos.toggle_approval', $photo->id) }}" style="margin: 0; flex: 1;">
                            @csrf
                            @if($photo->is_approved)
                                <button type="submit" style="width: 100%; padding: 6px 10px; border-radius: 6px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 11px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                    Hủy duyệt
                                </button>
                            @else
                                <button type="submit" style="width: 100%; padding: 6px 10px; border-radius: 6px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Duyệt lên LED
                                </button>
                            @endif
                        </form>

                        <form method="POST" action="{{ route('admin.guest_photos.destroy', $photo->id) }}" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn ảnh này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding: 6px 10px; border-radius: 6px; background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 600; cursor: pointer;" title="Xóa ảnh">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 24px;">
            {{ $photos->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Lightbox Modal -->
<div id="photoLightbox" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); z-index: 9999; align-items: center; justify-content: center; padding: 24px;">
    <div style="position: relative; max-width: 900px; width: 100%; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);">
        <button type="button" onclick="closeLightbox()" style="position: absolute; top: 12px; right: 12px; z-index: 10; background: rgba(0,0,0,0.5); color: #fff; border: none; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        <div style="max-height: 70vh; background: #0f172a; display: flex; align-items: center; justify-content: center; overflow: hidden;">
            <img id="lightboxImage" src="" alt="Enlarged" style="max-width: 100%; max-height: 70vh; object-fit: contain;">
        </div>
        <div style="padding: 16px 20px; background: #ffffff;">
            <div id="lightboxUploader" style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 4px;"></div>
            <div id="lightboxCaption" style="font-size: 13px; color: #64748b; font-style: italic;"></div>
        </div>
    </div>
</div>

<script>
    // Lightbox functions
    function openLightbox(url, uploader, caption) {
        document.getElementById('lightboxImage').src = url;
        document.getElementById('lightboxUploader').textContent = 'Người gửi: ' + uploader;
        document.getElementById('lightboxCaption').textContent = caption ? ('"' + caption + '"') : 'Không có chú thích kèm theo';
        document.getElementById('photoLightbox').style.display = 'flex';
    }

    function closeLightbox() {
        document.getElementById('photoLightbox').style.display = 'none';
    }

    // Batch Selection Logic
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllCheckbox');
        const checkboxes = document.querySelectorAll('.photoCheckbox');
        const batchBar = document.getElementById('batchActionBar');
        const countSpan = document.getElementById('selectedCount');

        function updateBatchBar() {
            const checked = document.querySelectorAll('.photoCheckbox:checked');
            const count = checked.length;
            countSpan.textContent = count;
            if (count > 0) {
                batchBar.style.display = 'flex';
            } else {
                batchBar.style.display = 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBatchBar();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateBatchBar();
                if (!this.checked && selectAll) {
                    selectAll.checked = false;
                }
            });
        });
    });

    function submitBatchAction(type) {
        const checked = document.querySelectorAll('.photoCheckbox:checked');
        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một bức ảnh.');
            return;
        }

        if (type === 'approve') {
            const form = document.getElementById('batchApproveForm');
            const container = document.getElementById('batchApproveInputs');
            container.innerHTML = '';
            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = cb.value;
                container.appendChild(inp);
            });
            form.submit();
        } else if (type === 'delete') {
            if (!confirm('Bạn có chắc chắn muốn xóa vĩnh viễn ' + checked.length + ' bức ảnh đã chọn?')) {
                return;
            }
            const form = document.getElementById('batchDeleteForm');
            const container = document.getElementById('batchDeleteInputs');
            container.innerHTML = '';
            checked.forEach(cb => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = cb.value;
                container.appendChild(inp);
            });
            form.submit();
        }
    }
</script>
@endsection
