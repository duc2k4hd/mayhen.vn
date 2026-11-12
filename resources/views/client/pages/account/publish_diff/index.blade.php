@extends('client.pages.account.layout')

@section('page_title', 'Thanh Tra & So Sánh Bản Nháp vs Bản Xuất Bản | ' . config('app.name'))
@section('account_breadcrumb', 'So Sánh Bản Nháp')

@section('account_content')
<div class="mayhen_publish_diff_studio" style="max-width: 1180px; margin: 0 auto;">
    {{-- Topbar Điều Hướng --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp cưới</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Thanh Tra & So Sánh Bản Nháp (Publish Diff Inspector)
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Kiểm duyệt toàn diện mọi thay đổi giữa <strong>Bản Đang Phát Hành</strong> và <strong>Bản Nháp Chỉnh Sửa</strong> trước khi xuất bản ra công chúng.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" 
               class="mayhen_account_btn" 
               style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #4A453E; padding: 9px 16px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                <span>Xem Bản Live Thực Tế</span>
            </a>

            @if($diff['has_changes'])
                <button type="button" onclick="document.getElementById('mayhen_publish_modal').style.display='flex';" 
                        class="mayhen_account_btn" 
                        style="background: #1F1D1A; color: #FFFFFF; padding: 9px 20px; border-radius: 8px; font-size: 13.5px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Xuất Bản Bản Mới (v{{ ((int)($invitation->revision_id ?? 0)) + 1 }})</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Thông Báo Flash --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div style="background: #EBF5FB; border: 1px solid #D4E6F1; color: #1B4F72; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('info') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #FDE8E8; border: 1px solid #FBD5D5; color: #9B1C1C; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Thanh Trạng Thái Phiên Bản --}}
    <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <div>
                    <span style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; color: #8C827A; display: block; font-weight: 600;">Phiên Bản Đang Live</span>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <span style="background: #F3F4F6; color: #1F1D1A; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px;">
                            v{{ $invitation->revision_id ?? 1 }}.0
                        </span>
                        <span style="font-size: 13px; color: #555;">
                            {{ $invitation->published_at ? 'Xuất bản: ' . \Carbon\Carbon::parse($invitation->published_at)->format('d/m/Y H:i') : 'Chưa từng xuất bản' }}
                        </span>
                    </div>
                </div>

                <div style="color: #D8CEBD; font-size: 20px;">&rarr;</div>

                <div>
                    <span style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.08em; color: #8C827A; display: block; font-weight: 600;">Bản Nháp Chờ Duyệt</span>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                        <span style="background: {{ $diff['has_changes'] ? '#FEF3C7; color: #92400E;' : '#F3F4F6; color: #6B7280;' }} padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 13px;">
                            v{{ ((int)($invitation->revision_id ?? 0)) + 1 }}.0 (Draft)
                        </span>
                        <span style="font-size: 13px; color: {{ $diff['has_changes'] ? '#B45309;' : '#6B7280;' }} font-weight: 500;">
                            {{ $diff['has_changes'] ? 'Có ' . $diff['total_changes'] . ' trường thay đổi' : 'Đồng bộ 100% với bản Live (Không có thay đổi)' }}
                        </span>
                    </div>
                </div>
            </div>

            @if($diff['has_changes'])
                <form action="{{ route('account.publish_diff.discard', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy toàn bộ thay đổi nháp và hoàn tác về bản Live đang chạy không?');">
                    @csrf
                    <button type="submit" style="background: none; border: 1px solid #E5E7EB; color: #DC2626; padding: 8px 14px; border-radius: 6px; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                        <span>Hủy Thay Đổi Nháp (Discard)</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Cảnh Báo Rủi Ro Nghiêm Trọng (Nếu Đổi Ngày / Giờ / Địa Điểm) --}}
    @if($diff['has_critical_schedule_change'])
        <div style="background: #FEF2F2; border: 1px solid #FCA5A5; border-radius: 12px; padding: 18px 22px; margin-bottom: 24px;">
            <div style="display: flex; align-items: flex-start; gap: 14px;">
                <div style="background: #EF4444; color: #FFFFFF; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-weight: 700;">!</div>
                <div style="flex: 1;">
                    <h4 style="margin: 0 0 6px 0; color: #991B1B; font-size: 16px; font-weight: 600;">
                        Cảnh Báo Thay Đổi Lịch Trình Tiệc Cưới (Breaking Change Detected)
                    </h4>
                    <p style="margin: 0 0 12px 0; color: #7F1D1D; font-size: 13.5px; line-height: 1.5;">
                        Bạn đang thay đổi <strong>Ngày cưới, Giờ tiệc hoặc Địa chỉ sảnh cưới</strong>. Nếu thiệp đã được gửi tới khách mời trước đây, việc thay đổi đột ngột có thể khiến khách nhầm lẫn hoặc đến sai giờ.
                    </p>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="{{ route('account.reschedule.index', $invitation->id) }}" target="_blank" 
                           style="background: #DC2626; color: #FFFFFF; padding: 7px 14px; border-radius: 6px; font-size: 12.5px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                            <span>Mở Công Cụ Thông Báo Dời Lịch Cưới &rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Bảng So Sánh Chi Tiết Diff --}}
    @if(! $diff['has_changes'])
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 48px 24px; text-align: center; margin-bottom: 30px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #DEF7EC; color: #046C4E; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 8px 0;">
                Bản Nháp Hiện Tại Hoàn Toàn Trùng Khớp Với Bản Live
            </h3>
            <p style="font-size: 13.5px; color: #8C827A; max-width: 540px; margin: 0 auto;">
                Không có sự khác biệt nào giữa dữ liệu đang chỉnh sửa và phiên bản đang phát hành cho khách mời. Khi bạn chỉnh sửa thông tin trong Studio, các thay đổi sẽ tự động xuất hiện tại đây để bạn kiểm duyệt.
            </p>
        </div>
    @else
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; overflow: hidden; margin-bottom: 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
            {{-- Header Bảng --}}
            <div style="display: grid; grid-template-columns: 240px 1fr 1fr; background: #FAF8F5; border-bottom: 1px solid #EFE8DD; padding: 14px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #555;">
                <div>Trường Thông Tin</div>
                <div style="color: #6B7280; display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #9CA3AF;"></span>
                    <span>Bản Đang Phát Hành (Live v{{ $invitation->revision_id ?? 1 }})</span>
                </div>
                <div style="color: #047857; display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span>
                    <span>Bản Nháp Mới Chuẩn Bị Xuất Bản (Draft v{{ ((int)($invitation->revision_id ?? 0)) + 1 }})</span>
                </div>
            </div>

            {{-- Các Nhóm Thay Đổi --}}
            @foreach($categoryLabels as $catKey => $catLabel)
                @if(! empty($diff['grouped_diffs'][$catKey]))
                    <div style="background: #FDFBF7; padding: 10px 20px; border-bottom: 1px solid #F0EAE1; font-size: 12.5px; font-weight: 700; color: #8C827A; letter-spacing: 0.04em;">
                        {{ $catLabel }} ({{ count($diff['grouped_diffs'][$catKey]) }} thay đổi)
                    </div>

                    @foreach($diff['grouped_diffs'][$catKey] as $item)
                        <div style="display: grid; grid-template-columns: 240px 1fr 1fr; border-bottom: 1px solid #F3EDE3; padding: 16px 20px; align-items: center; font-size: 13.5px;">
                            {{-- Cột Tên Trường & Mức Độ Rủi Ro --}}
                            <div>
                                <div style="font-weight: 600; color: #1F1D1A; margin-bottom: 4px;">{{ $item['label'] }}</div>
                                @if($item['risk'] === 'critical')
                                    <span style="background: #FEE2E2; color: #991B1B; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                        Rủi ro cao (Lịch trình)
                                    </span>
                                @elseif($item['risk'] === 'warning')
                                    <span style="background: #FEF3C7; color: #92400E; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                        Lưu ý (Tài khoản/Tên)
                                    </span>
                                @else
                                    <span style="background: #F3F4F6; color: #4B5563; font-size: 11px; padding: 2px 7px; border-radius: 4px;">
                                        Nội dung / Media
                                    </span>
                                @endif
                            </div>

                            {{-- Cột Giá Trị Cũ (Live) --}}
                            <div style="padding-right: 20px; color: #6B7280; text-decoration: {{ $item['is_cleared'] ? 'line-through' : 'none' }}; word-break: break-word;">
                                <div style="background: #FEF2F2; color: #991B1B; padding: 8px 12px; border-radius: 6px; border: 1px dashed #FCA5A5; font-size: 13px;">
                                    {{ $item['old_display'] }}
                                </div>
                            </div>

                            {{-- Cột Giá Trị Mới (Draft) --}}
                            <div style="word-break: break-word;">
                                <div style="background: #F0FDF4; color: #166534; padding: 8px 12px; border-radius: 6px; border: 1px solid #BBF7D0; font-size: 13px; font-weight: 500;">
                                    {{ $item['new_display'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    @endif

    {{-- Lịch Sử Các Bản Đã Xuất Bản Trước Đây (Version History Timeline) --}}
    <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 16px 0; font-weight: 600;">
            Lịch Sử Các Bản Đã Xuất Bản (Publish Version Ledger)
        </h3>

        @if(empty($history))
            <p style="font-size: 13.5px; color: #8C827A; margin: 0;">
                Chưa có lịch sử các bản phát hành được ghi lại trước đây. Mỗi lần bạn bấm "Xuất Bản", hệ thống sẽ lưu một snapshot phiên bản độc lập để dễ dàng xem lại và khôi phục khi cần.
            </p>
        @else
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @foreach($history as $record)
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 14px 18px; background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 10px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <span style="background: #1F1D1A; color: #FFFFFF; font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                                    v{{ $record['revision_id'] }}.0
                                </span>
                                <strong style="font-size: 14px; color: #1F1D1A;">{{ $record['change_note'] ?? 'Bản cập nhật v' . $record['revision_id'] }}</strong>
                                <span style="font-size: 12.5px; color: #8C827A;">• {{ \Carbon\Carbon::parse($record['published_at'])->format('d/m/Y H:i') }}</span>
                            </div>
                            <div style="font-size: 12.5px; color: #6B7280;">
                                Bao gồm <strong>{{ $record['changes_count'] ?? 0 }}</strong> thay đổi
                                @if(!empty($record['critical_count']) && $record['critical_count'] > 0)
                                    • <span style="color: #DC2626; font-weight: 600;">{{ $record['critical_count'] }} thay đổi lịch trình</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <form action="{{ route('account.publish_diff.rollback', [$invitation->id, $record['revision_id']]) }}" method="POST" onsubmit="return confirm('Bạn có muốn tải lại dữ liệu của phiên bản v{{ $record['revision_id'] }} vào bản nháp để xem xét không?');">
                                @csrf
                                <button type="submit" style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #4A453E; padding: 6px 14px; border-radius: 6px; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
                                    <span>Khôi Phục Bản Này Vào Nháp</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Modal Xác Nhận Xuất Bản Chính Thức --}}
<div id="mayhen_publish_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 520px; width: 100%; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 0 0 8px 0; font-weight: 600;">
            Xác Nhận Xuất Bản Bản Cập Nhật
        </h3>
        <p style="font-size: 13.5px; color: #6B7280; margin: 0 0 20px 0; line-height: 1.5;">
            Sau khi xuất bản, các thay đổi sẽ hiển thị ngay lập tức tới quan khách truy cập đường link thiệp cưới.
        </p>

        <form action="{{ route('account.publish_diff.publish', $invitation->id) }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Ghi chú phiên bản (Tùy chọn)
                </label>
                <input type="text" name="change_note" placeholder="Ví dụ: Đổi giờ tiệc sang 18h00, thêm ảnh cưới mới" 
                       style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('mayhen_publish_modal').style.display='none';" 
                        style="background: #F3F4F6; border: none; color: #4B5563; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; cursor: pointer; font-weight: 500;">
                    Hủy bỏ
                </button>
                <button type="submit" 
                        style="background: #1F1D1A; border: none; color: #FFFFFF; padding: 10px 22px; border-radius: 8px; font-size: 13.5px; cursor: pointer; font-weight: 600;">
                    Xác Nhận Xuất Bản Ngay
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

