<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    /**
     * Lấy danh sách mẫu email mặc định chuẩn mực Quiet Luxury.
     *
     * @return array<string, array>
     */
    public function getDefaultTemplatesData(): array
    {
        return [
            EmailTemplate::CODE_ORDER_CONFIRMED => [
                'name' => 'Xác Nhận Đơn Hàng & Kích Hoạt Lưu Trữ Thiệp Cưới',
                'channel' => EmailTemplate::CHANNEL_EMAIL,
                'subject' => 'Xác nhận đơn hàng #{{ order_code }} — Hồ sơ thiệp cưới "{{ invitation_title }}" đã kích hoạt',
                'variables' => [
                    'customer_name' => 'Tên cô dâu / chú rể đặt thiệp',
                    'order_code' => 'Mã đơn hàng (ví dụ: MH-202611-8899)',
                    'invitation_title' => 'Tên thiệp cưới (ví dụ: Vũ & Vy — The Velvet Romance)',
                    'plan_name' => 'Gói dịch vụ đã thanh toán (ví dụ: Trọn Đời - Vĩnh Cửu)',
                    'amount_formatted' => 'Số tiền thanh toán (ví dụ: 499.000 đ)',
                    'expired_at' => 'Hạn lưu trữ thiệp (ví dụ: 12/11/2027 hoặc Vĩnh viễn)',
                    'invitation_url' => 'Liên kết công khai thiệp cưới',
                    'dashboard_url' => 'Liên kết quản lý thiệp cưới trong Studio',
                ],
                'body_text' => "Kính chào {{ customer_name }},\n\nMây Hẹn Atelier xin trân trọng thông báo đơn hàng #{{ order_code }} của bạn đã hoàn tất thanh toán thành công.\n\nThông tin chi tiết:\n- Tên thiệp: {{ invitation_title }}\n- Gói dịch vụ: {{ plan_name }}\n- Số tiền: {{ amount_formatted }}\n- Hạn lưu trữ: {{ expired_at }}\n\nXem thiệp cưới: {{ invitation_url }}\nQuản lý thiệp: {{ dashboard_url }}\n\nTrân trọng,\nMây Hẹn Atelier de Mariage",
                'body_html' => $this->buildLuxuryHtmlWrapper(
                    'Đơn Hàng Đã Kích Hoạt',
                    'Kính chào {{ customer_name }}, Mây Hẹn Atelier xin trân trọng thông báo đơn hàng của bạn đã hoàn tất.',
                    '<p style="margin-bottom: 20px;">Chúng tôi xin chúc mừng bạn và người thương. Hồ sơ thiệp cưới kỹ thuật số <strong>{{ invitation_title }}</strong> đã chính thức được kích hoạt và sẵn sàng chia sẻ đến những người thân quý.</p>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; background-color: #FAF8F5; border-radius: 8px; overflow: hidden;">
                        <tr><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 13px; width: 40%;">Mã đơn hàng</td><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; font-weight: 600; color: #1F1D1A; font-size: 14px;">#{{ order_code }}</td></tr>
                        <tr><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 13px;">Gói lưu trữ</td><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ plan_name }}</td></tr>
                        <tr><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 13px;">Thanh toán</td><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; font-weight: 600; color: #9C7A4A; font-size: 14px;">{{ amount_formatted }}</td></tr>
                        <tr><td style="padding: 12px 16px; color: #7A7265; font-size: 13px;">Thời hạn hiển thị</td><td style="padding: 12px 16px; font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ expired_at }}</td></tr>
                    </table>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ invitation_url }}" style="display: inline-block; background-color: #1F1D1A; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500;">XEM THIỆP CƯỚI CÔNG KHAI</a>
                    </div>
                    <p style="font-size: 13px; color: #7A7265; text-align: center; margin-top: 10px;">Bạn có thể tùy biến thiệp cưới hoặc xuất danh sách khách mời bất kỳ lúc nào tại <a href="{{ dashboard_url }}" style="color: #9C7A4A; text-decoration: underline;">Studio Quản Trị Mây Hẹn</a>.</p>'
                ),
            ],

            EmailTemplate::CODE_INVITATION_EXPIRING_SOON => [
                'name' => 'Nhắc Nhở Thiệp Cưới Sắp Hết Hạn Lưu Trữ',
                'channel' => EmailTemplate::CHANNEL_EMAIL,
                'subject' => 'Nhắc nhở: Thiệp cưới "{{ invitation_title }}" sẽ hết hạn sau {{ days_remaining }} ngày',
                'variables' => [
                    'customer_name' => 'Tên cô dâu / chú rể',
                    'invitation_title' => 'Tên thiệp cưới',
                    'days_remaining' => 'Số ngày còn lại (ví dụ: 14 ngày hoặc 3 ngày)',
                    'expired_date' => 'Ngày hết hạn chính xác',
                    'renewal_url' => 'Liên kết gia hạn tự phục vụ',
                    'archive_url' => 'Liên kết tải trọn bộ kỷ niệm cưới .ZIP',
                ],
                'body_text' => "Kính chào {{ customer_name }},\n\nMây Hẹn Atelier xin gửi thông báo thiệp cưới \"{{ invitation_title }}\" của hai bạn dự kiến sẽ hết hạn lưu trữ sau {{ days_remaining }} ngày nữa (vào ngày {{ expired_date }}).\n\nĐể kỷ vật ngày cưới tiếp tục lung linh và mở cửa đón bạn bè ghé thăm, bạn có thể gia hạn bất kỳ lúc nào:\n- Gia hạn thiệp: {{ renewal_url }}\n- Tải toàn bộ kỷ niệm (.ZIP): {{ archive_url }}\n\nTrân trọng,\nMây Hẹn Atelier",
                'body_html' => $this->buildLuxuryHtmlWrapper(
                    'Nhắc Nhở Lưu Trữ Kỷ Niệm',
                    'Thiệp cưới của bạn dự kiến sẽ hết hạn sau {{ days_remaining }} ngày nữa.',
                    '<p style="margin-bottom: 20px;">Kính chào {{ customer_name }},</p>
                    <p style="margin-bottom: 20px;">Hồ sơ thiệp cưới kỹ thuật số <strong>{{ invitation_title }}</strong> của hai bạn sẽ chính thức kết thúc thời gian lưu trữ vào ngày <strong>{{ expired_date }}</strong> (còn <strong>{{ days_remaining }} ngày</strong>).</p>
                    <div style="background-color: #FFFDF9; border: 1px solid #E8DFD0; border-left: 3px solid #C5A880; padding: 18px 20px; border-radius: 6px; margin-bottom: 25px;">
                        <p style="margin: 0; font-size: 13.5px; color: #4A453E; line-height: 1.6;">
                            Khi hết hạn, thiệp mời công khai sẽ tạm ngừng hiển thị. Tuy nhiên, toàn bộ lời chúc phúc và sổ lưu bút của người thân vẫn được Mây Hẹn bảo toàn vẹn nguyên trong thời gian ân hạn 7 ngày.
                        </p>
                    </div>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ renewal_url }}" style="display: inline-block; background-color: #9C7A4A; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500; margin-right: 12px;">GIA HẠN THIỆP NGAY</a>
                        <a href="{{ archive_url }}" style="display: inline-block; background-color: transparent; border: 1px solid #1F1D1A; color: #1F1D1A; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500;">TẢI KỶ NIỆM (.ZIP)</a>
                    </div>'
                ),
            ],

            EmailTemplate::CODE_INVITATION_EXPIRED_GRACE => [
                'name' => 'Cảnh Báo Thiệp Cưới Đã Hết Hạn & Thời Gian Ân Hạn 7 Ngày',
                'channel' => EmailTemplate::CHANNEL_EMAIL,
                'subject' => 'Cảnh báo: Thiệp cưới "{{ invitation_title }}" đã tạm ngưng — Thời gian ân hạn 7 ngày',
                'variables' => [
                    'customer_name' => 'Tên cô dâu / chú rể',
                    'invitation_title' => 'Tên thiệp cưới',
                    'grace_period_days' => 'Số ngày ân hạn (mặc định: 7)',
                    'final_deadline' => 'Hạn chót trước khi lưu trữ sâu',
                    'renewal_url' => 'Liên kết gia hạn khôi phục',
                    'archive_url' => 'Liên kết tải dữ liệu kỷ niệm',
                ],
                'body_text' => "Kính chào {{ customer_name }},\n\nThiệp cưới \"{{ invitation_title }}\" của hai bạn đã chính thức hết hạn lưu trữ và chuyển sang chế độ tạm ngừng hiển thị công khai.\n\nHệ thống đang kích hoạt thời gian ân hạn {{ grace_period_days }} ngày (đến ngày {{ final_deadline }}) để bạn có thể gia hạn khôi phục lại thiệp hoặc tải trọn bộ kỷ niệm cưới.\n\n- Gia hạn khôi phục thiệp: {{ renewal_url }}\n- Tải dữ liệu kỷ niệm cưới: {{ archive_url }}\n\nTrân trọng,\nMây Hẹn Atelier",
                'body_html' => $this->buildLuxuryHtmlWrapper(
                    'Thời Gian Ân Hạn Thiệp Cưới',
                    'Thiệp cưới tạm ngừng hiển thị — Dữ liệu được bảo toàn trong 7 ngày ân hạn.',
                    '<p style="margin-bottom: 20px;">Kính chào {{ customer_name }},</p>
                    <p style="margin-bottom: 20px;">Thiệp cưới <strong>{{ invitation_title }}</strong> đã hết thời hạn gói lưu trữ và tạm ngừng phục vụ khách truy cập công khai.</p>
                    <div style="background-color: #FCF8F7; border: 1px solid #F1DDD9; border-left: 3px solid #D9B4A7; padding: 18px 20px; border-radius: 6px; margin-bottom: 25px;">
                        <p style="margin: 0; font-size: 13.5px; color: #5C3A35; line-height: 1.6;">
                            <strong>Thời gian ân hạn:</strong> Bạn có {{ grace_period_days }} ngày (hạn chót: <strong>{{ final_deadline }}</strong>) để gia hạn và mở lại thiệp ngay tức thì, hoặc tải về toàn bộ ảnh và sổ ký tên lưu niệm.
                        </p>
                    </div>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ renewal_url }}" style="display: inline-block; background-color: #1F1D1A; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500;">GIA HẠN KHÔI PHỤC NGAY</a>
                    </div>'
                ),
            ],

            EmailTemplate::CODE_TICKET_REPLIED => [
                'name' => 'Phản Hồi Phiếu Yêu Cầu Hỗ Trợ CSKH',
                'channel' => EmailTemplate::CHANNEL_EMAIL,
                'subject' => '[Ticket #{{ ticket_code }}] Chuyên viên Mây Hẹn đã phản hồi yêu cầu của bạn',
                'variables' => [
                    'customer_name' => 'Tên người dùng',
                    'ticket_code' => 'Mã phiếu hỗ trợ (ví dụ: TK-1082)',
                    'ticket_subject' => 'Tiêu đề yêu cầu hỗ trợ',
                    'staff_name' => 'Tên chuyên viên phản hồi',
                    'reply_content' => 'Nội dung phản hồi từ chuyên viên',
                    'ticket_url' => 'Liên kết xem chi tiết ticket',
                ],
                'body_text' => "Kính chào {{ customer_name }},\n\nChuyên viên {{ staff_name }} từ Mây Hẹn Atelier vừa phản hồi phiếu hỗ trợ #{{ ticket_code }}: \"{{ ticket_subject }}\".\n\nNội dung phản hồi:\n{{ reply_content }}\n\nXem chi tiết và tiếp tục trao đổi tại: {{ ticket_url }}\n\nTrân trọng,\nMây Hẹn Support Team",
                'body_html' => $this->buildLuxuryHtmlWrapper(
                    'Phản Hồi Hỗ Trợ Khách Hàng',
                    'Phiếu yêu cầu #{{ ticket_code }} vừa có phản hồi mới từ chuyên viên.',
                    '<p style="margin-bottom: 16px;">Kính chào {{ customer_name }},</p>
                    <p style="margin-bottom: 20px;">Chuyên viên <strong>{{ staff_name }}</strong> từ Mây Hẹn vừa gửi phản hồi về yêu cầu <em>"{{ ticket_subject }}"</em> của bạn:</p>
                    <div style="background-color: #FAF8F5; border-radius: 8px; padding: 20px; margin-bottom: 24px; border: 1px solid #EFE8DD; font-size: 14px; line-height: 1.7; color: #2C2824;">
                        {{ reply_content }}
                    </div>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ ticket_url }}" style="display: inline-block; background-color: #1F1D1A; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500;">XEM PHẢN HỒI & TRẢ LỜI</a>
                    </div>'
                ),
            ],

            EmailTemplate::CODE_WELCOME_COUPLE => [
                'name' => 'Chào Mừng Cặp Đôi Mới Gia Nhập Mây Hẹn Atelier',
                'channel' => EmailTemplate::CHANNEL_EMAIL,
                'subject' => 'Chào mừng {{ couple_names }} đến với Mây Hẹn Atelier de Mariage',
                'variables' => [
                    'couple_names' => 'Tên đôi uyên ương (ví dụ: Anh Vũ & Thảo Vy)',
                    'explore_templates_url' => 'Liên kết bộ sưu tập mẫu thiệp cưới',
                    'studio_url' => 'Liên kết bắt đầu tạo thiệp trong Studio',
                ],
                'body_text' => "Kính chào {{ couple_names }},\n\nMây Hẹn Atelier xin gửi lời chúc phúc chân thành nhất đến hai bạn trên hành trình chuẩn bị cho ngày chung đôi.\n\nTại Mây Hẹn, mỗi tấm thiệp không chỉ là một thông điệp mời dự tiệc, mà là một kỷ vật số trang nhã ghi dấu câu chuyện tình yêu riêng biệt.\n\n- Khám phá các mẫu thiệp nghệ thuật: {{ explore_templates_url }}\n- Bắt đầu thiết kế thiệp ngay: {{ studio_url }}\n\nTrân trọng,\nMây Hẹn Atelier",
                'body_html' => $this->buildLuxuryHtmlWrapper(
                    'Chào Mừng Đến Với Mây Hẹn',
                    'Nơi tình yêu được cất giữ bằng nghệ thuật thiệp cưới số Quiet Luxury.',
                    '<p style="margin-bottom: 20px;">Kính chào {{ couple_names }},</p>
                    <p style="margin-bottom: 20px;">Mây Hẹn Atelier xin gửi lời chúc mừng ấm áp nhất đến hai bạn khi bước vào chặng đường ngọt ngào chuẩn bị cho ngày trọng đại.</p>
                    <p style="margin-bottom: 24px;">Với tinh thần Quiet Luxury, chúng tôi trân trọng từng chi tiết nhỏ — từ chất liệu đồ họa giấy mỹ thuật, nét chữ thanh thoát đến điệu nhạc du dương và sự thuận tiện khi khách mời xác nhận tham dự (RSVP).</p>
                    <div style="text-align: center; margin: 32px 0;">
                        <a href="{{ explore_templates_url }}" style="display: inline-block; background-color: #9C7A4A; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500; margin-right: 12px;">KHÁM PHÁ BỘ SƯU TẬP</a>
                        <a href="{{ studio_url }}" style="display: inline-block; background-color: #1F1D1A; color: #FFFFFF; text-decoration: none; padding: 13px 32px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500;">TẠO THIỆP NGAY</a>
                    </div>'
                ),
            ],

            EmailTemplate::CODE_GUEST_INVITATION_EMAIL => [
                'name' => 'Thư Mời Cưới Trân Trọng Gửi Khách Quý',
                'channel' => EmailTemplate::CHANNEL_EMAIL,
                'subject' => 'Thư Mời Cưới Trọng Thể: {{ couple_names }} trân trọng kính mời {{ guest_name }}',
                'variables' => [
                    'guest_name' => 'Tên khách mời cá nhân hóa (ví dụ: Anh Hoàng Nam & Người thương)',
                    'couple_names' => 'Tên cô dâu & chú rể',
                    'event_date' => 'Ngày tổ chức đám cưới',
                    'event_time' => 'Giờ khai tiệc',
                    'event_venue' => 'Địa điểm tổ chức hôn lễ',
                    'event_address' => 'Địa chỉ chi tiết sảnh tiệc',
                    'invitation_url' => 'Liên kết mở thiệp cưới kèm mã khách riêng biệt',
                    'rsvp_url' => 'Liên kết xác nhận tham dự nhanh',
                ],
                'body_text' => "Kính gửi {{ guest_name }},\n\n{{ couple_names }} trân trọng kính mời bạn đến tham dự lễ thành hôn của chúng tôi vào ngày {{ event_date }} lúc {{ event_time }}.\n\nĐịa điểm: {{ event_venue }} - {{ event_address }}\n\nXem thiệp mời điện tử trang trọng tại: {{ invitation_url }}\nXác nhận tham dự (RSVP): {{ rsvp_url }}\n\nSự hiện diện của bạn là niềm vinh hạnh to lớn cho gia đình chúng tôi!",
                'body_html' => $this->buildLuxuryHtmlWrapper(
                    'Thư Mời Đám Cưới',
                    '{{ couple_names }} trân trọng kính mời {{ guest_name }} đến chung vui.',
                    '<div style="text-align: center; margin-bottom: 25px;">
                        <p style="font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; color: #9C7A4A; margin-bottom: 8px;">TRÂN TRỌNG KÍNH MỜI</p>
                        <h2 style="font-family: \'Playfair Display\', Georgia, serif; font-size: 26px; color: #1F1D1A; font-weight: normal; margin: 0;">{{ guest_name }}</h2>
                    </div>
                    <p style="text-align: center; color: #4A453E; margin-bottom: 24px; font-size: 14.5px; line-height: 1.7;">
                        Đến tham dự buổi tiệc mừng lễ thành hôn ấm cúng của chúng tôi.<br>
                        Sự hiện diện và lời chúc phúc của bạn sẽ là món quà vô giá đánh dấu khởi đầu hạnh phúc trọn đời.
                    </p>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 28px; background-color: #FAF8F5; border-radius: 8px;">
                        <tr><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 13px; width: 35%;">Thời gian</td><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ event_time }} — {{ event_date }}</td></tr>
                        <tr><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 13px;">Địa điểm</td><td style="padding: 12px 16px; border-bottom: 1px solid #EFE8DD; font-weight: 600; color: #1F1D1A; font-size: 14px;">{{ event_venue }}</td></tr>
                        <tr><td style="padding: 12px 16px; color: #7A7265; font-size: 13px;">Địa chỉ</td><td style="padding: 12px 16px; font-weight: 500; color: #4A453E; font-size: 13.5px;">{{ event_address }}</td></tr>
                    </table>
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ invitation_url }}" style="display: inline-block; background-color: #1F1D1A; color: #FFFFFF; text-decoration: none; padding: 14px 34px; border-radius: 6px; font-size: 14px; letter-spacing: 0.05em; font-weight: 500;">MỞ THIỆP MỜI TRANG TRỌNG</a>
                    </div>'
                ),
            ],
        ];
    }

    /**
     * Dựng khung HTML Quiet Luxury chuẩn email responsive.
     */
    protected function buildLuxuryHtmlWrapper(string $badgeText, string $preheader, string $contentHtml): string
    {
        return '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mây Hẹn Atelier</title>
</head>
<body style="margin: 0; padding: 0; background-color: #FAF8F5; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #1F1D1A; line-height: 1.6;">
    <div style="display: none; max-height: 0px; overflow: hidden; font-size: 1px; line-height: 1px; color: #FAF8F5;">
        '.htmlspecialchars($preheader, ENT_QUOTES, 'UTF-8').'
    </div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #FAF8F5; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #FFFFFF; border-radius: 12px; border: 1px solid #EFE8DD; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden;">
                    <!-- Brand Header -->
                    <tr>
                        <td style="padding: 36px 30px 24px; text-align: center; border-bottom: 1px solid #F5F1EB; background-color: #FFFDFB;">
                            <div style="font-family: \'Playfair Display\', Georgia, serif; font-size: 22px; letter-spacing: 0.25em; color: #1F1D1A; text-transform: uppercase; margin-bottom: 6px;">M Â Y &nbsp; H Ẹ N</div>
                            <div style="font-size: 10px; letter-spacing: 0.35em; color: #9C7A4A; text-transform: uppercase;">ATELIER DE MARIAGE</div>
                            <div style="margin-top: 18px;">
                                <span style="display: inline-block; padding: 4px 14px; background-color: #F8F5EE; border: 1px solid #EFE8DD; border-radius: 20px; font-size: 11px; letter-spacing: 0.1em; color: #7A7265; text-transform: uppercase;">
                                    '.htmlspecialchars($badgeText, ENT_QUOTES, 'UTF-8').'
                                </span>
                            </div>
                        </td>
                    </tr>
                    <!-- Main Body Content -->
                    <tr>
                        <td style="padding: 36px 32px; font-size: 14.5px; line-height: 1.7; color: #2C2824;">
                            '.$contentHtml.'
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 30px; text-align: center; background-color: #FAF8F5; border-top: 1px solid #EFE8DD; font-size: 12px; color: #8C8476;">
                            <p style="margin: 0 0 6px;">Mây Hẹn — Nền tảng thiệp cưới trực tuyến cao cấp.</p>
                            <p style="margin: 0; font-size: 11.5px; color: #A69E90;">© '.date('Y').' MayHen.vn • Hotline: 0988.xxx.xxx • contact@mayhen.vn</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Khởi tạo các mẫu email mặc định vào CSDL nếu chưa tồn tại.
     */
    public function seedDefaultTemplates(): int
    {
        $defaults = $this->getDefaultTemplatesData();
        $seededCount = 0;

        foreach ($defaults as $code => $data) {
            $existing = EmailTemplate::where('code', $code)->first();
            if (! $existing) {
                EmailTemplate::create([
                    'code' => $code,
                    'name' => $data['name'],
                    'channel' => $data['channel'],
                    'subject' => $data['subject'],
                    'body_html' => $data['body_html'],
                    'body_text' => $data['body_text'],
                    'variables' => $data['variables'],
                    'is_active' => true,
                ]);
                $seededCount++;
            }
        }

        return $seededCount;
    }

    public function getTemplateByCode(string $code): ?EmailTemplate
    {
        return EmailTemplate::where('code', $code)->first();
    }

    /**
     * Dữ liệu mẫu (sample mock data) để xem trước và gửi thử nghiệm.
     *
     * @return array<string, string>
     */
    public function getSampleVariables(string $code): array
    {
        return match ($code) {
            EmailTemplate::CODE_ORDER_CONFIRMED => [
                'customer_name' => 'Lê Nguyễn Hoàng Nam',
                'order_code' => 'MH-'.date('Ym').'-9821',
                'invitation_title' => 'Hoàng Nam & Mai Phương — Whispering Silk',
                'plan_name' => 'Gói Trọn Đời (Lifetime Forever)',
                'amount_formatted' => '499.000 đ',
                'expired_at' => 'Lưu trữ trọn đời',
                'invitation_url' => url('/thiep-cuoi/hoang-nam-mai-phuong'),
                'dashboard_url' => url('/dashboard'),
            ],
            EmailTemplate::CODE_INVITATION_EXPIRING_SOON => [
                'customer_name' => 'Hoàng Nam & Mai Phương',
                'invitation_title' => 'Hoàng Nam & Mai Phương — Whispering Silk',
                'days_remaining' => '14',
                'expired_date' => now()->addDays(14)->format('d/m/Y'),
                'renewal_url' => url('/tai-khoan/thiep-cuoi/gia-han/1'),
                'archive_url' => url('/tai-khoan/thiep-cuoi/xuat-du-lieu/1'),
            ],
            EmailTemplate::CODE_INVITATION_EXPIRED_GRACE => [
                'customer_name' => 'Hoàng Nam & Mai Phương',
                'invitation_title' => 'Hoàng Nam & Mai Phương — Whispering Silk',
                'grace_period_days' => '7',
                'final_deadline' => now()->addDays(7)->format('d/m/Y'),
                'renewal_url' => url('/tai-khoan/thiep-cuoi/gia-han/1'),
                'archive_url' => url('/tai-khoan/thiep-cuoi/xuat-du-lieu/1'),
            ],
            EmailTemplate::CODE_TICKET_REPLIED => [
                'customer_name' => 'Hoàng Nam',
                'ticket_code' => 'TK-202611-042',
                'ticket_subject' => 'Hỗ trợ thay đổi định dạng nhạc nền thiệp cưới',
                'staff_name' => 'Trần Lan Hương (CSKH Mây Hẹn)',
                'reply_content' => 'Chào bạn Nam, Mây Hẹn đã hỗ trợ bạn chuyển đổi file MP3 với chất lượng cao nhất và nạp vào thiệp thành công. Bạn hãy mở thiệp kiểm tra lại nhé!',
                'ticket_url' => url('/support/tickets/42'),
            ],
            EmailTemplate::CODE_WELCOME_COUPLE => [
                'couple_names' => 'Hoàng Nam & Mai Phương',
                'explore_templates_url' => url('/mau-thiep'),
                'studio_url' => url('/studio/tao-moi'),
            ],
            EmailTemplate::CODE_GUEST_INVITATION_EMAIL => [
                'guest_name' => 'Bác Tuấn & Gia Đình',
                'couple_names' => 'Hoàng Nam & Mai Phương',
                'event_date' => 'Chủ Nhật, ngày 28/11/2026',
                'event_time' => '18:00',
                'event_venue' => 'Trung Tâm Hội Nghị Tiệc Cưới White Palace',
                'event_address' => '194 Hoàng Văn Thụ, Phường 9, Quận Phú Nhuận, TP.HCM',
                'invitation_url' => url('/thiep-cuoi/hoang-nam-mai-phuong?guest=BAC_TUAN'),
                'rsvp_url' => url('/thiep-cuoi/hoang-nam-mai-phuong#rsvp'),
            ],
            default => [
                'customer_name' => 'Khách Hàng Mây Hẹn',
                'invitation_title' => 'Thiệp Cưới Mây Hẹn',
            ],
        };
    }

    /**
     * Render mẫu với dữ liệu token thực tế.
     *
     * @return array{subject: string, body_html: string, body_text: string}
     */
    public function render(EmailTemplate|string $template, array $data = []): array
    {
        if (is_string($template)) {
            $templateModel = EmailTemplate::where('code', $template)->first();
            if (! $templateModel) {
                // Fallback nếu chưa lưu trong DB thì lấy từ default
                $defaults = $this->getDefaultTemplatesData();
                $defaultData = $defaults[$template] ?? null;
                if (! $defaultData) {
                    throw new \InvalidArgumentException("Email template [{$template}] không tồn tại.");
                }
                $templateModel = new EmailTemplate($defaultData);
            }
        } else {
            $templateModel = $template;
        }

        $subject = $this->replaceTokens($templateModel->subject, $data);
        $bodyHtml = $this->replaceTokens($templateModel->body_html, $data);
        $bodyText = $this->replaceTokens($templateModel->body_text ?? '', $data);

        return [
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText,
        ];
    }

    /**
     * Thay thế các token dạng {{ variable }} và {{variable}}.
     */
    protected function replaceTokens(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $valStr = (string) $value;
            // Thay thế cả {{ key }} và {{key}}
            $content = str_replace(["{{ {$key} }}", "{{{$key}}}"], $valStr, $content);
        }

        return $content;
    }

    /**
     * Gửi thử nghiệm một mẫu email đến hòm thư test.
     */
    public function sendTestEmail(EmailTemplate $template, string $recipientEmail, array $sampleData = []): bool
    {
        $mergedData = array_merge($this->getSampleVariables($template->code), $sampleData);
        $rendered = $this->render($template, $mergedData);

        try {
            Mail::html($rendered['body_html'], function ($message) use ($recipientEmail, $rendered) {
                $message->to($recipientEmail)
                    ->subject('[TEST] '.$rendered['subject']);
            });

            $template->update([
                'last_tested_at' => now(),
                'last_tested_email' => $recipientEmail,
            ]);

            AuditLogger::log(
                'EMAIL_TEMPLATE_TEST_SENT',
                EmailTemplate::class,
                $template->id,
                "Gửi email thử nghiệm mẫu '{$template->name}' ({$template->code}) tới {$recipientEmail}",
                ['recipient' => $recipientEmail]
            );

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    /**
     * Cập nhật nội dung mẫu email kèm kiểm toán.
     */
    public function updateTemplate(EmailTemplate $template, array $attributes, ?User $admin = null): EmailTemplate
    {
        $oldData = $template->only(['name', 'subject', 'body_html', 'body_text', 'is_active']);

        $template->update([
            'name' => $attributes['name'] ?? $template->name,
            'subject' => $attributes['subject'] ?? $template->subject,
            'body_html' => $attributes['body_html'] ?? $template->body_html,
            'body_text' => $attributes['body_text'] ?? $template->body_text,
            'is_active' => isset($attributes['is_active']) ? (bool) $attributes['is_active'] : $template->is_active,
        ]);

        AuditLogger::log(
            'EMAIL_TEMPLATE_UPDATED',
            EmailTemplate::class,
            $template->id,
            "Cập nhật nội dung mẫu thông báo '{$template->name}' ({$template->code})",
            [
                'old' => $oldData,
                'new' => $template->only(['name', 'subject', 'body_html', 'body_text', 'is_active']),
                'updated_by' => $admin?->id ?? auth()->id(),
            ]
        );

        return $template;
    }

    /**
     * Khôi phục mẫu về nội dung mặc định của Mây Hẹn Atelier.
     */
    public function resetToDefault(EmailTemplate $template, ?User $admin = null): bool
    {
        $defaults = $this->getDefaultTemplatesData();
        if (! isset($defaults[$template->code])) {
            return false;
        }

        $defaultData = $defaults[$template->code];
        $template->update([
            'name' => $defaultData['name'],
            'subject' => $defaultData['subject'],
            'body_html' => $defaultData['body_html'],
            'body_text' => $defaultData['body_text'],
            'variables' => $defaultData['variables'],
            'is_active' => true,
        ]);

        AuditLogger::log(
            'EMAIL_TEMPLATE_RESET_TO_DEFAULT',
            EmailTemplate::class,
            $template->id,
            "Khôi phục mẫu thông báo '{$template->name}' ({$template->code}) về mặc định gốc của hệ thống",
            ['reset_by' => $admin?->id ?? auth()->id()]
        );

        return true;
    }
}
