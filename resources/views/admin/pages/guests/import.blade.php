@extends('admin.layouts.master')

@section('title', 'Nhập Khách Mời Hàng Loạt Theo Batch')

@section('content')
<div class="mayhen_admin_guest_import_container" style="display: flex; flex-direction: column; gap: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.guests.index') }}" style="color: inherit; text-decoration: none;">Quản lý khách mời</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Nhập hàng loạt (Batch Pipeline)</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Quy Trình Nhập Khách Mời 4 Bước (Batch Import)</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Nhập danh sách hàng trăm khách từ file Excel / CSV, tự động ánh xạ cột, chuẩn hóa số điện thoại 10 số (chống mất số 0 đầu) và chống trùng lặp an toàn.</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Quay lại sổ khách mời
            </a>
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

    <!-- Stepper 4 Bước -->
    <div class="mayhen_admin_card" style="padding: 16px 24px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
        <div style="display: flex; justify-content: space-between; align-items: center; position: relative;">
            <div style="position: absolute; top: 18px; left: 40px; right: 40px; height: 2px; background: #e7e5e4; z-index: 1;"></div>
            
            <!-- Step 1 -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; z-index: 2;">
                <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; {{ $currentStep >= 1 ? 'background: #292524; color: #ffffff;' : 'background: #f5f5f4; color: #a8a29e; border: 1px solid #e7e5e4;' }}">
                    1
                </div>
                <div style="font-size: 12px; font-weight: {{ $currentStep === 1 ? '700' : '500' }}; color: {{ $currentStep === 1 ? '#1c1917' : '#78716c' }};">1. Tải Tệp Lên</div>
            </div>

            <!-- Step 2 -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; z-index: 2;">
                <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; {{ $currentStep >= 2 ? 'background: #292524; color: #ffffff;' : 'background: #f5f5f4; color: #a8a29e; border: 1px solid #e7e5e4;' }}">
                    2
                </div>
                <div style="font-size: 12px; font-weight: {{ $currentStep === 2 ? '700' : '500' }}; color: {{ $currentStep === 2 ? '#1c1917' : '#78716c' }};">2. Ánh Xạ Cột</div>
            </div>

            <!-- Step 3 -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; z-index: 2;">
                <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; {{ $currentStep >= 3 ? 'background: #292524; color: #ffffff;' : 'background: #f5f5f4; color: #a8a29e; border: 1px solid #e7e5e4;' }}">
                    3
                </div>
                <div style="font-size: 12px; font-weight: {{ $currentStep === 3 ? '700' : '500' }}; color: {{ $currentStep === 3 ? '#1c1917' : '#78716c' }};">3. Xem Trước &amp; Thẩm Tra</div>
            </div>

            <!-- Step 4 -->
            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; position: relative; z-index: 2;">
                <div style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; {{ $currentStep >= 4 ? 'background: #15803d; color: #ffffff;' : 'background: #f5f5f4; color: #a8a29e; border: 1px solid #e7e5e4;' }}">
                    4
                </div>
                <div style="font-size: 12px; font-weight: {{ $currentStep === 4 ? '700' : '500' }}; color: {{ $currentStep === 4 ? '#15803d' : '#78716c' }};">4. Kết Quả</div>
            </div>
        </div>
    </div>

    <!-- NỘI DUNG TỪNG BƯỚC -->
    @if($currentStep === 1)
        <!-- BƯỚC 1: TẢI TỆP LÊN -->
        <div class="mayhen_admin_card" style="padding: 28px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0 0 16px 0;">Bước 1: Chọn Đám Cưới &amp; Tải Tệp Danh Sách Khách Mời</h3>

            <form method="POST" action="{{ route('admin.guests.import.upload') }}" enctype="multipart/form-data">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 18px; max-width: 600px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 6px;">
                            Đám cưới đích cần nhập khách <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="wedding_id" required style="width: 100%; padding: 10px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                            @foreach($weddings as $w)
                                <option value="{{ $w->id }}" {{ (old('wedding_id') ?? $selectedWedding?->id) == $w->id ? 'selected' : '' }}>
                                    #{{ $w->id }} — {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->user?->name ?? 'Vãng lai' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 6px;">
                            Chọn tệp dữ liệu (CSV / TXT) <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="border: 2px dashed #d6d3d1; border-radius: 8px; padding: 24px; text-align: center; background: #fafaf9; cursor: pointer;" onclick="document.getElementById('fileInput').click();">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#78716c" stroke-width="1.5" style="margin-bottom: 8px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <div style="font-size: 14px; font-weight: 600; color: #1c1917;">Bấm để chọn tệp CSV hoặc kéo thả vào đây</div>
                            <div style="font-size: 12px; color: #78716c; margin-top: 4px;">Hỗ trợ mã hóa UTF-8, dung lượng tối đa 5MB</div>
                            <input type="file" id="fileInput" name="file" accept=".csv,.txt" required style="display: none;" onchange="document.getElementById('fileNameDisplay').innerText = this.files[0] ? this.files[0].name : '';">
                            <div id="fileNameDisplay" style="font-size: 13px; font-weight: 600; color: #047857; margin-top: 8px;"></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: center; margin-top: 8px;">
                        <button type="submit" class="mayhen_admin_btn" style="padding: 10px 22px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">
                            Tải Lên &amp; Phân Tích Cột
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if($recentBatches->isNotEmpty())
            <!-- Lịch sử các đợt import gần nhất -->
            <div class="mayhen_admin_card" style="border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; overflow: hidden;">
                <div style="padding: 14px 20px; border-bottom: 1px solid #e7e5e4; font-weight: 700; font-size: 14px; color: #1c1917;">
                    Các Đợt Nhập Khách Gần Nhất
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; font-family: var(--mh-admin-font);">
                        <thead>
                            <tr style="background: #fafaf9; border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 11px; text-transform: uppercase;">
                                <th style="padding: 10px 16px;">Tên Tệp</th>
                                <th style="padding: 10px 16px;">Đám Cưới</th>
                                <th style="padding: 10px 16px;">Trạng Thái</th>
                                <th style="padding: 10px 16px;">Tổng Dòng</th>
                                <th style="padding: 10px 16px;">Đã Nhập / Cập Nhật</th>
                                <th style="padding: 10px 16px;">Thời Gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBatches as $rb)
                                <tr style="border-bottom: 1px solid #f5f5f4;">
                                    <td style="padding: 12px 16px; font-weight: 600;">{{ $rb->file_name }}</td>
                                    <td style="padding: 12px 16px;">{{ $rb->wedding?->groom_name }} &amp; {{ $rb->wedding?->bride_name }}</td>
                                    <td style="padding: 12px 16px;">
                                        <span class="mayhen_admin_badge {{ $rb->status_badge_class }}">
                                            {{ $rb->status_label }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 16px;">{{ $rb->total_rows }}</td>
                                    <td style="padding: 12px 16px; color: #047857; font-weight: 600;">
                                        {{ $rb->imported_count }} / {{ $rb->updated_count }}
                                    </td>
                                    <td style="padding: 12px 16px; color: #78716c; font-size: 12px;">{{ $rb->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @elseif($currentStep === 2)
        <!-- BƯỚC 2: ÁNH XẠ CỘT NGUỒN SANG TRƯỜNG ĐÍCH -->
        <div class="mayhen_admin_card" style="padding: 24px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="margin-bottom: 18px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0;">Bước 2: Ánh Xạ Cột Tệp Nguồn Vào Cơ Sở Dữ Liệu</h3>
                <p style="font-size: 13px; color: #78716c; margin: 4px 0 0 0;">Hệ thống đã tự động phân tích và đề xuất ánh xạ tương ứng. Bạn có thể thay đổi trường đích theo ý muốn.</p>
            </div>

            <form method="POST" action="{{ route('admin.guests.import.preview', $batch->batch_id) }}">
                @csrf
                <div style="overflow-x: auto; margin-bottom: 24px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; font-family: var(--mh-admin-font);">
                        <thead>
                            <tr style="background: #fafaf9; border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 11px; text-transform: uppercase;">
                                <th style="padding: 12px 16px; width: 30%;">Cột Trong Tệp Excel / CSV</th>
                                <th style="padding: 12px 16px; width: 35%;">Ánh Xạ Vào Trường Hệ Thống</th>
                                <th style="padding: 12px 16px; width: 35%;">Dữ Liệu Mẫu (Dòng 1)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sampleHeaders as $header)
                                @php
                                    $suggested = $batch->column_mapping[$header] ?? 'ignore';
                                    $sampleVal = !empty($sampleRows) ? ($sampleRows[0][$header] ?? '—') : '—';
                                @endphp
                                <tr style="border-bottom: 1px solid #f5f5f4;">
                                    <td style="padding: 14px 16px; font-weight: 600; color: #1c1917;">
                                        {{ $header }}
                                    </td>
                                    <td style="padding: 14px 16px;">
                                        <select name="mapping[{{ $header }}]" style="width: 100%; max-width: 280px; padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                                            <option value="ignore" {{ $suggested === 'ignore' ? 'selected' : '' }}>-- Bỏ qua cột này --</option>
                                            <option value="name" {{ $suggested === 'name' ? 'selected' : '' }}>Họ và tên khách mời (* Bắt buộc)</option>
                                            <option value="phone" {{ $suggested === 'phone' ? 'selected' : '' }}>Số điện thoại (Tự giữ số 0 đầu)</option>
                                            <option value="salutation" {{ $suggested === 'salutation' ? 'selected' : '' }}>Danh xưng (Anh/Chị/Bạn/Bác)</option>
                                            <option value="group_name" {{ $suggested === 'group_name' ? 'selected' : '' }}>Nhóm khách (Bạn CR, Họ Hàng...)</option>
                                            <option value="expected_attendees" {{ $suggested === 'expected_attendees' ? 'selected' : '' }}>Số suất mời / Người đi cùng</option>
                                            <option value="table_name" {{ $suggested === 'table_name' ? 'selected' : '' }}>Bàn tiệc xếp sẵn</option>
                                        </select>
                                    </td>
                                    <td style="padding: 14px 16px; color: #78716c; font-family: monospace; font-size: 12px;">
                                        {{ $sampleVal }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Chế độ xử lý trùng lặp -->
                <div style="background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 8px; padding: 18px; margin-bottom: 24px;">
                    <div style="font-weight: 700; font-size: 13px; color: #1c1917; margin-bottom: 8px;">Chính sách xử lý khách trùng lặp (Tên + Số điện thoại)</div>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="duplicate_mode" value="skip" checked style="accent-color: #292524;">
                            <span><strong>Bỏ qua (Skip):</strong> Không nhập nếu khách đã có trong danh bạ đám cưới này (Khuyên dùng).</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="duplicate_mode" value="update" style="accent-color: #292524;">
                            <span><strong>Cập nhật (Update):</strong> Ghi đè nhóm, bàn tiệc, danh xưng mới cho khách đã có.</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="duplicate_mode" value="create_always" style="accent-color: #292524;">
                            <span><strong>Luôn tạo mới:</strong> Cho phép tạo nhiều bản ghi trùng lặp tên.</span>
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <a href="{{ route('admin.guests.import.index') }}" class="mayhen_admin_btn" style="padding: 10px 18px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none;">Quay lại</a>
                    <button type="submit" class="mayhen_admin_btn" style="padding: 10px 22px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">
                        Chuyển Sang Xem Trước &amp; Thẩm Tra
                    </button>
                </div>
            </form>
        </div>

    @elseif($currentStep === 3)
        <!-- BƯỚC 3: XEM TRƯỚC PHÂN TÍCH LỖI (DRY RUN) -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- KPI Thống Kê Thẩm Tra -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
                <div class="mayhen_admin_card" style="padding: 16px; border-radius: 8px; background: #ffffff; border: 1px solid #e7e5e4;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600;">Tổng số dòng</div>
                    <div style="font-size: 24px; font-weight: 700; color: #1c1917; margin-top: 4px;">{{ $dryRun['summary']['total'] }}</div>
                </div>
                <div class="mayhen_admin_card" style="padding: 16px; border-radius: 8px; background: #ffffff; border: 1px solid #e7e5e4;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #15803d; font-weight: 600;">Hợp lệ sẵn sàng nhập</div>
                    <div style="font-size: 24px; font-weight: 700; color: #15803d; margin-top: 4px;">{{ $dryRun['summary']['valid'] }}</div>
                </div>
                <div class="mayhen_admin_card" style="padding: 16px; border-radius: 8px; background: #ffffff; border: 1px solid #e7e5e4;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #b45309; font-weight: 600;">Trùng lặp (Sẽ xử lý theo chính sách)</div>
                    <div style="font-size: 24px; font-weight: 700; color: #b45309; margin-top: 4px;">{{ $dryRun['summary']['duplicate'] }}</div>
                </div>
                <div class="mayhen_admin_card" style="padding: 16px; border-radius: 8px; background: #ffffff; border: 1px solid #e7e5e4;">
                    <div style="font-size: 11px; text-transform: uppercase; color: #dc2626; font-weight: 600;">Dòng lỗi (Bị bỏ qua)</div>
                    <div style="font-size: 24px; font-weight: 700; color: #dc2626; margin-top: 4px;">{{ $dryRun['summary']['invalid'] }}</div>
                </div>
            </div>

            <!-- Bảng Xem Trước Chi Tiết Dòng -->
            <div class="mayhen_admin_card" style="border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; overflow: hidden;">
                <div style="padding: 14px 20px; border-bottom: 1px solid #e7e5e4; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: 700; font-size: 14px; color: #1c1917;">Xem Trước Kết Quả Thẩm Tra</div>
                    <div style="font-size: 12px; color: #78716c;">Số điện thoại đã được chuẩn hóa tự động có số 0 đầu</div>
                </div>
                <div style="overflow-x: auto; max-height: 400px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; font-family: var(--mh-admin-font);">
                        <thead style="position: sticky; top: 0; background: #fafaf9; z-index: 2;">
                            <tr style="border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 11px; text-transform: uppercase;">
                                <th style="padding: 10px 14px;">Dòng</th>
                                <th style="padding: 10px 14px;">Họ và Tên</th>
                                <th style="padding: 10px 14px;">Số Điện Thoại (Chuẩn hóa)</th>
                                <th style="padding: 10px 14px;">Danh Xưng</th>
                                <th style="padding: 10px 14px;">Nhóm</th>
                                <th style="padding: 10px 14px;">Suất Mời</th>
                                <th style="padding: 10px 14px;">Trạng Thái</th>
                                <th style="padding: 10px 14px;">Lỗi / Cảnh Báo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dryRun['preview_rows'] as $row)
                                <tr style="border-bottom: 1px solid #f5f5f4; {{ $row['status'] === 'invalid' ? 'background: #fffbfa;' : '' }}">
                                    <td style="padding: 10px 14px; color: #a8a29e; font-family: monospace;">#{{ $row['row_number'] }}</td>
                                    <td style="padding: 10px 14px; font-weight: 600; color: #1c1917;">{{ $row['data']['name'] ?: '— (Trống)' }}</td>
                                    <td style="padding: 10px 14px; font-family: monospace;">{{ $row['data']['phone'] ?: '—' }}</td>
                                    <td style="padding: 10px 14px;">{{ $row['data']['salutation'] }}</td>
                                    <td style="padding: 10px 14px;">{{ $row['data']['group_name'] }}</td>
                                    <td style="padding: 10px 14px; font-weight: 600;">{{ $row['data']['expected_attendees'] }}</td>
                                    <td style="padding: 10px 14px;">
                                        @if($row['status'] === 'valid')
                                            <span style="background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Hợp Lệ</span>
                                        @elseif($row['status'] === 'duplicate')
                                            <span style="background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Trùng Lặp</span>
                                        @else
                                            <span style="background: #fee2e2; color: #dc2626; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">Lỗi Dữ Liệu</span>
                                        @endif
                                    </td>
                                    <td style="padding: 10px 14px; font-size: 12px; color: #ef4444;">
                                        {{ implode('; ', $row['errors']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Nút Hành Động Thực Thi -->
            <form method="POST" action="{{ route('admin.guests.import.execute', $batch->batch_id) }}" style="display: flex; justify-content: space-between; align-items: center;">
                @csrf
                <a href="{{ route('admin.guests.import.mapping', $batch->batch_id) }}" class="mayhen_admin_btn" style="padding: 10px 18px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none;">Quay lại chỉnh ánh xạ</a>
                <button type="submit" class="mayhen_admin_btn" style="padding: 11px 24px; font-size: 13px; font-weight: 700; border-radius: 6px; background: #15803d; color: #ffffff; border: none; cursor: pointer;">
                    Xác Nhận &amp; Thực Thi Nhập Khách Mời
                </button>
            </form>
        </div>

    @elseif($currentStep === 4)
        <!-- BƯỚC 4: KẾT QUẢ HOÀN THÀNH -->
        <div class="mayhen_admin_card" style="padding: 32px; border-radius: 12px; background: #ffffff; border: 1px solid #e7e5e4; text-align: center; max-width: 650px; margin: 0 auto;">
            <div style="width: 54px; height: 54px; border-radius: 50%; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>

            <h2 style="font-size: 20px; font-weight: 700; color: #1c1917; margin: 0 0 8px 0;">Nhập Danh Sách Khách Mời Hoàn Tất!</h2>
            <p style="font-size: 14px; color: #78716c; margin: 0 0 24px 0;">Đám cưới: <strong>{{ $selectedWedding?->groom_name }} &amp; {{ $selectedWedding?->bride_name }}</strong></p>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; background: #fafaf9; border-radius: 8px; padding: 16px; margin-bottom: 24px; border: 1px solid #e7e5e4;">
                <div>
                    <div style="font-size: 11px; color: #78716c; text-transform: uppercase;">Đã Nhập Mới</div>
                    <div style="font-size: 22px; font-weight: 700; color: #15803d; margin-top: 2px;">{{ $result['imported'] }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #78716c; text-transform: uppercase;">Cập Nhật</div>
                    <div style="font-size: 22px; font-weight: 700; color: #1d4ed8; margin-top: 2px;">{{ $result['updated'] }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #78716c; text-transform: uppercase;">Bỏ Qua (Trùng)</div>
                    <div style="font-size: 22px; font-weight: 700; color: #b45309; margin-top: 2px;">{{ $result['skipped'] }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #78716c; text-transform: uppercase;">Lỗi Bị Loại</div>
                    <div style="font-size: 22px; font-weight: 700; color: #dc2626; margin-top: 2px;">{{ $result['failed'] }}</div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                @if($result['failed'] > 0)
                    <a href="{{ route('admin.guests.import.export_failed', $batch->batch_id) }}" class="mayhen_admin_btn" style="padding: 10px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Tải Tệp CSV Danh Sách Lỗi ({{ $result['failed'] }})
                    </a>
                @endif
                <a href="{{ route('admin.guests.index', ['wedding_id' => $selectedWedding?->id]) }}" class="mayhen_admin_btn" style="padding: 10px 22px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; text-decoration: none;">
                    Xem Danh Bạ Khách Mời
                </a>
                <a href="{{ route('admin.guests.import.index') }}" class="mayhen_admin_btn" style="padding: 10px 18px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none;">
                    Nhập Tệp Khác
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
