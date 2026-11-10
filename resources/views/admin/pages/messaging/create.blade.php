@extends('admin.layouts.master')

@section('title', 'Tạo Đợt Phát Tin Thiệp Mới — Mây Hẹn')
@section('page_title', 'Tạo Đợt Phát Tin Thiệp Mời')

@section('content')
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('admin.messaging.index') }}" style="font-size: 0.85rem; color: #7A756E; text-decoration: none;">
                &larr; Quay lại danh sách chiến dịch
            </a>
        </div>

        <div class="mayhen_admin_card" style="padding: 24px 28px;">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0 0 6px;">
                Cấu Hình Đợt Phát Tin Thiệp Mời & Chống Trùng
            </h2>
            <p style="font-size: 0.82rem; color: var(--mh-admin-muted); margin: 0 0 24px;">
                Hệ thống tự động khử trùng lặp số điện thoại, cá nhân hóa danh xưng theo vai vế và chạy thử nghiệm Dry-Run trước khi phát tin thực tế.
            </p>

            <form action="{{ route('admin.messaging.store') }}" method="POST">
                @csrf

                {{-- Chọn Tiệc Cưới --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                        Tiệc Cưới Mục Tiêu <span style="color: #C0392B;">*</span>
                    </label>
                    <select name="wedding_id" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.9rem; background: #FFF;">
                        <option value="">-- Chọn tiệc cưới cần phát tin --</option>
                        @foreach($weddings as $wedding)
                            <option value="{{ $wedding->id }}" {{ (string) $selectedWeddingId === (string) $wedding->id ? 'selected' : '' }}>
                                #{{ $wedding->id }} &bull; {{ $wedding->groom_name }} & {{ $wedding->bride_name }} ({{ $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') : 'Chưa có ngày' }})
                            </option>
                        @endforeach
                    </select>
                    @error('wedding_id')
                        <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tên Chiến Dịch & Kênh Gửi --}}
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Tên Đợt Phát Tin <span style="color: #C0392B;">*</span>
                        </label>
                        <input type="text" name="campaign_name" value="{{ old('campaign_name', 'Thiệp Mời Chính Thức — Đợt 1') }}" required placeholder="VD: Gửi thiệp mời họ hàng hai bên" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.9rem;">
                        @error('campaign_name')
                            <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Kênh Phát Tin <span style="color: #C0392B;">*</span>
                        </label>
                        <select name="channel" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.9rem; background: #FFF;">
                            <option value="invitation_link">Link Thiệp Định Danh (0đ)</option>
                            <option value="zalo_zns">Zalo ZNS (250đ/tin)</option>
                            <option value="sms_brandname">SMS Brandname (650đ/tin)</option>
                        </select>
                        @error('channel')
                            <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Lọc Theo Nhóm Khách --}}
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                        Lọc Theo Phân Nhóm Khách Mời (Tùy chọn)
                    </label>
                    <input type="text" name="filter_group" value="{{ old('filter_group') }}" placeholder="Để trống nếu muốn gửi cho TOÀN BỘ quan khách. Hoặc nhập: Bạn Chú Rể, Gia Đình Cô Dâu..." style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.9rem;">
                    <div style="font-size: 0.72rem; color: #7A756E; margin-top: 4px;">Khử trùng SĐT vẫn được áp dụng chặt chẽ ngay cả khi chọn toàn bộ khách.</div>
                </div>

                {{-- Mẫu Tin Nhắn Cá Nhân Hóa --}}
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="font-size: 0.82rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                            Nội Dung Mẫu Tin Nhắn <span style="color: #C0392B;">*</span>
                        </label>
                        <span style="font-size: 0.72rem; color: #7A756E;">Hỗ trợ thẻ thay thế tự động</span>
                    </div>

                    {{-- Tags gợi ý nhanh --}}
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px;">
                        <span style="padding: 2px 8px; border-radius: 4px; background: #FAF8F5; border: 1px solid #ECE7E1; font-size: 0.72rem; font-family: monospace; color: #5D5852;">[DANH_XUNG]</span>
                        <span style="padding: 2px 8px; border-radius: 4px; background: #FAF8F5; border: 1px solid #ECE7E1; font-size: 0.72rem; font-family: monospace; color: #5D5852;">[TEN_KHACH]</span>
                        <span style="padding: 2px 8px; border-radius: 4px; background: #FAF8F5; border: 1px solid #ECE7E1; font-size: 0.72rem; font-family: monospace; color: #5D5852;">[CAP_DOI]</span>
                        <span style="padding: 2px 8px; border-radius: 4px; background: #FAF8F5; border: 1px solid #ECE7E1; font-size: 0.72rem; font-family: monospace; color: #5D5852;">[NGAY_CUOI]</span>
                        <span style="padding: 2px 8px; border-radius: 4px; background: #FAF8F5; border: 1px solid #ECE7E1; font-size: 0.72rem; font-family: monospace; color: #2980B9; font-weight: 700;">[LINK_THIEP]</span>
                        <span style="padding: 2px 8px; border-radius: 4px; background: #FAF8F5; border: 1px solid #ECE7E1; font-size: 0.72rem; font-family: monospace; color: #5D5852;">[NHOM_KHACH]</span>
                    </div>

                    <textarea name="message_template" rows="7" required style="width: 100%; padding: 12px 14px; border-radius: 8px; border: 1px solid #D1CCC6; font-size: 0.88rem; font-family: inherit; line-height: 1.5;">{{ old('message_template', $defaultTemplate) }}</textarea>
                    @error('message_template')
                        <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Action Submit --}}
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="{{ route('admin.messaging.index') }}" class="mayhen_admin_btn" style="padding: 10px 18px; font-size: 0.85rem; font-weight: 600; background: #FAF8F5; color: #4A463F; border: 1px solid #D1CCC6; border-radius: 8px; text-decoration: none;">
                        Hủy Bỏ
                    </a>
                    <button type="submit" class="mayhen_admin_btn" style="padding: 10px 22px; font-size: 0.85rem; font-weight: 700; background: var(--mh-admin-gold); color: #FFFFFF; border: none; border-radius: 8px; cursor: pointer;">
                        Tiếp Tục & Kiểm Thử Dry-Run &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

