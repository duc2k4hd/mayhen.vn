@extends('client.pages.account.layout')

@section('page_title', 'Nhật Ký Thay Đổi Giờ Chót Ngày Cưới | ' . config('app.name'))
@section('account_breadcrumb', 'Nhật Ký Giờ Chót')

@section('account_content')
<div class="mayhen_last_minute_studio" style="max-width: 1180px; margin: 0 auto;">
    {{-- Topbar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp cưới</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Nhật Ký Thay Đổi Giờ Chót Ngày Cưới (Last-Minute Wedding Log)
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Ghi chép và điều phối tức thì mọi phát sinh cấp bách: kê thêm ghế, đổi món chay, mượn đạo cụ, phân công nhân sự.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('account.last_minute_logs.export', $invitation->id) }}" 
               class="mayhen_account_btn" 
               style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #4A453E; padding: 10px 16px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Xuất CSV Đối Soát Chi Phí</span>
            </a>

            <button type="button" onclick="document.getElementById('mayhen_add_log_modal').style.display='flex';" 
                    class="mayhen_account_btn" 
                    style="background: #1F1D1A; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>+ Ghi Nhận Phát Sinh Mới</span>
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 4 Card KPI --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Tổng Lượt Phát Sinh</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #1F1D1A; line-height: 1.1;">
                {{ $stats['total_logs'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">vụ việc</span>
            </div>
            <div style="font-size: 12.5px; color: #DC2626; margin-top: 4px; font-weight: 500;">
                {{ $stats['urgent_count'] }} việc cần xử lý khẩn cấp
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Đang Chờ Xử Lý</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #D97706; line-height: 1.1;">
                {{ $stats['pending_count'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">chưa làm</span>
            </div>
            <div style="font-size: 12.5px; color: #6B7280; margin-top: 4px;">
                Cần phân công người phụ trách
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Đang Tiến Hành</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #2563EB; line-height: 1.1;">
                {{ $stats['in_progress_count'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">đang làm</span>
            </div>
            <div style="font-size: 12.5px; color: #2563EB; margin-top: 4px;">
                Đội ngũ đang giải quyết tại sảnh
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Đã Xong / Chi Phí</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #047857; line-height: 1.1;">
                {{ $stats['resolved_count'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">hoàn tất</span>
            </div>
            <div style="font-size: 12.5px; color: #555; margin-top: 4px;">
                Chi phí ước tính: <strong>{{ number_format($stats['total_cost_impact']) }} đ</strong>
            </div>
        </div>
    </div>

    {{-- Mẫu Câu Nhập Nhanh 1-Chạm --}}
    <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <span style="font-size: 13px; font-weight: 600; color: #8C827A;">Mẫu Câu Nhanh:</span>
        <button type="button" onclick="quickFillForm('Kê thêm 1 ghế phụ', 'seating', 'urgent')" 
                style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 5px 12px; border-radius: 6px; font-size: 12.5px; cursor: pointer;">
            + Kê thêm 1 ghế phụ
        </button>
        <button type="button" onclick="quickFillForm('Đổi món chay thanh tịnh', 'dietary', 'high')" 
                style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 5px 12px; border-radius: 6px; font-size: 12.5px; cursor: pointer;">
            + Đổi món chay
        </button>
        <button type="button" onclick="quickFillForm('Lấy 1 ghế ăn dặm cho trẻ em', 'seating', 'normal')" 
                style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 5px 12px; border-radius: 6px; font-size: 12.5px; cursor: pointer;">
            + Thêm ghế trẻ em
        </button>
        <button type="button" onclick="quickFillForm('Mượn 5 chiếc ô che mưa sảnh đón', 'logistics', 'high')" 
                style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 5px 12px; border-radius: 6px; font-size: 12.5px; cursor: pointer;">
            + Mượn ô che mưa
        </button>
        <button type="button" onclick="quickFillForm('Nhờ MC xướng tên bác trưởng họ lên chụp ảnh', 'stage', 'normal')" 
                style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 5px 12px; border-radius: 6px; font-size: 12.5px; cursor: pointer;">
            + Nhắc MC sân khấu
        </button>
    </div>

    {{-- Bộ Lọc Tìm Kiếm --}}
    <form method="GET" action="{{ route('account.last_minute_logs.index', $invitation->id) }}" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Tìm theo tiêu đề, người phụ trách, bàn..." 
               style="flex: 1; min-width: 200px; padding: 8px 12px; border: 1px solid #D8CEBD; border-radius: 6px; font-size: 13px; outline: none;">

        <select name="category" style="padding: 8px 12px; border: 1px solid #D8CEBD; border-radius: 6px; font-size: 13px; outline: none;">
            <option value="">Tất cả danh mục</option>
            @foreach($categories as $catKey => $catLabel)
                <option value="{{ $catKey }}" {{ ($filters['category'] ?? '') === $catKey ? 'selected' : '' }}>{{ $catLabel }}</option>
            @endforeach
        </select>

        <select name="status" style="padding: 8px 12px; border: 1px solid #D8CEBD; border-radius: 6px; font-size: 13px; outline: none;">
            <option value="">Tất cả trạng thái</option>
            @foreach($statuses as $stKey => $stLabel)
                <option value="{{ $stKey }}" {{ ($filters['status'] ?? '') === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
            @endforeach
        </select>

        <select name="priority" style="padding: 8px 12px; border: 1px solid #D8CEBD; border-radius: 6px; font-size: 13px; outline: none;">
            <option value="">Tất cả mức độ</option>
            @foreach($priorities as $pKey => $pLabel)
                <option value="{{ $pKey }}" {{ ($filters['priority'] ?? '') === $pKey ? 'selected' : '' }}>{{ $pLabel }}</option>
            @endforeach
        </select>

        <button type="submit" style="background: #1F1D1A; color: #FFFFFF; border: none; padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer;">
            Lọc
        </button>

        @if(!empty($filters['search']) || !empty($filters['category']) || !empty($filters['status']) || !empty($filters['priority']))
            <a href="{{ route('account.last_minute_logs.index', $invitation->id) }}" style="color: #DC2626; font-size: 13px; text-decoration: none;">
                Xóa lọc
            </a>
        @endif
    </form>

    {{-- Bảng Danh Sách Phát Sinh --}}
    @if(empty($logs))
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 48px 24px; text-align: center;">
            <p style="font-size: 14px; color: #8C827A; margin: 0;">Chưa có phát sinh nào được ghi nhận. Bấm "+ Ghi Nhận Phát Sinh Mới" để thêm vụ việc cần xử lý.</p>
        </div>
    @else
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 14px 18px;">Phát Sinh / Tiêu Đề</th>
                        <th style="padding: 14px 18px;">Ưu Tiên</th>
                        <th style="padding: 14px 18px;">Bàn / Khách</th>
                        <th style="padding: 14px 18px;">Người Phụ Trách</th>
                        <th style="padding: 14px 18px;">Chi Phí (VNĐ)</th>
                        <th style="padding: 14px 18px;">Trạng Thái</th>
                        <th style="padding: 14px 18px; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr style="border-bottom: 1px solid #F3EDE3;">
                            <td style="padding: 16px 18px;">
                                <div style="font-weight: 600; color: #1F1D1A; margin-bottom: 2px;">{{ $log['title'] }}</div>
                                <span style="font-size: 11.5px; color: #8C827A;">
                                    {{ $categories[$log['category'] ?? ''] ?? $log['category'] }} • {{ \Carbon\Carbon::parse($log['created_at'])->format('H:i d/m') }}
                                </span>
                                @if(!empty($log['note']))
                                    <div style="font-size: 12px; color: #6B7280; margin-top: 4px; font-style: italic;">
                                        "{{ $log['note'] }}"
                                    </div>
                                @endif
                            </td>

                            <td style="padding: 16px 18px;">
                                @if(($log['priority'] ?? '') === 'urgent')
                                    <span style="background: #FEE2E2; color: #991B1B; font-size: 11px; padding: 3px 8px; border-radius: 99px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: #EF4444; display: inline-block;"></span>
                                        Khẩn Cấp
                                    </span>
                                @elseif(($log['priority'] ?? '') === 'high')
                                    <span style="background: #FEF3C7; color: #92400E; font-size: 11px; padding: 3px 8px; border-radius: 99px; font-weight: 600;">
                                        Ưu Tiên Cao
                                    </span>
                                @else
                                    <span style="background: #F3F4F6; color: #4B5563; font-size: 11px; padding: 3px 8px; border-radius: 99px;">
                                        Bình Thường
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 16px 18px;">
                                @if(!empty($log['related_table']))
                                    <div style="font-weight: 500; color: #1F1D1A;">{{ $log['related_table'] }}</div>
                                @endif
                                @if(!empty($log['related_guest_name']))
                                    <div style="font-size: 12px; color: #8C827A;">Khách: {{ $log['related_guest_name'] }}</div>
                                @endif
                                @if(empty($log['related_table']) && empty($log['related_guest_name']))
                                    <span style="color: #9CA3AF;">—</span>
                                @endif
                            </td>

                            <td style="padding: 16px 18px;">
                                <span style="font-weight: 500; color: #4A453E;">{{ $log['assignee_name'] ?: 'Chưa gán' }}</span>
                            </td>

                            <td style="padding: 16px 18px;">
                                @if(!empty($log['cost_impact']) && $log['cost_impact'] > 0)
                                    <span style="font-weight: 600; color: #B45309;">+{{ number_format($log['cost_impact']) }} đ</span>
                                @else
                                    <span style="color: #9CA3AF;">0 đ</span>
                                @endif
                            </td>

                            <td style="padding: 16px 18px;">
                                <form action="{{ route('account.last_minute_logs.update_status', [$invitation->id, $log['id']]) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <select name="status" onchange="this.form.submit();" 
                                            style="padding: 4px 8px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; outline: none; border: 1px solid #D8CEBD;
                                            {{ ($log['status'] ?? '') === 'resolved' ? 'background: #DEF7EC; color: #046C4E;' : (($log['status'] ?? '') === 'in_progress' ? 'background: #DBEAFE; color: #1E40AF;' : 'background: #FEF3C7; color: #92400E;') }}">
                                        @foreach($statuses as $stKey => $stLabel)
                                            <option value="{{ $stKey }}" {{ ($log['status'] ?? '') === $stKey ? 'selected' : '' }}>{{ $stLabel }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>

                            <td style="padding: 16px 18px; text-align: right;">
                                <form action="{{ route('account.last_minute_logs.destroy', [$invitation->id, $log['id']]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bản ghi phát sinh này không?');" style="display: inline-block; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 4px 6px;" title="Xóa phát sinh">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Modal Thêm Phát Sinh Mới --}}
<div id="mayhen_add_log_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 540px; width: 100%; padding: 28px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 0 0 8px 0; font-weight: 600;">
            Ghi Nhận Phát Sinh Giờ Chót
        </h3>
        <p style="font-size: 13.5px; color: #6B7280; margin: 0 0 20px 0;">
            Ghi chép nhanh để đội ngũ lễ tân và ban tổ chức phối hợp giải quyết ngay.
        </p>

        <form action="{{ route('account.last_minute_logs.store', $invitation->id) }}" method="POST">
            @csrf

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Tiêu Đề Phát Sinh (*)</label>
                <input type="text" id="log_modal_title" name="title" required placeholder="Ví dụ: Kê thêm 1 ghế phụ Bàn 08" 
                       style="width: 100%; box-sizing: border-box; padding: 10px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Danh Mục</label>
                    <select id="log_modal_category" name="category" style="width: 100%; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                        @foreach($categories as $catKey => $catLabel)
                            <option value="{{ $catKey }}">{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Mức Độ Ưu Tiên</label>
                    <select id="log_modal_priority" name="priority" style="width: 100%; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                        @foreach($priorities as $pKey => $pLabel)
                            <option value="{{ $pKey }}">{{ $pLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Người Phụ Trách</label>
                    <input type="text" name="assignee_name" placeholder="Bạn Tuấn - Phù rể" 
                           style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Bàn Liên Quan</label>
                    <input type="text" name="related_table" placeholder="Bàn 08 (VIP)" 
                           style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Khách Liên Quan</label>
                    <input type="text" name="related_guest_name" placeholder="Bác Trần Văn An" 
                           style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Chi Phí Phát Sinh (VNĐ)</label>
                    <input type="number" name="cost_impact" value="0" min="0" step="50000" 
                           style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13.5px; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">Ghi Chú Chi Tiết</label>
                <textarea name="note" rows="2" placeholder="Ghi chú thêm cho người xử lý..." 
                          style="width: 100%; box-sizing: border-box; padding: 10px 12px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; outline: none;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('mayhen_add_log_modal').style.display='none';" 
                        style="background: #F3F4F6; border: none; color: #4B5563; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; cursor: pointer; font-weight: 500;">
                    Hủy bỏ
                </button>
                <button type="submit" 
                        style="background: #1F1D1A; border: none; color: #FFFFFF; padding: 10px 22px; border-radius: 8px; font-size: 13.5px; cursor: pointer; font-weight: 600;">
                    Lưu Phát Sinh
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function quickFillForm(title, category, priority) {
        document.getElementById('log_modal_title').value = title;
        document.getElementById('log_modal_category').value = category;
        document.getElementById('log_modal_priority').value = priority;
        document.getElementById('mayhen_add_log_modal').style.display = 'flex';
        document.getElementById('log_modal_title').focus();
    }
</script>
@endsection

