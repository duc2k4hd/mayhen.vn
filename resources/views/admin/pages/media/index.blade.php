@extends('admin.layouts.master')

@section('title', 'Quản Lý Thư Viện Phương Tiện (Media Library)')
@section('page_title', 'Thư Viện Phương Tiện (Media)')

@section('content')
<div class="mayhen_admin_page_wrapper">
    {{-- Header & Actions --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #9C7A4A; font-weight: 700; margin-bottom: 4px;">
                Mục 7.5 DOCX • Quản Trị Tài Nguyên Lưu Trữ
            </div>
            <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 1.85rem; font-weight: 600; color: #1F1D1A; margin: 0;">
                Thư Viện Phương Tiện & Tài Nguyên Cưới
            </h1>
            <p style="font-size: 0.88rem; color: #7A726A; margin: 4px 0 0;">
                Quản lý tập trung kho nhạc nền MP3, ảnh cưới khách hàng, tài nguyên mẫu thiệp và dọn dẹp tệp rác mồ côi.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.media.orphans') }}" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="border-color: #E65100; color: #E65100; background: #FFF8E1;">
                <span>🧹 Quét Tệp Rác Mồ Côi</span>
            </a>

            <button type="button" onclick="document.getElementById('mayhen_upload_modal').style.display = 'flex';" class="mayhen_admin_btn mayhen_admin_btn_primary">
                <span>+ Tải Lên Tệp Mới</span>
            </button>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E7D32; color: #1B5E20; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #FDEDEC; border-left: 4px solid #E74C3C; color: #922B21; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Storage KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="padding: 16px; border-left: 4px solid #9C7A4A;">
            <div style="font-size: 0.72rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Tổng Dung Lượng</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #1F1D1A;">{{ $stats['formatted_total'] }}</div>
            <div style="font-size: 0.76rem; color: #7A726A; margin-top: 2px;">{{ number_format($stats['total_files']) }} tệp tin trong hệ thống</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 16px; border-left: 4px solid #2E7D32;">
            <div style="font-size: 0.72rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Kho Nhạc Nền (MP3)</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2E7D32;">{{ $stats['categories']['musics']['formatted'] }}</div>
            <div style="font-size: 0.76rem; color: #7A726A; margin-top: 2px;">{{ $stats['categories']['musics']['count'] }} bài hát lãng mạn</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 16px; border-left: 4px solid #1565C0;">
            <div style="font-size: 0.72rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Ảnh Cưới Khách Hàng</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #1565C0;">{{ $stats['categories']['weddings']['formatted'] }}</div>
            <div style="font-size: 0.76rem; color: #7A726A; margin-top: 2px;">{{ $stats['categories']['weddings']['count'] }} ảnh album & photo drop</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 16px; border-left: 4px solid #6A1B9A;">
            <div style="font-size: 0.72rem; text-transform: uppercase; color: #7A726A; font-weight: 700; margin-bottom: 4px;">Mẫu Thiệp & Assets</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #6A1B9A;">{{ $stats['categories']['templates']['formatted'] }}</div>
            <div style="font-size: 0.76rem; color: #7A726A; margin-top: 2px;">{{ $stats['categories']['templates']['count'] }} tài nguyên đồ họa</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mayhen_admin_card" style="padding: 16px 20px; margin-bottom: 20px;">
        <form action="{{ route('admin.media.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 220px;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Tìm tên tệp hoặc đường dẫn..." 
                    class="mayhen_admin_input"
                    style="width: 100%;"
                >
            </div>

            <div style="min-width: 170px;">
                <select name="category" class="mayhen_admin_select" style="width: 100%;">
                    <option value="">Tất cả phân loại</option>
                    <option value="musics" {{ request('category') === 'musics' ? 'selected' : '' }}>Kho Nhạc Nền MP3</option>
                    <option value="weddings" {{ request('category') === 'weddings' ? 'selected' : '' }}>Ảnh Cưới Khách Hàng</option>
                    <option value="templates" {{ request('category') === 'templates' ? 'selected' : '' }}>Tài Nguyên Mẫu Thiệp</option>
                    <option value="assets" {{ request('category') === 'assets' ? 'selected' : '' }}>Biểu Tượng & Hoa Văn</option>
                    <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Tài Liệu & Khác</option>
                </select>
            </div>

            <div style="min-width: 150px;">
                <select name="type" class="mayhen_admin_select" style="width: 100%;">
                    <option value="">Loại định dạng</option>
                    <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>Âm Thanh (Audio)</option>
                    <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Hình Ảnh (Image)</option>
                    <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>Tài Liệu (Doc/PDF)</option>
                </select>
            </div>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 8px 18px;">
                Lọc Tệp
            </button>

            @if(request()->hasAny(['search', 'category', 'type']))
                <a href="{{ route('admin.media.index') }}" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="padding: 8px 14px;">
                    Đặt Lại
                </a>
            @endif
        </form>
    </div>

    {{-- Media Grid View --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
        @forelse($paginatedFiles as $file)
            <div class="mayhen_admin_card" style="padding: 14px; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden;">
                {{-- Preview Area --}}
                <div style="height: 150px; background: #FAF8F5; border-radius: 8px; border: 1px solid #EFE8DD; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; overflow: hidden; position: relative;">
                    @if($file['type'] === 'image')
                        <img 
                            src="{{ $file['url'] }}" 
                            alt="{{ $file['name'] }}" 
                            style="width: 100%; height: 100%; object-fit: cover;"
                            loading="lazy"
                            onerror="this.src='/assets/client/images/template-placeholder.webp'"
                        >
                    @elseif($file['type'] === 'audio')
                        <div style="text-align: center; padding: 12px; width: 100%;">
                            <div style="font-size: 2.2rem; margin-bottom: 6px;">🎵</div>
                            <audio controls style="width: 100%; height: 32px;" preload="none">
                                <source src="{{ $file['url'] }}" type="audio/mpeg">
                                Trình duyệt không hỗ trợ audio player.
                            </audio>
                        </div>
                    @else
                        <div style="text-align: center; color: #7A726A;">
                            <div style="font-size: 2.5rem; margin-bottom: 4px;">📄</div>
                            <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700;">{{ $file['ext'] }}</span>
                        </div>
                    @endif

                    <span style="position: absolute; top: 6px; right: 6px; font-size: 0.68rem; background: rgba(31,29,26,0.75); color: #FFFFFF; padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">
                        {{ $file['ext'] }}
                    </span>
                </div>

                {{-- File Meta Info --}}
                <div>
                    <strong style="display: block; font-size: 0.84rem; color: #1F1D1A; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;" title="{{ $file['name'] }}">
                        {{ $file['name'] }}
                    </strong>
                    <div style="font-size: 0.75rem; color: #7A726A; display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span>{{ $file['formatted_size'] }}</span>
                        <span>{{ $file['modified_formatted'] }}</span>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; gap: 6px; padding-top: 10px; border-top: 1px dashed #EFE8DD;">
                    <a href="{{ $file['url'] }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_sm" style="flex: 1; text-align: center; justify-content: center; background: #FAF8F5;" title="Xem trực tiếp">
                        Mở
                    </a>

                    <button 
                        type="button" 
                        class="mayhen_admin_btn mayhen_admin_btn_sm" 
                        style="flex: 1; text-align: center; justify-content: center; background: #FAF8F5;"
                        onclick="navigator.clipboard.writeText('{{ $file['url'] }}'); alert('Đã sao chép link URL tệp!');"
                        title="Sao chép liên kết"
                    >
                        Copy URL
                    </button>

                    <form action="{{ route('admin.media.destroy') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tệp này khỏi storage không?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="path" value="{{ base64_encode($file['path']) }}">
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_sm" style="background: #FDEDEC; color: #922B21; border-color: #F5B7B1;" title="Xóa tệp">
                            🗑
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; color: #7A726A;">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">📂</div>
                <strong style="color: #1F1D1A; display: block; margin-bottom: 4px;">Không tìm thấy tệp tin nào phù hợp</strong>
                <span style="font-size: 0.85rem;">Hãy thử thay đổi từ khóa tìm kiếm hoặc bỏ bớt bộ lọc danh mục.</span>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($paginatedFiles->hasPages())
        <div style="padding: 16px 20px; background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px;">
            {{ $paginatedFiles->links() }}
        </div>
    @endif
</div>

{{-- Upload Modal --}}
<div id="mayhen_upload_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 12px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 16px 36px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; margin: 0; color: #1F1D1A;">
                Tải Lên Tệp Media Mới
            </h3>
            <button type="button" onclick="document.getElementById('mayhen_upload_modal').style.display = 'none';" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #7A726A;">✕</button>
        </div>

        <form action="{{ route('admin.media.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: #4A453E; margin-bottom: 6px;">Chọn Tệp Cần Tải (Tối đa 20MB)</label>
                <input type="file" name="file" required class="mayhen_admin_input" style="width: 100%; padding: 8px;">
                <span style="font-size: 0.72rem; color: #7A726A; margin-top: 4px; display: block;">Hỗ trợ: JPG, PNG, WebP, SVG, MP3, WAV, PDF.</span>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: #4A453E; margin-bottom: 6px;">Thư Mục Lưu Trữ Phân Loại</label>
                <select name="folder" class="mayhen_admin_select" style="width: 100%;">
                    <option value="client/images">client/images (Biểu Tượng & Ảnh Dùng Chung)</option>
                    <option value="client/musics">client/musics (Kho Nhạc Nền MP3)</option>
                    <option value="admin/assets">admin/assets (Tài Nguyên Bảng Quản Trị)</option>
                    <option value="weddings">weddings (Thư Mục Ảnh Cưới)</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('mayhen_upload_modal').style.display = 'none';" class="mayhen_admin_btn mayhen_admin_btn_secondary">Hủy</button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">Bắt Đầu Tải Lên</button>
            </div>
        </form>
    </div>
</div>
@endsection

