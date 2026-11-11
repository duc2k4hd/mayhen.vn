@extends('admin.layouts.master')

@section('title', 'Dọn Dẹp Tệp Rác Mồ Côi (Orphaned Assets Cleaner)')
@section('page_title', 'Dọn Dẹp Tệp Rác (Orphaned Assets)')

@section('content')
<div class="mayhen_admin_page_wrapper">
    {{-- Header & Breadcrumbs --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #E65100; font-weight: 700; margin-bottom: 4px;">
                Mục 7.5 DOCX • File Không Còn Được Tham Chiếu
            </div>
            <h1 style="font-size: 1.85rem; font-weight: 600; color: #1F1D1A; margin: 0;">
                Thanh Tra & Dọn Dẹp Tệp Mồ Côi (Orphaned Media)
            </h1>
            <p style="font-size: 0.88rem; color: #7A726A; margin: 4px 0 0;">
                Tự động quét các tệp tin trong Storage không còn liên kết với bất kỳ Đám Cưới, Thiệp, Khách Mời hay Mẫu Thiệp nào trong Database.
            </p>
        </div>

        <div>
            <a href="{{ route('admin.media.index') }}" class="mayhen_admin_btn mayhen_admin_btn_secondary">
                ← Quay Lại Thư Viện Media
            </a>
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

    {{-- Scan Summary Card --}}
    <div class="mayhen_admin_card" style="padding: 20px; margin-bottom: 24px; border-left: 4px solid #E65100; background: #FFFDF9;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #E65100;">Kết Quả Rà Soát Tệp Tin Mồ Côi</span>
                <div style="font-size: 1.6rem; font-weight: 700; color: #1F1D1A; margin-top: 4px;">
                    Phát hiện <span style="color: #E65100;">{{ number_format($orphanData['total_orphans']) }}</span> tệp không còn được tham chiếu
                </div>
                <div style="font-size: 0.84rem; color: #7A726A; margin-top: 2px;">
                    Tổng dung lượng có thể giải phóng: <strong style="color: #2E7D32;">{{ $orphanData['formatted_orphan_size'] }}</strong>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.media.orphans') }}" class="mayhen_admin_btn mayhen_admin_btn_secondary" style="background: #FFFFFF;">
                    🔄 Quét Lại Storage
                </a>
            </div>
        </div>
    </div>

    @if($orphanData['total_orphans'] > 0)
        <form action="{{ route('admin.media.purge_orphans') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn các tệp mồ côi đã chọn không? Thao tác này sẽ giải phóng dung lượng ổ đĩa vĩnh viễn.');">
            @csrf

            <div class="mayhen_admin_card" style="padding: 16px 20px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.88rem; font-weight: 600; color: #1F1D1A; cursor: pointer;">
                    <input type="checkbox" id="mayhen_select_all_orphans" style="width: 18px; height: 18px; accent-color: #9C7A4A;">
                    <span>Chọn tất cả {{ $orphanData['total_orphans'] }} tệp mồ côi</span>
                </label>

                <button type="submit" class="mayhen_admin_btn" style="background: #E65100; color: #FFFFFF; padding: 10px 22px;">
                    🧹 Dọn Dẹp Các Tệp Đã Chọn
                </button>
            </div>

            <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">Chọn</th>
                            <th style="width: 90px;">Xem Thử</th>
                            <th>Đường Dẫn & Tên Tệp</th>
                            <th style="width: 140px;">Dung Lượng</th>
                            <th style="width: 160px;">Thời Gian Chỉnh Sửa</th>
                            <th style="width: 100px; text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orphanData['orphans'] as $orphan)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="paths[]" value="{{ base64_encode($orphan['path']) }}" class="mayhen_orphan_checkbox" style="width: 17px; height: 17px; accent-color: #9C7A4A;">
                                </td>
                                <td>
                                    @php
                                        $ext = strtolower(pathinfo($orphan['path'], PATHINFO_EXTENSION));
                                    @endphp
                                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                        <img src="{{ $orphan['url'] }}" alt="{{ $orphan['name'] }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #EFE8DD;" loading="lazy">
                                    @elseif(in_array($ext, ['mp3', 'wav', 'ogg']))
                                        <span style="font-size: 1.8rem;">🎵</span>
                                    @else
                                        <span style="font-size: 1.8rem;">📄</span>
                                    @endif
                                </td>
                                <td>
                                    <strong style="display: block; font-size: 0.88rem; color: #1F1D1A; margin-bottom: 2px;">
                                        {{ $orphan['name'] }}
                                    </strong>
                                    <span style="font-family: monospace; font-size: 0.75rem; color: #7A726A; word-break: break-all;">
                                        {{ $orphan['path'] }}
                                    </span>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: #E65100; font-size: 0.88rem;">
                                        {{ $orphan['formatted_size'] }}
                                    </span>
                                </td>
                                <td style="font-size: 0.82rem; color: #7A726A;">
                                    {{ $orphan['modified_formatted'] }}
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ $orphan['url'] }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_sm" style="background: #FAF8F5;">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    @else
        <div class="mayhen_admin_card" style="text-align: center; padding: 60px 20px; color: #7A726A;">
            <div style="font-size: 3rem; margin-bottom: 10px;">✨</div>
            <h3 style="font-size: 1.6rem; font-weight: 600; color: #1F1D1A; margin: 0 0 6px;">
                Kho Lưu Trữ Sạch Sẽ & Tối Ưu Tuyệt Đối
            </h3>
            <p style="font-size: 0.88rem; max-width: 500px; margin: 0 auto;">
                Không phát hiện tệp rác mồ côi nào trong hệ thống. Toàn bộ tệp tin trong Storage đều đang được liên kết chính xác với dữ liệu ngày cưới của các cặp đôi.
            </p>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('mayhen_select_all_orphans');
        const checkboxes = document.querySelectorAll('.mayhen_orphan_checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
            });
        }
    });
</script>
@endsection

