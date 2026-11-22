@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $brandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $faviconUrl = \App\Models\PlatformSetting::getFaviconUrl();
    $customerName = trim((string) ($user->name ?? ''));
    $greeting = $customerName !== '' ? $customerName : 'bạn';
    $summaryText = $news->summary ?: \Illuminate\Support\Str::limit(strip_tags($news->content), 260);
    $coverUrl = null;
    if (!empty($news->cover_image_url)) {
        $coverUrl = \Illuminate\Support\Str::startsWith($news->cover_image_url, ['http://', 'https://'])
            ? $news->cover_image_url
            : asset(ltrim($news->cover_image_url, '/'));
    }
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $news->title }} | {{ $brandName }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF7F3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; color: #453738; -webkit-font-smoothing: antialiased; line-height: 1.65;">
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FAF7F3; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 620px; background-color: #FFFFFF; border-radius: 20px; border: 1px solid #E8DDD5; overflow: hidden; box-shadow: 0 15px 45px rgba(75, 48, 43, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 38px 30px 26px 30px; background: radial-gradient(ellipse at 50% 50%, #FFFDF9, #FAF3ED); border-bottom: 1px solid #F0E3DB;">
                            <div style="display: inline-block; width: 52px; height: 52px; line-height: 52px; border-radius: 50%; background-color: #FFFFFF; border: 1px solid #E6D4CA; box-shadow: 0 4px 14px rgba(128, 80, 82, 0.08); vertical-align: middle; text-align: center;">
                                <img src="{{ $faviconUrl }}" alt="{{ $brandName }}" width="26" height="26" style="display: inline-block; width: 26px; height: 26px; vertical-align: middle; border: 0; outline: none; margin-top: -3px;">
                            </div>
                            <h2 style="font-size: 11.5px; letter-spacing: 0.18em; text-transform: uppercase; color: #B58E6F; margin: 14px 0 8px 0; font-weight: 700;">
                                {{ $brandNameUpper }} &bull; {{ mb_strtoupper($news->category_label ?: 'BẢN TIN MỚI', 'UTF-8') }}
                            </h2>
                            <h1 style="font-size: 22px; line-height: 1.4; color: #684143; margin: 0; font-weight: 700;">
                                {{ $news->title }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 34px 36px 26px 36px;">
                            <p style="font-size: 15.5px; color: #453738; margin: 0 0 14px 0; font-weight: 600;">
                                Xin chào {{ $greeting }},
                            </p>
                            <p style="font-size: 14.5px; line-height: 1.8; color: #665253; margin: 0 0 20px 0;">
                                Đội ngũ <strong>{{ $brandName }}</strong> vừa cập nhật một tin tức mới dành cho cộng đồng thành viên. Dưới đây là bản tóm tắt nhanh để bạn tiện theo dõi:
                            </p>

                            @if ($coverUrl)
                                <div style="margin: 0 0 22px 0; border-radius: 14px; overflow: hidden; border: 1px solid #ECE1D8;">
                                    <a href="{{ $newsUrl }}" target="_blank" rel="noopener noreferrer" style="display: block; text-decoration: none;">
                                        <img src="{{ $coverUrl }}" alt="{{ $news->title }}" width="548" style="display: block; width: 100%; max-height: 280px; object-fit: cover; border: 0;">
                                    </a>
                                </div>
                            @endif

                            <!-- Khối Tóm tắt bài viết -->
                            <div style="background-color: #FDFBF9; border: 1px solid #ECE1D8; border-left: 4px solid #805052; border-radius: 12px; padding: 20px 22px; margin-bottom: 26px;">
                                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.14em; color: #B58E6F; font-weight: 700; margin-bottom: 8px;">
                                    TÓM TẮT NỘI DUNG
                                </div>
                                <p style="font-size: 14.5px; line-height: 1.8; color: #52383A; margin: 0;">
                                    {{ $summaryText }}
                                </p>
                            </div>

                            <!-- Nút CTA Xem Chi Tiết -->
                            <div style="text-align: center; margin: 28px 0 28px 0;">
                                <a href="{{ $newsUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="display: inline-block; background-color: #805052; color: #FFFFFF; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 36px; border-radius: 12px; letter-spacing: 0.02em; box-shadow: 0 8px 22px rgba(128, 80, 82, 0.22);">
                                    Đọc Toàn Bộ Bài Viết Trên {{ $brandName }} &rarr;
                                </a>
                            </div>

                            <div style="text-align: center; margin-bottom: 18px;">
                                <a href="{{ $allNewsUrl }}" target="_blank" rel="noopener noreferrer"
                                   style="font-size: 13px; color: #8C7B76; text-decoration: underline; text-underline-offset: 4px;">
                                    Xem thêm các tin tức &amp; cập nhật khác tại chuyên mục Tin Tức
                                </a>
                            </div>

                            <!-- Hỗ trợ -->
                            <div style="background-color: #F9F4EE; border-radius: 12px; padding: 16px 20px; margin-top: 24px;">
                                <p style="font-size: 12.5px; line-height: 1.7; color: #6E5C56; margin: 0;">
                                    Bạn nhận được thư này vì đã đăng ký tài khoản thành viên trên hệ thống <strong>{{ $brandName }}</strong> (<span style="color: #805052;">{{ $user->email }}</span>). Mọi góp ý hoặc câu hỏi hỗ trợ xin gửi về <a href="mailto:{{ $noreplyEmail }}" style="color: #805052; text-decoration: none; font-weight: 600;">{{ $noreplyEmail }}</a>.
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 22px 30px; background-color: #FAF5F0; border-top: 1px solid #EFE4DC;">
                            <p style="font-size: 12px; color: #8A7A75; margin: 0 0 6px 0; line-height: 1.6;">
                                Thư thông báo bản tin từ <strong>{{ $brandName }}</strong> &bull; Nền tảng tạo thiệp cưới online phong cách Quiet Luxury.
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
