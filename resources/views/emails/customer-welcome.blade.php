@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $faviconUrl = \App\Models\PlatformSetting::getFaviconUrl();
    $customerName = trim((string) ($user->name ?? ''));
    $greeting = $customerName !== '' ? $customerName : 'bạn';
    $studioUrl = route('onboarding.index');
    $templatesUrl = route('templates.index');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào Mừng Bạn Đến Với {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 620px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E8DDD5; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 42px 30px 28px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FAF3ED); border-bottom: 1px solid #F0E3DB;">
                            <!-- Favicon Thương Hiệu từ Admin -->
                            <div style="display: inline-block; width: 56px; height: 56px; line-height: 56px; border-radius: 50%; background-color: #FFFFFF; border: 1px solid #E6D4CA; box-shadow: 0 4px 14px rgba(128, 80, 82, 0.08); vertical-align: middle; text-align: center;">
                                <img src="{{ $faviconUrl }}" alt="{{ $brandName }}" width="28" height="28" style="display: inline-block; width: 28px; height: 28px; vertical-align: middle; border: 0; outline: none; margin-top: -3px;">
                            </div>
                            <h2 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; color: #B58E6F; margin: 16px 0 8px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; LỜI CHÀO NỒNG ẤM
                            </h2>
                            <h1 style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 24px; line-height: 1.35; color: #684143; margin: 0; font-weight: 700; letter-spacing: normal;">
                                Chào Mừng Bạn Đến Với {{ $brandName }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Lời Chúc Mừng & Tri Ân -->
                    <tr>
                        <td style="padding: 36px 38px 24px 38px;">
                            <p style="font-size: 16px; color: #453738; margin: 0 0 16px 0; font-weight: 600;">
                                Xin chào {{ $greeting }},
                            </p>
                            <p style="font-size: 14.5px; line-height: 1.85; color: #665253; margin: 0 0 18px 0;">
                                Chúng mình vô cùng vinh hạnh và hạnh phúc khi được chào đón bạn gia nhập <strong>{{ $brandName }}</strong>. Cảm ơn bạn đã tin tưởng trao gửi để Mây Hẹn đồng hành cùng hai bạn trong hành trình chuẩn bị cho khoảnh khắc thiêng liêng nhất cuộc đời.
                            </p>
                            <p style="font-size: 14.5px; line-height: 1.85; color: #665253; margin: 0 0 24px 0;">
                                Tài khoản của bạn đã được kích hoạt thành công với email <strong style="color: #805052;">{{ $user->email }}</strong>. Giờ đây, bạn có thể tự do sáng tạo, lưu giữ những kỷ niệm tình yêu và tạo nên chiếc thiệp cưới online mang phong cách <em>Quiet Luxury</em> thanh lịch, chạm đến trái tim từng vị khách quý.
                            </p>

                            <!-- Nút Bắt Đầu Tạo Thiệp -->
                            <div style="text-align: center; margin: 28px 0 34px 0;">
                                <a href="{{ $studioUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #805052; color: #FFFFFF; font-size: 15px; font-weight: 600; text-decoration: none; padding: 15px 40px; border-radius: 12px; letter-spacing: 0.02em; box-shadow: 0 8px 22px rgba(128, 80, 82, 0.25);">
                                    Bắt Đầu Tạo Thiệp Cưới Của Bạn &rarr;
                                </a>
                            </div>

                            <!-- Khối Phân Cách -->
                            <div style="border-top: 1px solid #F0E5DE; margin: 30px 0 26px 0;"></div>

                            <!-- Header Mục Mẫu Thiệp Nổi Bật -->
                            <div style="text-align: center; margin-bottom: 24px;">
                                <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: #B58E6F; font-weight: 700;">
                                    BỘ SƯU TẬP TIÊU BIỂU
                                </span>
                                <h3 style="font-size: 18px; color: #684143; margin: 6px 0 0 0; font-weight: 700;">
                                    Gợi Ý Mẫu Thiệp Cưới Được Yêu Thích Nhất
                                </h3>
                                <p style="font-size: 13px; color: #8C7B76; margin: 6px 0 0 0;">
                                    Được tuyển chọn kỹ lưỡng, mỗi thiết kế mang một ngôn ngữ cảm xúc riêng biệt:
                                </p>
                            </div>

                            <!-- Danh Sách Mẫu Thiệp Đa Dạng -->
                            @if (!empty($featuredTemplates))
                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    @foreach ($featuredTemplates as $tpl)
                                        <tr>
                                            <td style="padding: 12px 0;">
                                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FDFBF9; border: 1px solid #ECE1D8; border-radius: 14px; overflow: hidden;">
                                                    <tr>
                                                        <!-- Cột Ảnh Thu Nhỏ -->
                                                        <td width="130" valign="middle" align="center" style="padding: 12px; background-color: #F8F2EB;">
                                                            <a href="{{ $tpl['preview_url'] }}" target="_blank" rel="noopener noreferrer" style="text-decoration: none; display: block;">
                                                                <img src="{{ $tpl['thumbnail_url'] }}" alt="{{ $tpl['name'] }}" width="110" height="130" style="display: block; width: 110px; height: 130px; object-fit: cover; border-radius: 8px; border: 1px solid #E6D4CA;">
                                                            </a>
                                                        </td>
                                                        <!-- Cột Thông Tin Mẫu -->
                                                        <td valign="middle" style="padding: 14px 18px;">
                                                            <span style="display: inline-block; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.1em; color: #805052; background-color: #F6ECE5; padding: 3px 8px; border-radius: 6px; font-weight: 700; margin-bottom: 6px;">
                                                                {{ $tpl['category_label'] }}
                                                            </span>
                                                            <h4 style="font-size: 16px; color: #52383A; margin: 0 0 6px 0; font-weight: 700; line-height: 1.35;">
                                                                <a href="{{ $tpl['preview_url'] }}" target="_blank" rel="noopener noreferrer" style="color: #52383A; text-decoration: none;">
                                                                    {{ $tpl['name'] }}
                                                                </a>
                                                            </h4>
                                                            <p style="font-size: 13px; line-height: 1.6; color: #786663; margin: 0 0 12px 0;">
                                                                {{ $tpl['tagline'] }}
                                                            </p>
                                                            <a href="{{ $tpl['preview_url'] }}" target="_blank" rel="noopener noreferrer"
                                                               style="display: inline-block; font-size: 12.5px; font-weight: 600; color: #805052; text-decoration: none;">
                                                                Xem mẫu thiệp thử &rarr;
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <!-- Link Xem Tất Cả Mẫu -->
                            <div style="text-align: center; margin: 18px 0 28px 0;">
                                <a href="{{ $templatesUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; font-size: 13.5px; font-weight: 600; color: #805052; text-decoration: underline; text-underline-offset: 4px;">
                                    Khám phá toàn bộ bộ sưu tập mẫu thiệp cưới &rarr;
                                </a>
                            </div>

                            <!-- Khối Lời Hứa & Hỗ Trợ -->
                            <div style="background-color: #F9F4EE; border-radius: 12px; padding: 18px 22px; border-left: 3px solid #B58E6F; margin-top: 24px;">
                                <p style="font-size: 13px; line-height: 1.7; color: #6E5C56; margin: 0;">
                                    <strong style="color: #684143;">Đặc quyền hỗ trợ riêng:</strong> Nếu bạn cần tư vấn chọn mẫu, hỗ trợ căn chỉnh hình ảnh kỷ niệm hoặc tích hợp nhạc nền yêu thích, đội ngũ Mây Hẹn luôn sẵn lòng đồng hành cùng bạn qua email <a href="mailto:{{ $noreplyEmail }}" style="color: #805052; text-decoration: none; font-weight: 600;">{{ $noreplyEmail }}</a>.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 24px 30px; background-color: #FAF5F0; border-top: 1px solid #EFE4DC;">
                            <p style="font-size: 12px; color: #8A7A75; margin: 0 0 6px 0; line-height: 1.6;">
                                Thư gửi tự động từ <strong>{{ $brandName }}</strong> &bull; Nền tảng tạo thiệp cưới online phong cách Quiet Luxury.
                            </p>
                            <p style="font-size: 11.5px; color: #9A8A85; margin: 0;">
                                &copy; {{ date('Y') }} {{ $brandName }}. Chúc hai bạn một hành trình trọn vẹn hạnh phúc.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
