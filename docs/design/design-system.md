# MÂY HẸN (MAYHEN.VN) — DESIGN SYSTEM & DESIGN TOKENS

## 1. Triết Lý Thiết Kế: Quiet Luxury & Editorial Wedding
Mây Hẹn hướng đến phong cách thiệp cưới và nền tảng trực tuyến tinh tế, lãng mạn, tôn vinh thẩm mỹ và câu chuyện tình yêu của các cặp đôi.
- **Không thương mại hóa xô bồ**: Tránh xa các nút kêu gọi lòe loẹt, badge giảm giá giật gân, banner chớp nháy.
- **Khoảng trắng thở (Generous Whitespace)**: Mang lại cảm giác thanh lịch, cao cấp và trang trọng.
- **Mobile-First thực chất**: Thiệp cưới 95% được xem trên smartphone qua Zalo/Facebook; giao diện phải vừa vặn hoàn hảo từ 360px đến 430px.

---

## 2. Bảng Màu Thống Nhất (Color Tokens)

```css
:root {
    /* Primary Backgrounds */
    --mayhen-color-bg-base: #FCFBF9;       /* Ivory White - Nền trắng ngà thanh tao */
    --mayhen-color-bg-surface: #FFFFFF;    /* Pure White - Thẻ bề mặt nổi bật */
    --mayhen-color-bg-soft: #F7F4EE;       /* Warm Cream - Nền section mềm ấm */
    --mayhen-color-bg-accent: #EFE9E0;     /* Soft Beige - Khối nhấn nhẹ nhàng */

    /* Typography & Contrasts */
    --mayhen-color-text-main: #1C1917;     /* Charcoal/Stone 900 - Chữ chính đậm nét */
    --mayhen-color-text-muted: #78716C;    /* Stone 500 - Chữ phụ, caption */
    --mayhen-color-text-light: #A8A29E;    /* Stone 400 - Placeholder, nhãn mờ */

    /* Accent & Brand Identity */
    --mayhen-color-primary: #C5A880;       /* Muted Gold - Vàng ánh kim nhã nhặn */
    --mayhen-color-primary-hover: #B39265; /* Darker Gold cho hover state */
    --mayhen-color-primary-soft: #F4EFE6;  /* Tint Gold nhạt cho background badge */
    
    --mayhen-color-romantic: #D4A5A5;     /* Dusty Rose - Hồng phấn hoài niệm */
    --mayhen-color-sage: #9FAF90;         /* Sage Green - Xanh lá xô thơm tự nhiên */

    /* Semantic States */
    --mayhen-color-border: #E7E2D9;       /* Đường kẻ viền mỏng tinh tế */
    --mayhen-color-border-focus: #C5A880; /* Viền khi focus */
    --mayhen-color-success: #52796F;      /* Xanh rêu - RSVP đồng ý */
    --mayhen-color-danger: #BC4749;       /* Đỏ đất - Báo lỗi/Từ chối */
}
```

---

## 3. Hệ Thống Typography

- **Display & Headings**: `font-family: 'Playfair Display', 'Cormorant Garamond', 'Times New Roman', serif;`
- **Body & Controls**: `font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;`
- **Scale (CSS Clamp)**:
  - Display: `clamp(2.25rem, 5vw, 3.75rem)`
  - H1: `clamp(1.75rem, 3.5vw, 2.5rem)`
  - H2: `clamp(1.5rem, 2.5vw, 2rem)`
  - H3: `clamp(1.25rem, 2vw, 1.5rem)`
  - Body: `1rem` (16px), line-height: `1.65`
  - Body Small: `0.875rem` (14px)
  - Caption: `0.75rem` (12px)

---

## 4. Spacing, Radius & Shadows

```css
:root {
    --mayhen-space-xs: 4px;
    --mayhen-space-sm: 8px;
    --mayhen-space-md: 16px;
    --mayhen-space-lg: 24px;
    --mayhen-space-xl: 32px;
    --mayhen-space-2xl: 48px;
    --mayhen-space-3xl: 64px;
    --mayhen-space-4xl: 96px;

    --mayhen-radius-sm: 4px;
    --mayhen-radius-md: 8px;
    --mayhen-radius-lg: 16px;
    --mayhen-radius-pill: 9999px;

    /* Subtle Luxury Shadows */
    --mayhen-shadow-card: 0 4px 20px -2px rgba(28, 25, 23, 0.05);
    --mayhen-shadow-card-hover: 0 12px 30px -4px rgba(28, 25, 23, 0.1);
    --mayhen-shadow-envelope: 0 20px 40px -10px rgba(70, 50, 40, 0.15);
}
```
