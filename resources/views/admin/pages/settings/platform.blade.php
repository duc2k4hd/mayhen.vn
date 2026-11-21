@extends('admin.layouts.master')

@section('title', 'Thiết Lập Nền Tảng & Chẩn Đoán Kết Nối')
@section('page_title', 'Thiết Lập Nền Tảng & Chẩn Đoán Kết Nối (Safe Platform Cockpit)')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/admin/css/pages/settings.css') }}">
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
                <h3 style="font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal, #2B2927);">
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
                    <div id="diagnosticLatency" style="font-weight: 600;"></div>
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
                            <h2 style="font-size: 1.35rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
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
                <form action="{{ route('admin.settings.platform.update', ['group' => $activeGroup]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @if($activeGroup === \App\Models\PlatformSetting::GROUP_MAIL)
                        <!-- Thông Tin 3 Hòm Thư Chuẩn Hóa Hệ Thống Mây Hẹn -->
                        <div style="margin-top: 18px; margin-bottom: 24px; padding: 18px 20px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
                            <div style="font-weight: 700; color: #1E293B; font-size: 0.9375rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                                <span>📬</span> 3 Tài Khoản Email Chuẩn Hóa Phân Quyền Toàn Nền Tảng {{ \App\Models\PlatformSetting::getBrandName() }}
                            </div>
                            <div style="font-size: 0.8125rem; color: #64748B; margin-bottom: 14px; line-height: 1.5;">
                                Hệ thống phân định rõ ràng 3 hòm thư đại diện nhằm tối ưu tỷ lệ vào Hộp thư đến (Inbox), phân tách rủi ro bảo mật và ngăn ngừa nghẽn luồng gửi thư giao dịch.
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                                <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; padding: 14px 16px; border-left: 3px solid #3B82F6;">
                                    <div style="font-size: 0.71875rem; font-weight: 700; color: #3B82F6; text-transform: uppercase; letter-spacing: 0.5px;">1. Mail Hệ Thống (Nội Bộ)</div>
                                    <div style="font-weight: 700; color: #0F172A; font-size: 0.9375rem; margin: 4px 0 6px 0;">admin@mayhen.vn</div>
                                    <div style="font-size: 0.75rem; color: #64748B; line-height: 1.45;">
                                        Gửi/nhận cảnh báo hạ tầng, sao lưu dữ liệu, sự cố máy chủ và trao đổi nội bộ ban quản trị {{ \App\Models\PlatformSetting::getBrandName() }}.
                                    </div>
                                </div>
                                <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; padding: 14px 16px; border-left: 3px solid #10B981;">
                                    <div style="font-size: 0.71875rem; font-weight: 700; color: #10B981; text-transform: uppercase; letter-spacing: 0.5px;">2. Mail CSKH & Hỗ Trợ</div>
                                    <div style="font-weight: 700; color: #0F172A; font-size: 0.9375rem; margin: 4px 0 6px 0;">support@mayhen.vn</div>
                                    <div style="font-size: 0.75rem; color: #64748B; line-height: 1.45;">
                                        Tiếp nhận thắc mắc của cô dâu chú rể, tư vấn gói thiệp cưới, giải quyết khiếu nại và hiển thị tại chân trang.
                                    </div>
                                </div>
                                <div style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; padding: 14px 16px; border-left: 3px solid #8B5CF6;">
                                    <div style="font-size: 0.71875rem; font-weight: 700; color: #8B5CF6; text-transform: uppercase; letter-spacing: 0.5px;">3. Mail Không Nhận Lại (No-Reply)</div>
                                    <div style="font-weight: 700; color: #0F172A; font-size: 0.9375rem; margin: 4px 0 6px 0;">no-reply@mayhen.vn</div>
                                    <div style="font-size: 0.75rem; color: #64748B; line-height: 1.45;">
                                        Gửi thư tự động qua hàng đợi (quên mật khẩu, OTP, xác nhận tài khoản); tuyệt đối không tiếp nhận thư phản hồi gửi về.
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

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
                                                      style="width: 100%; resize: vertical; font-size: 0.8125rem;"
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
                                        @elseif($field['type'] === 'image')
                                            <div style="display: flex; flex-direction: column; gap: 10px; max-width: 550px;">
                                                <!-- Image Preview Card -->
                                                <div style="display: flex; gap: 14px; align-items: center; padding: 12px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
                                                    <img src="{{ !empty($field['value']) ? asset($field['value']) : asset('/storage/images/client/banners/banner-home.webp') }}" 
                                                         alt="{{ $field['label'] }}" 
                                                         id="preview_{{ $field['key'] }}" 
                                                         style="width: 140px; height: 78px; object-fit: cover; border-radius: 6px; border: 1px solid #CBD5E1; background: #FFFFFF;">
                                                    <div style="flex: 1; min-width: 0;">
                                                        <div style="font-size: 0.8125rem; font-weight: 600; color: #0F172A; margin-bottom: 2px;">
                                                            Xem trước ảnh nền hiện tại
                                                        </div>
                                                        <div style="font-size: 0.75rem; color: #64748B;">
                                                            Tỉ lệ khuyến nghị 16:9 (1920x1080), định dạng WebP, JPG, PNG.
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Input URL & Upload button -->
                                                <div style="display: flex; gap: 8px; align-items: center;">
                                                    <input type="text" 
                                                           name="{{ $field['key'] }}" 
                                                           id="field_{{ $field['key'] }}" 
                                                           class="mayhen_admin_input" 
                                                           value="{{ $field['value'] }}" 
                                                           style="flex: 1; font-family: monospace; font-size: 0.8125rem;" 
                                                           placeholder="/storage/images/client/banners/..."
                                                           oninput="document.getElementById('preview_{{ $field['key'] }}').src = this.value;">
                                                    <label style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 6px; font-size: 0.8125rem; font-weight: 600; color: #334155; cursor: pointer; white-space: nowrap;">
                                                        <input type="file" 
                                                               name="{{ $field['key'] }}_file" 
                                                               accept="image/webp,image/jpeg,image/png,image/jpg" 
                                                               style="display: none;" 
                                                               onchange="if(this.files && this.files[0]){ const r = new FileReader(); r.onload = e => document.getElementById('preview_{{ $field['key'] }}').src = e.target.result; r.readAsDataURL(this.files[0]); document.getElementById('field_{{ $field['key'] }}').value = '[Tệp tải lên mới: ' + this.files[0].name + ']'; }">
                                                        <span>📁 Tải Ảnh Mới</span>
                                                    </label>
                                                </div>
                                                <div style="font-size: 0.75rem; color: #166534; display: flex; align-items: center; gap: 4px;">
                                                    <span>✓</span> <span>Tự động dọn dẹp ảnh cũ trong Storage khi thay thế</span>
                                                </div>
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

            @if($activeGroup === \App\Models\PlatformSetting::GROUP_MAIL)
                <!-- Panel Kiểm Thử Gửi Thư Mẫu Thực Tế (Live SMTP Test) -->
                <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 24px; border-left: 4px solid #3B82F6;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 18px;">
                        <div>
                            <h4 style="font-size: 1.15rem; margin: 0 0 4px 0; color: #1E293B; display: flex; align-items: center; gap: 8px;">
                                <span>✉️</span> Kiểm Tra Gửi Email Mẫu Thực Tế (Live SMTP Dispatcher Test)
                            </h4>
                            <p style="color: #64748B; font-size: 0.84375rem; margin: 0; line-height: 1.5;">
                                Hệ thống sẽ nạp trực tiếp cấu hình máy chủ SMTP hiện tại và gửi thư kiểm thử chuẩn Quiet Luxury tới hòm thư chỉ định để xác nhận luồng thư tín hoạt động bình thường.
                            </p>
                        </div>
                        <span class="mayhen_admin_badge" style="background: #EFF6FF; color: #1D4ED8; font-weight: 600; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem;">
                            ⚡ Live Socket &amp; TLS
                        </span>
                    </div>

                    <!-- Box Alert Kết Quả SMTP Test -->
                    <div id="smtpTestAlert" style="display: none; padding: 14px 18px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                            <div id="smtpTestMessage">Đang gửi thư kiểm thử...</div>
                            <div id="smtpTestLatency" style="font-weight: 600; white-space: nowrap;"></div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 18px; border-radius: 10px;">
                        <div style="flex: 1; min-width: 260px;">
                            <label for="smtp_test_email" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                Địa Chỉ Email Người Nhận Kiểm Thử:
                            </label>
                            <input type="email" 
                                   id="smtp_test_email" 
                                   class="mayhen_admin_input" 
                                   value="{{ auth()->user()->email ?? 'admin@mayhen.vn' }}" 
                                   placeholder="nhap.email.nhan@domain.com"
                                   style="width: 100%; font-size: 0.875rem; padding: 9px 14px; background: #FFFFFF;">
                        </div>

                        <button type="button" 
                                id="btnSendSmtpTest" 
                                onclick="triggerSmtpTest()" 
                                class="mayhen_admin_btn" 
                                style="background: #2563EB; color: #FFFFFF; border: none; padding: 10px 22px; border-radius: 6px; font-weight: 600; font-size: 0.875rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            <span>Gửi Email Thử Nghiệm Ngay</span>
                        </button>
                    </div>

                    <div style="margin-top: 12px; font-size: 0.78125rem; color: #64748B; display: flex; align-items: center; gap: 6px;">
                        <span>💡</span>
                        <span>Sau khi bấm gửi, hãy mở hộp thư (bao gồm cả thư mục Quảng cáo / Spam) để kiểm tra thời gian nhận và độ sắc nét của mẫu thư.</span>
                    </div>
                </div>
            @endif

            @if($activeGroup === \App\Models\PlatformSetting::GROUP_PAYMENT)
                <!-- Panel Kiểm Thử Cổng Thanh Toán & VietQR Mẫu / Email Xác Nhận -->
                <div class="mayhen_admin_card" style="padding: 24px; margin-bottom: 24px; border-left: 4px solid #10B981;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 20px;">
                        <div>
                            <h4 style="font-size: 1.15rem; margin: 0 0 4px 0; color: #064E3B; display: flex; align-items: center; gap: 8px;">
                                <span>💳</span> Kiểm Thử Cổng Thanh Toán &amp; Mẫu Thư Xác Nhận VietQR
                            </h4>
                            <p style="color: #64748B; font-size: 0.84375rem; margin: 0; line-height: 1.5;">
                                Trình diễn mã VietQR chuẩn Napas 24/7 theo số tài khoản thụ hưởng thực tế và gửi thử email hóa đơn kích hoạt gói thiệp cưới cho Quản trị viên.
                            </p>
                        </div>
                        <span class="mayhen_admin_badge" style="background: #ECFDF5; color: #047857; font-weight: 600; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem;">
                            ✦ VietQR Napas 24/7 &amp; SePay Ready
                        </span>
                    </div>

                    <!-- Box Alert Kết Quả Payment Test -->
                    <div id="paymentTestAlert" style="display: none; padding: 14px 18px; border-radius: 8px; font-size: 0.875rem; margin-bottom: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                            <div id="paymentTestMessage">Đang xử lý kiểm thử cổng thanh toán...</div>
                            <div id="paymentTestLatency" style="font-weight: 600; white-space: nowrap;"></div>
                        </div>
                    </div>

                    <!-- Grid 2 Cột: Cột 1 là VietQR Preview, Cột 2 là Test Email Xác Nhận -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
                        <!-- Cột 1: Sinh mã VietQR Mẫu -->
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px; display: flex; flex-direction: column;">
                            <div style="font-weight: 700; color: #1E293B; font-size: 0.9375rem; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                <span>📱</span> 1. Sinh Mã VietQR Mẫu &amp; Quét Thử
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                                <div>
                                    <label for="vietqr_test_amount" style="display: block; font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 4px;">Số tiền thử nghiệm:</label>
                                    <input type="number" id="vietqr_test_amount" class="mayhen_admin_input" value="50000" step="1000" style="width: 100%; font-size: 0.8125rem; padding: 7px 10px; background: #FFFFFF;">
                                </div>
                                <div>
                                    <label for="vietqr_test_memo" style="display: block; font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 4px;">Cú pháp chuyển khoản:</label>
                                    <input type="text" id="vietqr_test_memo" class="mayhen_admin_input" value="MH TEST 9988" style="width: 100%; font-size: 0.8125rem; padding: 7px 10px; background: #FFFFFF;">
                                </div>
                            </div>

                            <button type="button" 
                                    id="btnGenerateQr" 
                                    onclick="triggerGenerateSampleQr()" 
                                    class="mayhen_admin_btn" 
                                    style="background: #059669; color: #FFFFFF; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 0.8125rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 14px;">
                                <span>🔄 Tạo / Cập Nhật Mã VietQR Mẫu</span>
                            </button>

                            <!-- Box hiển thị QR Code -->
                            <div id="sampleQrContainer" style="background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 8px; padding: 14px; text-align: center; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <img id="sampleQrImage" src="" alt="Mã VietQR Mẫu" style="width: 200px; height: 200px; border-radius: 6px; border: 1px solid #E2E8F0; padding: 6px; margin-bottom: 10px; display: none;">
                                <div id="sampleQrLoading" style="color: #64748B; font-size: 0.8125rem; padding: 30px 0;">
                                    Đang nạp mã VietQR theo tài khoản thụ hưởng...
                                </div>
                                <div id="sampleQrInfo" style="display: none; font-size: 0.78125rem; color: #334155; line-height: 1.5; text-align: left; width: 100%; background: #F1F5F9; padding: 10px 12px; border-radius: 6px;">
                                    <div><strong>Ngân hàng:</strong> <span id="sampleQrBank">-</span></div>
                                    <div><strong>Số tài khoản:</strong> <code id="sampleQrAccount" style="font-weight: 700; color: #0F172A;">-</code></div>
                                    <div><strong>Chủ tài khoản:</strong> <span id="sampleQrHolder" style="font-weight: 600;">-</span></div>
                                    <div><strong>Số tiền:</strong> <span id="sampleQrAmount" style="color: #059669; font-weight: 700;">-</span></div>
                                    <div><strong>Nội dung:</strong> <code id="sampleQrMemo" style="color: #D97706;">-</code></div>
                                </div>
                            </div>
                        </div>

                        <!-- Cột 2: Gửi Email Thanh Toán Mẫu -->
                        <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="font-weight: 700; color: #1E293B; font-size: 0.9375rem; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                    <span>✉️</span> 2. Gửi Thư Xác Nhận Thanh Toán Mẫu
                                </div>
                                <p style="font-size: 0.8125rem; color: #64748B; margin-bottom: 16px; line-height: 1.5;">
                                    Gửi một email hóa đơn xác nhận thanh toán thành công (phong cách Quiet Luxury) đến hộp thư của Quản trị viên để kiểm tra trải nghiệm khách hàng khi kích hoạt đơn hàng.
                                </p>

                                <div style="margin-bottom: 14px;">
                                    <label for="payment_test_email" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                        Email Người Nhận Thư Mẫu:
                                    </label>
                                    <input type="email" 
                                           id="payment_test_email" 
                                           class="mayhen_admin_input" 
                                           value="{{ auth()->user()->email ?? 'admin@mayhen.vn' }}" 
                                           placeholder="nhap.email.nhan@domain.com"
                                           style="width: 100%; font-size: 0.875rem; padding: 8px 12px; background: #FFFFFF;">
                                </div>

                                <div style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 6px; padding: 10px 12px; font-size: 0.75rem; color: #92400E; margin-bottom: 16px; line-height: 1.45;">
                                    ✓ Email gửi đi sẽ đính kèm chi tiết đơn hàng mẫu, tài khoản thụ hưởng và mã VietQR chuẩn xác.
                                </div>
                            </div>

                            <button type="button" 
                                    id="btnSendPaymentTestEmail" 
                                    onclick="triggerSendPaymentTestEmail()" 
                                    class="mayhen_admin_btn" 
                                    style="background: #047857; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 0.875rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 6px rgba(4, 120, 87, 0.2);">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                                <span>Gửi Email Xác Nhận Thanh Toán Mẫu</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Bảng Lịch Sử Thay Đổi & Hoàn Tác (Rollback History) -->
            <div class="mayhen_admin_card" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <div>
                        <h4 style="font-size: 1.1rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
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
                                        <td style="color: #C62828; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $hist->masked_old_value }}
                                        </td>
                                        <td style="color: #2E7D32; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
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

        // 1. Kiểm tra gửi thư SMTP thử nghiệm (Live SMTP Dispatcher)
        function triggerSmtpTest() {
            const emailInput = document.getElementById('smtp_test_email');
            const btn = document.getElementById('btnSendSmtpTest');
            const alertBox = document.getElementById('smtpTestAlert');
            const messageEl = document.getElementById('smtpTestMessage');
            const latencyEl = document.getElementById('smtpTestLatency');

            if (!emailInput || !btn || !alertBox) return;

            const recipientEmail = emailInput.value.trim();
            if (!recipientEmail) {
                alertBox.style.display = 'block';
                alertBox.style.background = '#FEF2F2';
                alertBox.style.border = '1px solid #FECACA';
                alertBox.style.color = '#991B1B';
                messageEl.textContent = '✕ Vui lòng nhập địa chỉ email người nhận kiểm thử.';
                latencyEl.textContent = '';
                emailInput.focus();
                return;
            }

            btn.disabled = true;
            const originalBtnHtml = btn.innerHTML;
            btn.innerHTML = '⏳ Đang kết nối SMTP & gửi thư...';
            alertBox.style.display = 'block';
            alertBox.style.background = '#FFF8E1';
            alertBox.style.border = '1px solid #FFE082';
            alertBox.style.color = '#B45309';
            messageEl.textContent = 'Đang bắt tay với máy chủ SMTP và phát thư kiểm thử tới [' + recipientEmail + ']...';
            latencyEl.textContent = '';

            const payload = {
                recipient_email: recipientEmail,
                mail_host: document.getElementById('field_mail_host')?.value || '',
                mail_port: document.getElementById('field_mail_port')?.value || '',
                mail_encryption: document.getElementById('field_mail_encryption')?.value || '',
                mail_username: document.getElementById('field_mail_username')?.value || '',
                mail_password: document.getElementById('field_mail_password')?.value || '',
                mail_from_address: document.getElementById('field_mail_from_address')?.value || '',
                mail_from_name: document.getElementById('field_mail_from_name')?.value || ''
            };

            fetch('{{ route("admin.settings.platform.test_email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;

                if (data.success) {
                    alertBox.style.background = '#ECFDF5';
                    alertBox.style.border = '1px solid #A7F3D0';
                    alertBox.style.color = '#065F46';
                    messageEl.textContent = '✓ ' + (data.message || 'Đã gửi email thử nghiệm thành công.');
                    latencyEl.textContent = (data.latency_ms || 0) + ' ms';
                } else {
                    alertBox.style.background = '#FEF2F2';
                    alertBox.style.border = '1px solid #FECACA';
                    alertBox.style.color = '#991B1B';
                    messageEl.textContent = '✕ ' + (data.message || 'Gửi thư thử nghiệm thất bại.');
                    latencyEl.textContent = (data.latency_ms || 0) + ' ms';
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
                alertBox.style.background = '#FEF2F2';
                alertBox.style.border = '1px solid #FECACA';
                alertBox.style.color = '#991B1B';
                messageEl.textContent = '✕ Lỗi kết nối máy chủ quản trị: ' + err.message;
                latencyEl.textContent = 'Error';
            });
        }

        // 2. Tạo mã VietQR mẫu
        function triggerGenerateSampleQr() {
            const btn = document.getElementById('btnGenerateQr');
            const img = document.getElementById('sampleQrImage');
            const loading = document.getElementById('sampleQrLoading');
            const info = document.getElementById('sampleQrInfo');

            const amount = document.getElementById('vietqr_test_amount')?.value || 50000;
            const memo = document.getElementById('vietqr_test_memo')?.value || 'MH TEST 9988';
            const bankCode = document.getElementById('field_bank_code')?.value || '';
            const bankAccount = document.getElementById('field_bank_account')?.value || '';
            const bankAccountName = document.getElementById('field_bank_account_name')?.value || '';

            if (btn) btn.disabled = true;
            if (loading) loading.style.display = 'block';

            const payload = {
                amount: amount,
                memo: memo,
                bank_code: bankCode,
                bank_account: bankAccount,
                bank_account_name: bankAccountName
            };

            fetch('{{ route("admin.settings.platform.test_payment_qr") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (btn) btn.disabled = false;
                if (loading) loading.style.display = 'none';

                if (data.success) {
                    if (img) {
                        img.src = data.qr_data_uri;
                        img.style.display = 'inline-block';
                    }
                    if (info) {
                        info.style.display = 'block';
                        document.getElementById('sampleQrBank').textContent = data.bank_name + ' (' + data.bank_code + ')';
                        document.getElementById('sampleQrAccount').textContent = data.bank_account;
                        document.getElementById('sampleQrHolder').textContent = data.bank_account_name;
                        document.getElementById('sampleQrAmount').textContent = data.amount_formatted;
                        document.getElementById('sampleQrMemo').textContent = data.memo;
                    }
                }
            })
            .catch(err => {
                if (btn) btn.disabled = false;
                if (loading) {
                    loading.textContent = '✕ Lỗi sinh mã VietQR: ' + err.message;
                    loading.style.color = '#B91C1C';
                }
            });
        }

        // 3. Gửi email xác nhận thanh toán mẫu
        function triggerSendPaymentTestEmail() {
            const emailInput = document.getElementById('payment_test_email');
            const btn = document.getElementById('btnSendPaymentTestEmail');
            const alertBox = document.getElementById('paymentTestAlert');
            const messageEl = document.getElementById('paymentTestMessage');
            const latencyEl = document.getElementById('paymentTestLatency');

            if (!emailInput || !btn || !alertBox) return;

            const recipientEmail = emailInput.value.trim();
            if (!recipientEmail) {
                alertBox.style.display = 'block';
                alertBox.style.background = '#FEF2F2';
                alertBox.style.border = '1px solid #FECACA';
                alertBox.style.color = '#991B1B';
                messageEl.textContent = '✕ Vui lòng nhập địa chỉ email người nhận kiểm thử.';
                latencyEl.textContent = '';
                emailInput.focus();
                return;
            }

            btn.disabled = true;
            const originalBtnHtml = btn.innerHTML;
            btn.innerHTML = '⏳ Đang khởi tạo hóa đơn & gửi thư...';
            alertBox.style.display = 'block';
            alertBox.style.background = '#FFF8E1';
            alertBox.style.border = '1px solid #FFE082';
            alertBox.style.color = '#B45309';
            messageEl.textContent = 'Đang gửi email xác nhận thanh toán mẫu tới [' + recipientEmail + ']...';
            latencyEl.textContent = '';

            const payload = {
                recipient_email: recipientEmail,
                amount: document.getElementById('vietqr_test_amount')?.value || 50000,
                memo: document.getElementById('vietqr_test_memo')?.value || 'MH TEST 9988',
                bank_code: document.getElementById('field_bank_code')?.value || '',
                bank_account: document.getElementById('field_bank_account')?.value || '',
                bank_account_name: document.getElementById('field_bank_account_name')?.value || ''
            };

            fetch('{{ route("admin.settings.platform.test_payment_email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;

                if (data.success) {
                    alertBox.style.background = '#ECFDF5';
                    alertBox.style.border = '1px solid #A7F3D0';
                    alertBox.style.color = '#065F46';
                    messageEl.textContent = '✓ ' + (data.message || 'Đã gửi email thanh toán mẫu thành công.');
                    latencyEl.textContent = (data.latency_ms || 0) + ' ms';
                } else {
                    alertBox.style.background = '#FEF2F2';
                    alertBox.style.border = '1px solid #FECACA';
                    alertBox.style.color = '#991B1B';
                    messageEl.textContent = '✕ ' + (data.message || 'Gửi email thanh toán mẫu thất bại.');
                    latencyEl.textContent = (data.latency_ms || 0) + ' ms';
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
                alertBox.style.background = '#FEF2F2';
                alertBox.style.border = '1px solid #FECACA';
                alertBox.style.color = '#991B1B';
                messageEl.textContent = '✕ Lỗi gửi thư thanh toán mẫu: ' + err.message;
                latencyEl.textContent = 'Error';
            });
        }

        // Tự động sinh mã QR khi mở tab Payment
        @if($activeGroup === \App\Models\PlatformSetting::GROUP_PAYMENT)
            document.addEventListener('DOMContentLoaded', function() {
                triggerGenerateSampleQr();
            });
        @endif
    </script>
@endpush
