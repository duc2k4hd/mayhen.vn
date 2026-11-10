@extends('client.pages.account.layout')

@section('page_title', 'Lịch Trình & Sự Kiện Cưới | ' . config('app.name'))
@section('account_breadcrumb', 'Lịch Trình & Sự Kiện Cưới')

@section('account_content')
<div class="mayhen_account_events_wrapper">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0;">
                Lịch Trình & Sự Kiện Cưới
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Đám cưới: <strong style="color: #1F1D1A;">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> • Quản lý nhiều lễ cưới độc lập (Lễ Vu Quy, Thành Hôn, Tiệc Cưới...).
            </p>
        </div>

        <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openCreateModal()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Thêm Sự Kiện / Lễ Cưới</span>
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Timeline Danh Sách Sự Kiện --}}
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @forelse($events as $event)
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 20px; display: grid; grid-template-columns: auto 1fr auto; gap: 20px; align-items: center; position: relative;">
                {{-- Date Box --}}
                <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; width: 80px; height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <span style="font-size: 11px; text-transform: uppercase; color: #8C827A; letter-spacing: 0.5px;">Tháng {{ $event->event_date ? $event->event_date->format('m') : '--' }}</span>
                    <strong style="font-size: 26px; color: #9C7A4A; font-family: 'Cormorant Garamond', serif; line-height: 1;">
                        {{ $event->event_date ? $event->event_date->format('d') : '--' }}
                    </strong>
                    <span style="font-size: 11px; color: #8C827A;">{{ $event->event_date ? $event->event_date->format('Y') : '' }}</span>
                </div>

                {{-- Event Info --}}
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #FAF8F5; border: 1px solid #EAE3D9; color: #9C7A4A;">
                            {{ $event->type_label }}
                        </span>

                        @if($event->is_active)
                            <span style="font-size: 12px; color: #2E7D32; display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #2E7D32;"></span>
                                Hiển thị trong thiệp
                            </span>
                        @else
                            <span style="font-size: 12px; color: #8C827A; display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; border-radius: 50%; background: #8C827A;"></span>
                                Ẩn khỏi thiệp
                            </span>
                        @endif

                        @if($event->isPast())
                            <span style="font-size: 11px; background: #F3F4F6; color: #6B7280; padding: 2px 6px; border-radius: 4px;">Đã diễn ra</span>
                        @endif
                    </div>

                    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 6px 0;">
                        {{ $event->name }}
                    </h3>

                    <div style="font-size: 13px; color: #555; display: flex; flex-wrap: wrap; gap: 16px;">
                        @if($event->event_time)
                            <span>⏰ Giờ tổ chức: <strong>{{ $event->event_time }}</strong></span>
                        @endif
                        @if($event->lunar_date)
                            <span>🌙 Âm lịch: <strong>{{ $event->lunar_date }}</strong></span>
                        @endif
                        <span>📍 Địa điểm: <strong>{{ $event->venue_name }}</strong></span>
                    </div>

                    @if($event->venue_address)
                        <div style="font-size: 12px; color: #8C827A; margin-top: 4px;">
                            Địa chỉ: {{ $event->venue_address }}
                            @if($event->map_url)
                                • <a href="{{ $event->map_url }}" target="_blank" rel="noopener" style="color: #9C7A4A; text-decoration: underline;">Xem bản đồ Google Maps &nearr;</a>
                            @endif
                        </div>
                    @endif

                    @if($event->dress_code)
                        <div style="font-size: 12px; color: #8C827A; margin-top: 4px;">
                            👗 Trang phục: <span style="color: #1F1D1A;">{{ $event->dress_code }}</span>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="openEditModal({{ json_encode($event) }})">
                        Sửa
                    </button>

                    @if($events->count() > 1)
                        <form method="POST" action="{{ route('account.events.destroy', [$invitation->id, $event->id]) }}" onsubmit="return confirm('Bạn có chắc muốn xóa sự kiện này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm" style="background: transparent; color: #9B1C1C; border: 1px solid #F8B4B4;">
                                Xóa
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div style="background: #FFFFFF; border: 1px dashed #EAE3D9; border-radius: 14px; padding: 40px; text-align: center; color: #8C827A;">
                Chưa có sự kiện cưới nào được thiết lập. Hãy bấm nút "Thêm Sự Kiện / Lễ Cưới" để bắt đầu.
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Thêm / Chỉnh Sửa Sự Kiện --}}
<div id="eventModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 540px; width: 92%; margin: 60px auto; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <h3 id="modalTitle" style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin-top: 0; margin-bottom: 16px;">
            Thêm Sự Kiện Cưới
        </h3>

        <form id="eventForm" method="POST" action="{{ route('account.events.store', $invitation->id) }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Tên Sự Kiện / Nghi Lễ <span style="color: #9B1C1C;">*</span></label>
                <input type="text" name="name" id="eventName" placeholder="Ví dụ: Lễ Vu Quy, Lễ Thành Hôn, Tiệc Cưới..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Phân Loại</label>
                    <select name="event_type" id="eventType" style="width: 100%; padding: 10px 12px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;">
                        <option value="banquet">Tiệc Cưới & Mừng Hạnh Phúc</option>
                        <option value="ceremony_bride">Lễ Vu Quy (Nhà Gái)</option>
                        <option value="ceremony_groom">Lễ Thành Hôn (Nhà Trai)</option>
                        <option value="engagement">Lễ Đính Hôn / Ăn Hỏi</option>
                        <option value="after_party">Tiệc Thân Mật / After Party</option>
                        <option value="other">Sự Kiện Khác</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Giờ Tổ Chức</label>
                    <input type="text" name="event_time" id="eventTime" placeholder="Ví dụ: 09:30, 11:00, 18:00" style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Ngày Dương Lịch <span style="color: #9B1C1C;">*</span></label>
                    <input type="date" name="event_date" id="eventDate" style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;" required>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Ngày Âm Lịch</label>
                    <input type="text" name="lunar_date" id="eventLunarDate" placeholder="Ví dụ: 16 tháng 10 năm Giáp Thìn" style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Tên Địa Điểm Tổ Chức <span style="color: #9B1C1C;">*</span></label>
                <input type="text" name="venue_name" id="eventVenueName" placeholder="Ví dụ: Tư gia Nhà Gái, White Palace Hoàng Văn Thụ..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;" required>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Địa Chỉ Chi Tiết</label>
                <textarea name="venue_address" id="eventVenueAddress" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Đường Dẫn Bản Đồ Google Maps</label>
                <input type="url" name="map_url" id="eventMapUrl" placeholder="https://maps.google.com/..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Gợi Ý Trang Phục (Dresscode)</label>
                <input type="text" name="dress_code" id="eventDressCode" placeholder="Ví dụ: Trang phục trang nhã, tone Trắng / Be / Pastel..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1F1D1A; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="eventIsActive" value="1" checked>
                    <span>Hiển thị sự kiện này trên thiệp cưới trực tuyến</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeEventModal()">
                    Hủy Bỏ
                </button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                    Lưu Sự Kiện
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalTitle').textContent = 'Thêm Sự Kiện Cưới';
        document.getElementById('eventForm').action = '{{ route("account.events.store", $invitation->id) }}';
        document.getElementById('formMethod').value = 'POST';

        document.getElementById('eventName').value = '';
        document.getElementById('eventType').value = 'banquet';
        document.getElementById('eventDate').value = '{{ $wedding->wedding_date ? $wedding->wedding_date->format("Y-m-d") : date("Y-m-d") }}';
        document.getElementById('eventTime').value = '11:00';
        document.getElementById('eventLunarDate').value = '';
        document.getElementById('eventVenueName').value = '';
        document.getElementById('eventVenueAddress').value = '';
        document.getElementById('eventMapUrl').value = '';
        document.getElementById('eventDressCode').value = '';
        document.getElementById('eventIsActive').checked = true;

        document.getElementById('eventModal').style.display = 'block';
    }

    function openEditModal(event) {
        document.getElementById('modalTitle').textContent = 'Chỉnh Sửa: ' + event.name;
        document.getElementById('eventForm').action = '/tai-khoan/thiep/{{ $invitation->id }}/su-kien/' + event.id;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('eventName').value = event.name || '';
        document.getElementById('eventType').value = event.event_type || 'banquet';
        document.getElementById('eventDate').value = event.event_date ? event.event_date.substring(0, 10) : '';
        document.getElementById('eventTime').value = event.event_time || '';
        document.getElementById('eventLunarDate').value = event.lunar_date || '';
        document.getElementById('eventVenueName').value = event.venue_name || '';
        document.getElementById('eventVenueAddress').value = event.venue_address || '';
        document.getElementById('eventMapUrl').value = event.map_url || '';
        document.getElementById('eventDressCode').value = event.dress_code || '';
        document.getElementById('eventIsActive').checked = Boolean(event.is_active);

        document.getElementById('eventModal').style.display = 'block';
    }

    function closeEventModal() {
        document.getElementById('eventModal').style.display = 'none';
    }
</script>
@endsection

