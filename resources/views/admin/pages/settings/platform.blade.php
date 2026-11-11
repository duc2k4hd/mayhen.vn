@extends('admin.layouts.master')

@section('title', 'Thiết Lập Nền Tảng & Chẩn Đoán Kết Nối')
@section('page_title', 'Thiết Lập Nền Tảng & Chẩn Đoán Kết Nối (Safe Platform Cockpit)')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
    <style>
        .mayhen_admin_platform_layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 992px) {
            .mayhen_admin_platform_layout {
                grid-template-columns: 1fr;
            }
        }
        .mayhen_admin_settings_nav {
            background: #FFFFFF;
            border-radius: 12px;
            border: 1px solid #EBE7DF;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }
        .mayhen_admin_settings_nav_item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            border-radius: 8px;
            color: var(--mh-admin-charcoal, #2B2927);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }
        .mayhen_admin_settings_nav_item:hover {
            background: #F7F5F0;
            color: var(--mh-admin-primary, #A38048);
        }
        .mayhen_admin_settings_nav_item.active {
            background: #F2EFEB;
            color: var(--mh-admin-primary, #8C6B37);
            font-weight: 600;
            border-left: 3px solid var(--mh-admin-primary, #A38048);
        }
        .mayhen_admin_settings_nav_item_icon {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .mayhen_admin_field_row {
            padding: 20px 0;
            border-bottom: 1px solid #F0ECE4;
        }
        .mayhen_admin_field_row:last-child {
            border-bottom: none;
        }
        .mayhen_admin_field_grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 20px;
        }
        @media (max-width: 768px) {
            .mayhen_admin_field_grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
        .mayhen_admin_secret_badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #FFF3E0;
            color: #E65100;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
        }
        .mayhen_admin_switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }
        .mayhen_admin_switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .mayhen_admin_slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #D6D0C4;
            transition: .3s;
            border-radius: 24px;
        }
        .mayhen_admin_slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        input:checked + .mayhen_admin_slider {
            background-color: #2E7D32;
        }
        input:checked + .mayhen_admin_slider:before {
            transform: translateX(20px);
        }
        .mayhen_diag_box {
            display: none;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .mayhen_diag_success {
            background: #E8F5E9;
            border: 1px solid #A5D6A7;
            color: #1B5E20;
        }
        .mayhen_diag_failed {
            background: #FFEBEE;
            border: 1px solid #FFCDD2;
            color: #B71C1C;
        }
    </style>
@endpush

@section('content')
    <!-- Giới thiệu Cockpit An Toàn -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px; border-left: 4px solid var(--mh-admin-primary, #A38048);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal, #2B2927);">
                    Trung Tâm Cấu Hình Nền Tảng An Toàn & Chẩn Đoán Kết Nối (Safe Cockpit)
                </h3>
                <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.84375rem; margin: 0;">
                    Bảo vệ khóa bí mật (Masked Secret Protection), chẩn đoán độ trễ kết nối thời gian thực và tự động lưu vết lịch sử phục vụ hoàn tác 1-click (Safe Rollback).
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <span class="mayhen_admin_badge" style="background: #E8F5E9; color: #2E7D32; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    🛡️ Zero Secret Leak
                </span>
                <span class="mayhen_admin_badge" style="background: #FFF8E1; color: #F57F17; padding: 6px 12px; font-size: 0.75rem; font-weight: 600;">
                    ↺ 1-Click Rollback
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Thông báo Session -->
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #E8F5E9; color: #1B5E20; border: 1px solid #A5D6A7;">
            <strong>✓ Thành công:</strong> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert_danger" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #FFEBEE; color: #B71C1C; border: 1px solid #FFCDD2;">
            <strong>✕ Lỗi:</strong> {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mayhen_admin_alert mayhen_admin_alert_info" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #E1F5FE; color: #01579B; border: 1px solid #B3E5FC;">
            <strong>ℹ Thông báo:</strong> {{ session('info') }}
        </div>
    @endif

    <div class="mayhen_admin_platform_layout">
        <!-- Cột Trái: Danh Mục Nhóm Thiết Lập -->
        <aside class="mayhen_admin_settings_nav">
            <div style="padding: 8px 12px 12px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted, #7A756D);">
                Danh Mục Cấu Hình
            </div>
            @foreach($definitions as $grpKey => $def)
                @php
                    $grpIcon = match($def['icon'] ?? '') {
                        'brand' => '🎨',
                        'payment' => '💳',
                        'mail' => '✉️',
                        'storage' => '💾',
                        'security' => '🛡️',
                        'features' => '⚡',
                        default => '⚙️',
                    };
                @endphp
                <a href="{{ route('admin.settings.platform', ['group' => $grpKey]) }}" 
                   class="mayhen_admin_settings_nav_item {{ $activeGroup === $grpKey ? 'active' : '' }}">
                    <span class="mayhen_admin_settings_nav_item_icon">
                        <span style="font-size: 1.1rem;">{{ $grpIcon }}</span>
                        <span>{{ $def['title'] }}</span>
                    </span>
                    <span class="mayhen_admin_badge" style="background: #EBE7DF; color: #5A554E; font-size: 0.7rem; padding: 2px 7px; border-radius: 10px;">
                        {{ count($def['settings'] ?? []) }}
                    </span>
                </a>
            @endforeach

            <div style="margin-top: 20px; padding: 16px; background: #FAF9F6; border-radius: 8px; border: 1px dashed #D6D0C4;">
                <div style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-charcoal, #2B2927); margin-bottom: 4px;">
                    Quy Tắc Bảo Vệ Dữ Liệu
                </div>
                <div style="font-size: 0.75rem; color: var(--mh-admin-muted, #7A756D); line-height: 1.5;">
                    Khóa bảo mật chỉ hiển thị <code>••••••••</code> và được mã hóa lưu trữ. Để giữ nguyên giá trị cũ, bạn chỉ cần để trống hoặc không thay đổi chuỗi chấm tròn.
                </div>
            </div>
        </aside>

        <!-- Cột Phải: Bảng Điều Khiển Cài Đặt Cụ Thể -->
        <main>
            <!-- Hộp Chẩn Đoán Kết Nối (AJAX Result) -->
            <div id="diagnosticAlert" class="mayhen_diag_box">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div id="diagnosticMessage">Đang kiểm tra kết nối...</div>
                    <div id="diagnosticLatency" style="font-weight: 600; font-family: monospace;"></div>
                </div>
            </div>

            <!-- Panel Cấu Hình Nhóm Hiện Tại -->
            <div class="mayhen_admin_card" style="padding: 28px; margin-bottom: 24px;">
                <!-- Header Nhóm -->
                @php
                    $activeIcon = match($groupMeta['icon'] ?? '') {
                        'brand' => '🎨',
                        'payment' => '💳',
                        'mail' => '✉️',
                        'storage' => '💾',
                        'security' => '🛡️',
                        'features' => '⚡',
                        default => '⚙️',
                    };
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid #F0ECE4;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                            <span style="font-size: 1.5rem;">{{ $activeIcon }}</span>
                            <h2 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                                {{ $groupMeta['title'] }}
                            </h2>
                        </div>
                        <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.875rem; margin: 0;">
                            {{ $groupMeta['description'] ?? ($groupMeta['desc'] ?? '') }}
                        </p>
                    </div>

                    @if(!empty($groupMeta['test_service']))
                        <button type="button" 
                                id="btnTestConnection" 
                                onclick="runConnectionTest('{{ $groupMeta['test_service'] }}')" 
                                class="mayhen_admin_btn" 
                                style="background: #FAF8F5; border: 1px solid #D6D0C4; color: var(--mh-admin-charcoal, #2B2927); font-size: 0.8125rem; padding: 8px 16px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Kiểm Tra Kết Nối
                        </button>
                    @endif
                </div>

                <!-- Form Cập Nhật Cấu Hình -->
                <form action="{{ route('admin.settings.platform.update', ['group' => $activeGroup]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div style="margin-top: 8px;">
                        @foreach($settings as $field)
                            <div class="mayhen_admin_field_row">
                                <div class="mayhen_admin_field_grid">
                                    <div>
                                        <label class="mayhen_admin_label" for="field_{{ $field['key'] }}" style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927); margin-bottom: 4px; display: inline-block;">
                                            {{ $field['label'] }}
                                        </label>
                                        @if($field['is_secret'])
                                            <span class="mayhen_admin_secret_badge">🔒 Masked Secret</span>
                                        @endif
                                        @if(!empty($field['help']))
                                            <div style="color: var(--mh-admin-muted, #7A756D); font-size: 0.78125rem; line-height: 1.4; margin-top: 4px;">
                                                {{ $field['help'] }}
                                            </div>
                                        @endif
                                    </div>

                                    <div>
                                        @if($field['type'] === 'toggle')
                                            <label class="mayhen_admin_switch" style="margin-top: 4px;">
                                                <input type="checkbox" 
                                                       name="{{ $field['key'] }}" 
                                                       value="1" 
                                                       id="field_{{ $field['key'] }}"
                                                       {{ $field['value'] == '1' || $field['value'] === true ? 'checked' : '' }}>
                                                <span class="mayhen_admin_slider"></span>
                                            </label>
                                        @elseif($field['type'] === 'select')
                                            <select name="{{ $field['key'] }}" id="field_{{ $field['key'] }}" class="mayhen_admin_select" style="width: 100%; max-width: 450px;">
                                                @foreach(($field['options'] ?? []) as $optVal => $optLabel)
                                                    <option value="{{ $optVal }}" {{ (string)$field['value'] === (string)$optVal ? 'selected' : '' }}>
                                                        {{ $optLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($field['type'] === 'textarea')
                                            <textarea name="{{ $field['key'] }}" 
                                                      id="field_{{ $field['key'] }}" 
                                                      rows="3" 
                                                      class="mayhen_admin_input" 
                                                      style="width: 100%; resize: vertical; font-family: monospace; font-size: 0.8125rem;"
                                                      placeholder="{{ $field['is_secret'] ? '•••••••• (Để trống để giữ nguyên khóa bí mật)' : '' }}">{{ $field['is_secret'] ? '' : $field['value'] }}</textarea>
                                        @elseif($field['type'] === 'password')
                                            <div style="position: relative; max-width: 450px;">
                                                <input type="password" 
                                                       name="{{ $field['key'] }}" 
                                                       id="field_{{ $field['key'] }}" 
                                                       class="mayhen_admin_input" 
                                                       style="width: 100%; padding-right: 40px;" 
                                                       value=""
                                                       placeholder="{{ !empty($field['value']) ? '•••••••• (Giữ nguyên khóa hiện tại)' : 'Nhập mật khẩu / khóa mới' }}">
                                                <button type="button" 
                                                        onclick="togglePasswordVisibility('field_{{ $field['key'] }}')" 
                                                        style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #8A857C;">
                                                    👁️
                                                </button>
                                            </div>
                                        @else
                                            <input type="text" 
                                                   name="{{ $field['key'] }}" 
                                                   id="field_{{ $field['key'] }}" 
                                                   class="mayhen_admin_input" 
                                                   style="width: 100%; max-width: 450px;" 
                                                   value="{{ $field['value'] }}"
                                                   placeholder="Nhập giá trị...">
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #F0ECE4; display: flex; justify-content: flex-end; gap: 12px;">
                        <a href="{{ route('admin.settings.platform', ['group' => $activeGroup]) }}" class="mayhen_admin_btn" style="background: #F4F1EA; color: #5A554E; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 0.875rem;">
                            Hủy Thay Đổi
                        </a>
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="background: var(--mh-admin-primary, #A38048); color: #FFFFFF; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 500; font-size: 0.875rem; cursor: pointer;">
                            Lưu Thiết Lập Nhóm
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bảng Lịch Sử Thay Đổi & Hoàn Tác (Rollback History) -->
            <div class="mayhen_admin_card" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h4 style="font-family: 'Playfair Display', serif; font-size: 1.1rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                            Lịch Sử Cập Nhật & Hoàn Tác Cấu Hình (Safe Rollback)
                        </h4>
                        <p style="color: var(--mh-admin-muted, #7A756D); font-size: 0.8125rem; margin: 2px 0 0;">
                            Ghi lại 10 thay đổi gần nhất của nhóm [{{ $groupMeta['title'] }}]. Nhấn Hoàn Tác để khôi phục giá trị cũ tức thì.
                        </p>
                    </div>
                </div>

                @if($histories->isEmpty())
                    <div style="padding: 32px; text-align: center; color: var(--mh-admin-muted, #7A756D); font-size: 0.875rem;">
                        Chưa có lịch sử thay đổi nào cho nhóm cấu hình này.
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table class="mayhen_admin_table" style="width: 100%; font-size: 0.8125rem;">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Thời Gian</th>
                                    <th style="width: 140px;">Người Thực Hiện</th>
                                    <th style="width: 160px;">Khóa Thiết Lập</th>
                                    <th>Giá Trị Trước Đó</th>
                                    <th>Giá Trị Cập Nhật</th>
                                    <th style="width: 100px; text-align: right;">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histories as $hist)
                                    <tr>
                                        <td style="color: var(--mh-admin-muted, #7A756D); white-space: nowrap;">
                                            {{ $hist->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td style="font-weight: 500; color: var(--mh-admin-charcoal, #2B2927);">
                                            {{ $hist->changer->name ?? 'Hệ Thống' }}
                                        </td>
                                        <td>
                                            <code>{{ $hist->key }}</code>
                                        </td>
                                        <td style="color: #C62828; font-family: monospace; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $hist->masked_old_value }}
                                        </td>
                                        <td style="color: #2E7D32; font-family: monospace; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $hist->masked_new_value }}
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <form action="{{ route('admin.settings.platform.rollback', ['historyId' => $hist->id]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục khóa [{{ $hist->key }}] về giá trị trước đó?');">
                                                @csrf
                                                <button type="submit" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; font-size: 0.75rem; padding: 4px 10px; border-radius: 4px; cursor: pointer;">
                                                    ↺ Hoàn Tác
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
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            if (input) {
                input.type = input.type === 'password' ? 'text' : 'password';
            }
        }

        function runConnectionTest(service) {
            const btn = document.getElementById('btnTestConnection');
            const alertBox = document.getElementById('diagnosticAlert');
            const messageEl = document.getElementById('diagnosticMessage');
            const latencyEl = document.getElementById('diagnosticLatency');

            if (!btn || !alertBox) return;

            btn.disabled = true;
            btn.innerHTML = '⏳ Đang kiểm tra...';
            alertBox.style.display = 'block';
            alertBox.className = 'mayhen_diag_box';
            alertBox.style.background = '#FFF8E1';
            alertBox.style.borderColor = '#FFE082';
            alertBox.style.color = '#F57F17';
            messageEl.textContent = 'Đang gửi gói tin chẩn đoán kết nối tới hạ tầng [' + service + ']...';
            latencyEl.textContent = '';

            fetch('{{ url("admin/settings/platform/test") }}/' + service, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Kiểm Tra Kết Nối';

                if (data.success) {
                    alertBox.className = 'mayhen_diag_box mayhen_diag_success';
                    messageEl.textContent = '✓ ' + (data.message || 'Kết nối hạ tầng hoạt động bình thường.');
                    latencyEl.textContent = (data.latency_ms || 0) + ' ms';
                } else {
                    alertBox.className = 'mayhen_diag_box mayhen_diag_failed';
                    messageEl.textContent = '✕ ' + (data.message || 'Kiểm tra kết nối thất bại.');
                    latencyEl.textContent = (data.latency_ms || 0) + ' ms';
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Kiểm Tra Kết Nối';
                alertBox.className = 'mayhen_diag_box mayhen_diag_failed';
                messageEl.textContent = '✕ Lỗi kết nối máy chủ quản trị: ' + err.message;
                latencyEl.textContent = 'Error';
            });
        }
    </script>
@endpush
