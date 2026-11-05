---
name: mayhen-guest-rsvp
description: Guest grouping, household RSVP, dietary preferences, personalized links, and Zalo messaging.
---

# mayhen-guest-rsvp

## Applicable Paths
`app/Services/Guests/, app/Http/Controllers/Client/GuestController.php`

## Purpose
Manage wedding guests and comprehensive household RSVPs.

## Mandatory Rules
- Support individual, couple, and household invitations.
- Track counts: adults, children, attendance response, dietary needs, wishes.
- Generate personalized invitation links and pre-formatted Zalo invite messages.
- Export and import via Excel/CSV safely.
- UI/UX Standard: Phần xác nhận tham dự (RSVP) và Sổ ký tên (Signature) bắt buộc phải có trong 100% mẫu thiệp cưới. TUYỆT ĐỐI CẤM chèn ảnh cặp đôi hay ảnh stock người vào 2 phần này; chỉ được trang trí bằng hiệu ứng thuần (CSS canvas, stardust), sticker SVG, tem niêm phong sáp (wax seal) hoặc hoa văn đồ họa.

## Definition of Done
- Verified against Master Prompt requirements.
- Code is clean, well-documented, tested, and formatted.
