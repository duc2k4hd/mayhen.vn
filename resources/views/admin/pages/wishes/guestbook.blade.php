@extends('admin.layouts.master')

@section('title', 'Sổ Lưu Bút Kỷ Niệm & Album Chữ Ký Điện Tử')

@section('content')
<div class="mayhen_admin_guestbook_container" style="display: flex; flex-direction: column; gap: 24px; font-family: var(--mh-admin-font);">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.wishes.index') }}" style="color: inherit; text-decoration: none;">Kiểm duyệt lời chúc</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Sổ Lưu Bút Kỷ Niệm</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Sổ Lưu Bút Kỷ Niệm &amp; Album Chữ Ký Điện Tử</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Lưu giữ nét chữ ký tay cảm xúc, ghim lời chúc ý nghĩa, đáp lễ tri ân và xuất bản sổ lưu niệm ngày cưới trọn đời.</p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if($activeWedding)
                <a href="{{ route('admin.wishes.guestbook.keepsake_print', $activeWedding->id) }}" target="_blank" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; font-weight: 600; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #1c1917; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    In Sách Kỷ Niệm (Print Keepsake)
                </a>
                <a href="{{ route('admin.wishes.guestbook.export', ['weddingId' => $activeWedding->id, 'tab' => $filters['tab']]) }}" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Xuất Sổ CSV
                </a>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; color: #166534; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 18px; color: #991b1b; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Navigation Tabs Giữa Sổ Lưu Bút vs Kiểm Duyệt vs RSVP -->
    <div style="display: flex; gap: 8px; border-bottom: 1px solid #e7e5e4; padding-bottom: 12px; flex-wrap: wrap;">
        <a href="{{ route('admin.wishes.guestbook.index') }}" class="mayhen_admin_btn" style="background: #292524; color: #ffffff; border: 1px solid #292524; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
            <span>Sổ Lưu Bút Kỷ Niệm</span>
        </a>
        <a href="{{ route('admin.wishes.index') }}" class="mayhen_admin_btn" style="background: #ffffff; color: #44403c; border: 1px solid #d6d3d1; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <span>Bảng Kiểm Duyệt Lời Chúc</span>
        </a>
        <a href="{{ route('admin.wishes.rsvps') }}" class="mayhen_admin_btn" style="background: #ffffff; color: #44403c; border: 1px solid #d6d3d1; padding: 8px 16px; border-radius: 6px; font-size: 13px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            <span>Phản Hồi RSVP Quan Khách</span>
        </a>
    </div>

    <!-- Bộ Chọn Tiệc Cưới Hoạt Động -->
    <div class="mayhen_admin_card" style="padding: 14px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <form method="GET" action="{{ route('admin.wishes.guestbook.index') }}" style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <label style="font-size: 13px; font-weight: 600; color: #44403c; white-space: nowrap;">Sổ lưu bút tiệc cưới:</label>
            <select name="wedding_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; max-width: 450px; width: 100%;">
                @forelse($allWeddings as $w)
                    <option value="{{ $w->id }}" {{ $activeWedding?->id == $w->id ? 'selected' : '' }}>
                        #{{ $w->id }} — {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->wishes_count }} lời chúc)
                    </option>
                @empty
                    <option value="">Chưa có dữ liệu tiệc cưới</option>
                @endforelse
            </select>
        </form>

        @if($activeWedding)
            <div style="font-size: 12px; color: #78716c;">
                Chủ tiệc: <strong style="color: #1c1917;">{{ $activeWedding->user?->name ?? 'Vãng lai' }}</strong> • Ngày cưới: <strong style="color: #1c1917;">{{ $activeWedding->wedding_date ? $activeWedding->wedding_date->format('d/m/Y') : '—' }}</strong>
            </div>
        @endif
    </div>

    @if($activeWedding && $guestbook)
        <!-- 5 KPI Thẻ Sổ Lưu Bút Kỷ Niệm -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px;">
            <!-- Tổng Lời Chúc -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Tổng Lời Chúc Lưu Bút</div>
                <div style="font-size: 26px; font-weight: 700; color: #1c1917; margin-top: 4px;">
                    {{ number_format($guestbook['stats']['total_wishes']) }} <span style="font-size: 14px; font-weight: 500; color: #78716c;">lời chúc</span>
                </div>
                <div style="font-size: 12px; color: #047857; margin-top: 2px;">
                    Đã duyệt: <strong>{{ $guestbook['stats']['approved_wishes'] }}</strong> • Chờ: <strong style="color: #b45309;">{{ $guestbook['stats']['pending_wishes'] }}</strong>
                </div>
            </div>

            <!-- Chữ Ký Số -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #0284c7; font-weight: 600; letter-spacing: 0.5px;">Chữ Ký Tay Điện Tử</div>
                <div style="font-size: 26px; font-weight: 700; color: #0284c7; margin-top: 4px;">
                    {{ number_format($guestbook['stats']['signature_wishes']) }} <span style="font-size: 14px; font-weight: 500; color: #0ea5e9;">chữ ký</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Ký trên Canvas cảm ứng
                </div>
            </div>

            <!-- Đã Ghim Nổi Bật -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #b45309; font-weight: 600; letter-spacing: 0.5px;">Lời Chúc Ghim Nổi Bật</div>
                <div style="font-size: 26px; font-weight: 700; color: #b45309; margin-top: 4px;">
                    {{ number_format($guestbook['stats']['pinned_wishes']) }} <span style="font-size: 14px; font-weight: 500; color: #d97706;">bản ghi</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Ưu tiên hiển thị trang đầu
                </div>
            </div>

            <!-- Lời Chúc Riêng Tư -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #7c3aed; font-weight: 600; letter-spacing: 0.5px;">Lời Chúc Riêng Tư</div>
                <div style="font-size: 26px; font-weight: 700; color: #7c3aed; margin-top: 4px;">
                    {{ number_format($guestbook['stats']['private_wishes']) }} <span style="font-size: 14px; font-weight: 500; color: #8b5cf6;">lời nhắn</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Gửi riêng cho cô dâu chú rể
                </div>
            </div>

            <!-- Tiến Độ Đáp Lễ -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #047857; font-weight: 600; letter-spacing: 0.5px;">Tỷ Lệ Đáp Lễ Tri Ân</div>
                <div style="font-size: 26px; font-weight: 700; color: #047857; margin-top: 4px;">
                    {{ $guestbook['stats']['reply_rate'] }}%
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Đã hồi đáp: <strong>{{ $guestbook['stats']['replied_wishes'] }}</strong> lời chúc
                </div>
            </div>
        </div>

        <!-- Bộ Lọc Danh Mục Tabs & Tìm Kiếm -->
        <div class="mayhen_admin_card" style="padding: 16px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; flex-direction: column; gap: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <!-- Tabs -->
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'all', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'all' ? 'background: #292524; color: #ffffff;' : 'background: #f5f5f4; color: #44403c;' }}">
                        Tất cả ({{ $guestbook['stats']['total_wishes'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'pinned', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'pinned' ? 'background: #d97706; color: #ffffff;' : 'background: #fffbeb; color: #b45309;' }}">
                        Đã ghim ({{ $guestbook['stats']['pinned_wishes'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'signature', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'signature' ? 'background: #0284c7; color: #ffffff;' : 'background: #f0f9ff; color: #0369a1;' }}">
                        Có chữ ký ({{ $guestbook['stats']['signature_wishes'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'approved', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'approved' ? 'background: #059669; color: #ffffff;' : 'background: #ecfdf5; color: #047857;' }}">
                        Đã duyệt ({{ $guestbook['stats']['approved_wishes'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'pending', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'pending' ? 'background: #dc2626; color: #ffffff;' : 'background: #fef2f2; color: #991b1b;' }}">
                        Chờ duyệt ({{ $guestbook['stats']['pending_wishes'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'private', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'private' ? 'background: #7c3aed; color: #ffffff;' : 'background: #f5f3ff; color: #6d28d9;' }}">
                        Riêng tư ({{ $guestbook['stats']['private_wishes'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['tab' => 'replied', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['tab'] === 'replied' ? 'background: #047857; color: #ffffff;' : 'background: #f0fdf4; color: #15803d;' }}">
                        Đã đáp lễ ({{ $guestbook['stats']['replied_wishes'] }})
                    </a>
                </div>

                <!-- Form Tìm Kiếm -->
                <form method="GET" action="{{ route('admin.wishes.guestbook.index') }}" style="display: flex; gap: 8px;">
                    <input type="hidden" name="wedding_id" value="{{ $activeWedding->id }}">
                    <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm người gửi, nội dung lời chúc..." style="padding: 7px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); width: 220px;">
                    <button type="submit" class="mayhen_admin_btn" style="padding: 7px 14px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">
                        Tìm
                    </button>
                    @if(!empty($filters['q']))
                        <a href="{{ route('admin.wishes.guestbook.index', ['wedding_id' => $activeWedding->id, 'tab' => $filters['tab']]) }}" style="padding: 7px 12px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none; display: inline-flex; align-items: center;">
                            Đặt lại
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Lưới Thẻ Kỷ Niệm Sổ Lưu Bút (Keepsake Cards Grid) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px;">
            @forelse($guestbook['wishes'] as $wish)
                <div class="mayhen_admin_card" style="padding: 20px; border-radius: 12px; background: #ffffff; border: 1px solid {{ $wish->is_pinned ? '#fcd34d' : '#e7e5e4' }}; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; position: relative; box-shadow: {{ $wish->is_pinned ? '0 4px 12px rgba(217, 119, 6, 0.08)' : 'none' }};">
                    <div>
                        <!-- Header thẻ: Tên người gửi, Badge -->
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 8px;">
                            <div>
                                <div style="font-weight: 700; color: #1c1917; font-size: 15px;">{{ $wish->sender_name }}</div>
                                <div style="font-size: 11px; color: #78716c; margin-top: 2px;">
                                    {{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '—' }}
                                    @if($wish->guest)
                                        • <span style="background: #f5f5f4; padding: 1px 6px; border-radius: 4px;">{{ $wish->guest->group_name ?: 'Khách chung' }}</span>
                                    @endif
                                </div>
                            </div>

                            <div style="display: flex; gap: 4px; flex-wrap: wrap; justify-content: flex-end;">
                                @if($wish->is_pinned)
                                    <span style="background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 700;">📌 Ghim</span>
                                @endif
                                @if($wish->is_private)
                                    <span style="background: #f5f3ff; color: #7c3aed; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">🔒 Riêng tư</span>
                                @endif
                                @if($wish->is_approved)
                                    <span style="background: #ecfdf5; color: #047857; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">Đã duyệt</span>
                                @else
                                    <span style="background: #fef2f2; color: #dc2626; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">Chờ duyệt</span>
                                @endif
                            </div>
                        </div>

                        <!-- Nội dung lời chúc -->
                        <div style="font-size: 13px; color: #44403c; line-height: 1.6; background: #fafaf9; border: 1px solid #f5f5f4; border-radius: 8px; padding: 12px 14px; font-style: italic;">
                            “{{ $wish->message }}”
                        </div>

                        <!-- Chữ Ký Điện Tử Canvas (Nếu Có) -->
                        @if(!empty($wish->signature_url))
                            <div style="margin-top: 10px; padding: 8px 12px; background: #ffffff; border: 1px dashed #d6d3d1; border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600;">Chữ Ký Tay Điện Tử:</div>
                                    <img src="{{ $wish->signature_url }}" alt="Chữ ký {{ $wish->sender_name }}" style="max-height: 48px; max-width: 140px; object-fit: contain; margin-top: 4px; filter: contrast(120%);" loading="lazy">
                                </div>
                                <button type="button" onclick="zoomSignature('{{ addslashes($wish->signature_url) }}', '{{ addslashes($wish->sender_name) }}')" style="background: #f5f5f4; border: 1px solid #d6d3d1; border-radius: 6px; padding: 4px 8px; font-size: 11px; color: #44403c; cursor: pointer;">
                                    Phóng to
                                </button>
                            </div>
                        @endif

                        <!-- Lời Đáp Lễ Của Cặp Đôi (Nếu Có) -->
                        @if(!empty($wish->reply))
                            <div style="margin-top: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                    <span style="font-size: 11px; font-weight: 700; color: #166534; text-transform: uppercase;">Lời Đáp Lễ Của Cặp Đôi:</span>
                                    <span style="font-size: 10px; color: #166534;">{{ $wish->replied_at ? $wish->replied_at->format('d/m/Y H:i') : '' }}</span>
                                </div>
                                <div style="font-size: 12px; color: #14532d; line-height: 1.5;">
                                    {{ $wish->reply }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions Footer -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f5f5f4; pt: 10px; margin-top: 6px; padding-top: 10px; gap: 6px; flex-wrap: wrap;">
                        <div style="display: flex; gap: 6px;">
                            <!-- Toggle Pin -->
                            <form method="POST" action="{{ route('admin.wishes.guestbook.toggle_pin', $wish->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" title="{{ $wish->is_pinned ? 'Bỏ ghim' : 'Ghim nổi bật' }}" style="padding: 4px 8px; font-size: 11px; border-radius: 4px; border: 1px solid #d6d3d1; background: {{ $wish->is_pinned ? '#fef3c7' : '#ffffff' }}; color: {{ $wish->is_pinned ? '#b45309' : '#44403c' }}; cursor: pointer;">
                                    {{ $wish->is_pinned ? '★ Đã ghim' : '☆ Ghim' }}
                                </button>
                            </form>

                            <!-- Toggle Approval -->
                            <form method="POST" action="{{ route('admin.wishes.guestbook.toggle_approval', $wish->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" title="{{ $wish->is_approved ? 'Tạm ẩn' : 'Duyệt' }}" style="padding: 4px 8px; font-size: 11px; border-radius: 4px; border: 1px solid #d6d3d1; background: #ffffff; color: {{ $wish->is_approved ? '#047857' : '#b45309' }}; cursor: pointer;">
                                    {{ $wish->is_approved ? 'Ẩn' : 'Duyệt' }}
                                </button>
                            </form>

                            <!-- Toggle Privacy -->
                            <form method="POST" action="{{ route('admin.wishes.guestbook.toggle_privacy', $wish->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" title="{{ $wish->is_private ? 'Chuyển công khai' : 'Chuyển riêng tư' }}" style="padding: 4px 8px; font-size: 11px; border-radius: 4px; border: 1px solid #d6d3d1; background: #ffffff; color: {{ $wish->is_private ? '#7c3aed' : '#78716c' }}; cursor: pointer;">
                                    {{ $wish->is_private ? 'Khóa' : 'Mở' }}
                                </button>
                            </form>
                        </div>

                        <div style="display: flex; gap: 6px;">
                            <!-- Nút Viết Lời Đáp Lễ -->
                            <button type="button" onclick="openReplyModal({{ $wish->id }}, '{{ addslashes($wish->sender_name) }}', '{{ addslashes($wish->reply ?? '') }}')" style="padding: 4px 8px; font-size: 11px; font-weight: 600; border-radius: 4px; background: #047857; color: #ffffff; border: none; cursor: pointer;">
                                {{ !empty($wish->reply) ? 'Sửa Đáp Lễ' : 'Đáp Lễ' }}
                            </button>

                            <!-- Nút Xóa Vĩnh Viễn -->
                            <form method="POST" action="{{ route('admin.wishes.guestbook.destroy', $wish->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn lời chúc này khỏi sổ lưu bút?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="padding: 4px 6px; font-size: 11px; border-radius: 4px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;" title="Xóa lời chúc">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="mayhen_admin_card" style="grid-column: 1 / -1; padding: 48px; text-align: center; border-radius: 12px; background: #ffffff; border: 1px solid #e7e5e4;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#a8a29e" stroke-width="1.5" style="margin-bottom: 10px;"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    <h3 style="font-size: 15px; font-weight: 700; color: #1c1917; margin: 0 0 4px 0;">Không Tìm Thấy Lời Chúc Phù Hợp</h3>
                    <p style="font-size: 13px; color: #78716c; margin: 0;">Hãy thử chọn danh mục hoặc từ khóa tìm kiếm khác.</p>
                </div>
            @endforelse
        </div>

        <!-- Phân Trang -->
        @if($guestbook['wishes']->hasPages())
            <div style="display: flex; justify-content: center; padding: 16px 0;">
                {{ $guestbook['wishes']->links() }}
            </div>
        @endif
    @else
        <!-- Empty State Khi Chưa Có Tiệc Cưới -->
        <div class="mayhen_admin_card" style="padding: 48px; text-align: center; border-radius: 12px; background: #ffffff; border: 1px solid #e7e5e4;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0 0 6px 0;">Chưa Chọn Tiệc Cưới Giám Sát</h3>
            <p style="font-size: 14px; color: #78716c; max-width: 420px; margin: 0 auto;">Vui lòng chọn một tiệc cưới ở thanh trên để mở Sổ Lưu Bút Kỷ Niệm và Album Chữ Ký Số.</p>
        </div>
    @endif
</div>

<!-- Modal 1: Phóng To Chữ Ký Số -->
<div id="modalSignatureZoom" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font); text-align: center;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0;" id="zoomSignatureTitle">Chữ Ký Điện Tử</h3>
            <button type="button" onclick="closeSignatureZoom()" style="background: none; border: none; font-size: 20px; color: #78716c; cursor: pointer;">&times;</button>
        </div>

        <div style="background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 8px; padding: 24px; display: flex; align-items: center; justify-content: center; min-height: 180px;">
            <img id="zoomSignatureImg" src="" alt="Chữ ký phóng to" style="max-height: 160px; max-width: 100%; object-fit: contain;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
            <button type="button" onclick="closeSignatureZoom()" style="padding: 8px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Đóng</button>
            <a id="zoomSignatureDownload" href="" download="" target="_blank" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                Tải Chữ Ký Gốc
            </a>
        </div>
    </div>
</div>

<!-- Modal 2: Viết Lời Đáp Lễ Tri Ân -->
<div id="modalReplyWish" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0;">Gửi Lời Đáp Lễ Tri Ân Của Cặp Đôi</h3>
            <button type="button" onclick="closeReplyModal()" style="background: none; border: none; font-size: 20px; color: #78716c; cursor: pointer;">&times;</button>
        </div>

        <form id="formReplyWish" method="POST" action="">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="font-size: 13px; color: #44403c;">
                    Hồi đáp lời chúc từ quan khách: <strong id="modalReplySenderName" style="color: #1c1917;"></strong>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Nội dung lời cảm ơn: <span style="color: #dc2626;">*</span></label>
                    <textarea name="reply" id="modalReplyTextarea" rows="4" required placeholder="Ví dụ: Vợ chồng mình cảm ơn bạn rất nhiều! Rất vui vì có sự hiện diện của bạn trong ngày trọng đại..." style="width: 100%; padding: 10px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box; line-height: 1.5;"></textarea>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px;">
                <button type="button" onclick="closeReplyModal()" style="padding: 8px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #047857; color: #ffffff; border: none; cursor: pointer;">Lưu &amp; Gửi Đáp Lễ</button>
            </div>
        </form>
    </div>
</div>

<script>
    function zoomSignature(url, sender) {
        document.getElementById('zoomSignatureTitle').textContent = `Chữ Ký: ${sender}`;
        document.getElementById('zoomSignatureImg').src = url;
        const dl = document.getElementById('zoomSignatureDownload');
        dl.href = url;
        dl.download = `chu-ky-${sender}.png`;
        document.getElementById('modalSignatureZoom').style.display = 'flex';
    }

    function closeSignatureZoom() {
        document.getElementById('modalSignatureZoom').style.display = 'none';
    }

    function openReplyModal(id, sender, currentReply) {
        document.getElementById('formReplyWish').action = `/admin/wishes/guestbook/${id}/reply`;
        document.getElementById('modalReplySenderName').textContent = sender;
        document.getElementById('modalReplyTextarea').value = currentReply || '';
        document.getElementById('modalReplyWish').style.display = 'flex';
    }

    function closeReplyModal() {
        document.getElementById('modalReplyWish').style.display = 'none';
    }
</script>
@endsection

