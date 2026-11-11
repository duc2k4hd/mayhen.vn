@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quản Trị Khách Mời Hộ Gia Đình & Bếp Tiệc';
@endphp

@section('account_breadcrumb', 'Khách Mời Hộ Gia Đình & Bếp Tiệc')

@section('account_content')
    <div style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #9C7A4A; font-weight: 700; margin-bottom: 6px;">
                    <span>✦</span>
                    <span>Mục 2.4 DOCX & Section 31, 32 PRD V3</span>
                </div>
                <h2 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1F1D1A; margin: 0 0 6px 0;">
                    Báo Cáo Hộ Gia Đình &amp; Suất Ăn Bếp Tiệc
                </h2>
                <p style="font-size: 13px; color: #7A726A; margin: 0; line-height: 1.5; max-width: 800px;">
                    Phân tách khoa học giữa số lời mời phát ra, số khách dự kiến và sĩ số thực tế có mặt. Thống kê chi tiết suất ăn người lớn, suất ăn trẻ em và các yêu cầu ăn chay / ăn kiêng để bàn giao cho Bếp Trưởng và Nhà Hàng.
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('account.household.print', $invitation->id) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>In Báo Cáo A4</span>
                </a>

                <a href="{{ route('account.household.export', $invitation->id) }}" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFF; border: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV Bếp Trưởng</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Hàng 4 Thẻ KPI Cốt Lõi (Phân biệt rõ ràng Lời mời vs Người tham dự) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #7A726A; font-weight: 600;">Tổng Số Lời Mời</span>
            <div style="font-size: 28px; font-weight: 700; color: #1F1D1A; margin: 4px 0;">{{ number_format($metrics['total_invitations']) }}</div>
            <span style="font-size: 12px; color: #9C7A4A;">Dự kiến: {{ number_format($metrics['total_expected_attendees']) }} người</span>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #7A726A; font-weight: 600;">Người Lớn (Suất Tiệc)</span>
            <div style="font-size: 28px; font-weight: 700; color: #2E7D32; margin: 4px 0;">{{ number_format($metrics['confirmed_adults']) }}</div>
            <span style="font-size: 12px; color: #66BB6A;">Đã xác nhận có mặt</span>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #7A726A; font-weight: 600;">Trẻ Em (Ghế Riêng)</span>
            <div style="font-size: 28px; font-weight: 700; color: #1D4ED8; margin: 4px 0;">{{ number_format($metrics['confirmed_children']) }}</div>
            <span style="font-size: 12px; color: #60A5FA;">Cần chuẩn bị ghế em bé</span>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #7A726A; font-weight: 600;">Bàn Tiệc Cần Đặt</span>
            <div style="font-size: 28px; font-weight: 700; color: #9C7A4A; margin: 4px 0;">{{ number_format($metrics['tables_needed']) }} bàn</div>
            <span style="font-size: 12px; color: #8C827A;">Tiêu chuẩn 10 người/bàn</span>
        </div>
    </div>

    {{-- Khối Phân Tích Phân Loại Khách & Ẩm Thực Bếp Tiệc --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
        {{-- Phân Loại Hộ Gia Đình --}}
        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px;">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 14px 0;">
                Phân Loại Quy Mô Lời Mời (Household Types)
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">👤</span>
                        <div>
                            <strong style="font-size: 13px; color: #1F1D1A;">Khách Đi Đơn Lẻ (Cá nhân)</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">1 người / thiệp</span>
                        </div>
                    </div>
                    <strong style="font-size: 16px; color: #9C7A4A;">{{ $metrics['household_breakdown']['individual'] }} lời mời</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">👫</span>
                        <div>
                            <strong style="font-size: 13px; color: #1F1D1A;">Khách Cặp Đôi (+1)</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">2 người / thiệp</span>
                        </div>
                    </div>
                    <strong style="font-size: 16px; color: #9C7A4A;">{{ $metrics['household_breakdown']['couple'] }} lời mời</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">👨‍👩‍👧‍👦</span>
                        <div>
                            <strong style="font-size: 13px; color: #1F1D1A;">Hộ Gia Đình (Gia quyến)</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">Từ 3 người trở lên</span>
                        </div>
                    </div>
                    <strong style="font-size: 16px; color: #9C7A4A;">{{ $metrics['household_breakdown']['family'] }} lời mời</strong>
                </div>
            </div>
        </div>

        {{-- Phân Bổ Hai Bên Gia Đình & Suất Ăn Chay --}}
        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px;">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 14px 0;">
                Phân Bổ Bên Gia Đình &amp; Suất Ăn Chay
            </h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FEF3C7; border-radius: 8px; border: 1px solid #FDE68A;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 18px;">🥗</span>
                        <div>
                            <strong style="font-size: 13px; color: #92400E;">Suất Ăn Chay Cần Chuẩn Bị</strong>
                            <span style="font-size: 11px; color: #B45309; display: block;">Thông báo bếp trưởng làm menu riêng</span>
                        </div>
                    </div>
                    <strong style="font-size: 18px; color: #B45309;">{{ $metrics['dietary_summary']['vegetarian_count'] }} suất</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">👑</span>
                        <span style="font-size: 13px; color: #1F1D1A;">Khách Nhà Trai:</span>
                    </div>
                    <strong style="font-size: 14px; color: #4A433C;">{{ $metrics['family_breakdown']['groom_side'] }} khách</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">🌸</span>
                        <span style="font-size: 13px; color: #1F1D1A;">Khách Nhà Gái:</span>
                    </div>
                    <strong style="font-size: 14px; color: #4A433C;">{{ $metrics['family_breakdown']['bride_side'] }} khách</strong>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #FAF8F5; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 16px;">🕊️</span>
                        <span style="font-size: 13px; color: #1F1D1A;">Bạn Bè &amp; Đối Tác Chung:</span>
                    </div>
                    <strong style="font-size: 14px; color: #4A433C;">{{ $metrics['family_breakdown']['mutual_friends'] }} khách</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng Chi Tiết Khách Có Yêu Cầu Ẩm Thực Đặc Biệt --}}
    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <div>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1F1D1A; margin: 0 0 2px 0;">
                    Danh Sách Yêu Cầu Ăn Kiêng &amp; Dị Ứng ({{ count($metrics['dietary_summary']['list']) }} khách)
                </h3>
                <span style="font-size: 12px; color: #7A726A;">Vị trí bàn tiệc và ghi chú ẩm thực chi tiết của từng quan khách</span>
            </div>
        </div>

        @if(count($metrics['dietary_summary']['list']) > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 1.5px solid #EAE3D9; color: #7A726A; font-size: 12px; text-transform: uppercase;">
                            <th style="padding: 10px 12px;">Khách Mời</th>
                            <th style="padding: 10px 12px;">Bàn Tiệc</th>
                            <th style="padding: 10px 12px;">Số Lượng</th>
                            <th style="padding: 10px 12px;">Nội Dung Yêu Cầu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metrics['dietary_summary']['list'] as $item)
                            <tr style="border-bottom: 1px solid #F5EFEB;">
                                <td style="padding: 12px; font-weight: 600; color: #1F1D1A;">
                                    {{ $item['guest_name'] }}
                                </td>
                                <td style="padding: 12px;">
                                    <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; background: #EFE8DD; color: #6B5E4B; font-weight: 600;">
                                        {{ $item['table_name'] }}
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #4A433C;">
                                    {{ $item['adults'] }} người lớn @if($item['children'] > 0), {{ $item['children'] }} trẻ em @endif
                                </td>
                                <td style="padding: 12px; color: #B45309; font-weight: 500;">
                                    {{ $item['requirements'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 30px; color: #8C827A; font-style: italic; font-size: 13px; background: #FAF8F5; border-radius: 8px;">
                Hiện chưa có quan khách nào ghi nhận yêu cầu ăn kiêng đặc biệt.
            </div>
        @endif
    </div>
@endsection

