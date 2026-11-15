---
id: CLI-GIFT-004
title: "Lập bảng dự toán ngân sách cưới (Wedding Budget) và theo dõi chi phí thực tế"
category: "Sổ Mừng cưới VietQR & Dự toán"
summary: "Hướng dẫn lập và quản lý Bảng Dự Toán Ngân Sách Cưới (Wedding Budget Planner): Thiết lập hạn mức ngân sách mục tiêu, phân bổ 7 hạng mục chi phí thiết yếu (Tiệc cưới, trang phục, quay chụp, trang trí, sính lễ), theo dõi tiền đặt cọc và cảnh báo bội chi chi phí thực tế."
reader: "PER-CLI-02 / PER-CLI-03"
order: 1
updated_at: "2026-11-15"
tags: [mayhen, sổ-mừng-cưới, du-toan-ngan-sach, wedding-budget, chi-phi-dam-cuoi, quan-ly-chi-tieu]
mapped_page: "CLI-GIFT-PAGE"
mapped_route: "gifts.index"
risk_level: "HIGH"
required_role: "customer"
---

# Lập bảng dự toán ngân sách cưới (Wedding Budget) và theo dõi chi phí thực tế

> **Dành cho:** Cô dâu & Chú rể lên kế hoạch tài chính và kiểm soát dòng tiền đám cưới  
> **Thời gian thiết lập:** Khoảng 10 - 15 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Kế Hoạch & Dự Toán Ngân Sách** (Tab: `budget`)  
> **API Cốt lõi:** `account.planner.budget.store`, `account.planner.budget.target` & `account.planner.budget.export`

Đám cưới là một trong những sự kiện tài chính lớn nhất của đời người với hàng chục khoản chi lớn nhỏ đan xen: Tiền đặt cọc nhà hàng, may váy cưới, thuê thợ quay phim chụp ảnh, tráp ăn hỏi đến thiệp mời và xe hoa. Rất nhiều cặp đôi gặp tình trạng **"vỡ kế hoạch tài chính"**, chi phí thực tế đội lên 30% - 50% so với dự tính ban đầu vì không có công cụ ghi chép và cảnh báo bội chi.

**Bảng Dự Toán Ngân Sách Cưới (Wedding Budget Planner)** của Mây Hẹn cung cấp một khung tài chính chuẩn hóa, giúp hai bạn thiết lập hạn mức chi tiêu, theo dõi chi phí ước tính vs thực tế, quản lý các đợt thanh toán cọc và xuất file Excel đối soát chi tiết.

---

## 1. Bảng Chỉ Số Cân Đối Tài Chính (Budget Balance Dashboard)

Ngay tại tab Ngân Sách, hệ thống tổng hợp bức tranh tài chính hôn lễ thời gian thực:

```
┌────────────────────────────────────────────────────────────────────────┐
│  💰 BẢNG CÂN ĐỐI NGÂN SÁCH ĐÁM CƯỚI (BUDGET OVERVIEW)                  │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  HẠN MỨC MỤC TIÊU   │   DỰ TOÁN BAN ĐẦU  │      CHI PHÍ THỰC TẾ        │
│    250,000,000 đ    │    242,000,000 đ   │       238,500,000 đ         │
│  (Ngân sách trần)   │   (Tổng hạng mục)  │   (Tiết kiệm: 11,500,000 đ) │
├─────────────────────┴────────────────────┴─────────────────────────────┤
│  TIẾN ĐỘ THANH TOÁN DÒNG TIỀN:                                         │
│  💳 Đã đặt cọc / Thanh toán: 165,000,000 đ (69.2%)                     │
│  ⏳ Còn phải thanh toán:      73,500,000 đ (30.8% - Sau khi tiệc xong) │
└────────────────────────────────────────────────────────────────────────┘
```

- **Hạn mức mục tiêu (`target_budget`):** Con số tối đa mà hai bạn thống nhất chi trả cho toàn bộ đám cưới.
- **Thanh đo an toàn (Budget Health Bar):**
  - Màu xanh ngọc: Chi phí thực tế nằm trong hạn mức an toàn (< 90%).
  - Màu vàng cam: Chi phí chạm ngưỡng 90% - 100%.
  - Màu đỏ rượu (Cảnh báo bội chi): Chi phí thực tế vượt quá hạn mức mục tiêu, hệ thống gắn cờ cảnh báo để bạn cân nhắc cắt giảm các hạng mục chưa ký hợp đồng.

---

## 2. Bảy Hạng Mục Chi Phí Thiết Yếu Trong Đám Cưới Việt Nam

Mây Hẹn cấu trúc sẵn 7 nhóm danh mục chuẩn hóa để bạn không bao giờ bỏ sót:

| Nhóm chi phí | Hạng mục con tiêu biểu | Tỷ trọng gợi ý |
| :--- | :--- | :--- |
| **1. Tiệc Cưới & Hội Trường** | Mâm cỗ tiệc cưới, đồ uống (bia, nước ngọt), phí phục vụ, bánh cưới, tháp ly champagne | **45% - 50%** tổng ngân sách |
| **2. Trang Phục & Làm Đẹp** | Thuê váy cưới cô dâu (chính lễ + đi bàn), vest chú rể, áo dài ăn hỏi, trang điểm & làm tóc | **10% - 15%** tổng ngân sách |
| **3. Quay Phim & Chụp Ảnh** | Album ảnh cưới Pre-wedding, phóng sự cưới ngày cưới, quay phim Flycam, thợ chụp truyền thống | **10% - 12%** tổng ngân sách |
| **4. Trang Trí & Không Gian** | Cổng hoa tươi, backdrop chụp ảnh (Photobooth), hoa để bàn, lối đi sân khấu, âm thanh ánh sáng | **10% - 12%** tổng ngân sách |
| **5. Nghi Lễ Truyền Thống** | Bộ tráp ăn hỏi (5 hoặc 7 hoặc 9 tráp), thuê đội bưng quả, xe hoa rước dâu, xe chở hai họ | **8% - 10%** tổng ngân sách |
| **6. Nhẫn Cưới & Trang Sức** | Cặp nhẫn cưới vàng tây/bạch kim, trang sức tặng cô dâu ngày xuất giá | **5% - 8%** tổng ngân sách |
| **7. Quà Tặng & Chi Phí Khác** | Thiệp cưới online Mây Hẹn, quà cảm ơn khách mời, phòng tân hôn, chi phí phát sinh dự phòng | **3% - 5%** tổng ngân sách |

---

## 3. Quy trình thêm và quản lý một khoản chi phí

1. Nhấp nút **[+ Thêm Khoản Chi Mới]** (`account.planner.budget.store`).
2. Điền thông tin chi tiết:
   - **Tên khoản chi:** *Ví dụ: Tiền đặt cọc nhà hàng tiệc cưới Trống Đồng*.
   - **Nhóm danh mục:** Chọn 1 trong 7 nhóm trên.
   - **Chi phí dự toán (`estimated_cost`):** Dự kiến ban đầu (ví dụ: `120,000,000 đ`).
   - **Chi phí thực tế (`actual_cost`):** Số tiền theo hợp đồng đã ký (ví dụ: `118,000,000 đ`).
   - **Số tiền đã trả / Đặt cọc (`paid_amount`):** Số tiền bạn đã chuyển khoản trước (ví dụ: cọc đợt 1 `30,000,000 đ`).
   - **Nhà cung cấp liên kết (Vendor):** Chọn Studio ảnh hoặc Nhà hàng liên quan để tiện lưu số điện thoại liên hệ.
   - **Ghi chú điều khoản:** Ghi chú ngày thanh toán đợt 2 (*Ví dụ: Thanh toán 70% còn lại trước ngày cưới 3 ngày*).
3. Bấm **[Lưu Khoản Chi]**. Hệ thống tự động tính toán lại số tiền còn nợ (`remaining_amount`) và cập nhật biểu đồ phân bổ chi phí.

---

## 4. Xuất File Báo Cáo Ngân Sách Dự Toán (Export CSV)

Khi cần gửi file dự toán chi tiết cho bố mẹ duyệt hoặc ngồi tính toán đối soát với người bạn đời:

1. Nhấp nút **[📥 Xuất Bảng Dự Toán (CSV)]** (`account.planner.budget.export`).
2. File Excel tải về có sẵn công thức tính tự động:
   - So sánh trực quan cột Dự toán và Thực tế.
   - Hiển thị khoản nào đang tiết kiệm được, khoản nào bị đội giá.
   - Bảng kê lịch trình các khoản nợ sắp đến hạn thanh toán để hai bạn chuẩn bị sẵn dòng tiền trong tài khoản.

---

## 5. Ba lời khuyên vàng để không bị vỡ kế hoạch cưới

> [!TIP]
> **Quy tắc quỹ dự phòng 10% (Emergency Fund):**  
> Luôn giữ lại 10% ngân sách mục tiêu (ví dụ ngân sách 200 triệu thì giữ 20 triệu) làm quỹ dự phòng cho các chi phí phát sinh sát ngày cưới như: mua thêm hoa tươi, tiền bồi dưỡng lái xe rước dâu, phụ phí phục vụ quá giờ của nhà hàng.

> [!TIP]
> **Cập nhật tiền cọc ngay khi chuyển khoản:**  
> Đừng đợi đến cuối tuần mới ghi chép. Mỗi khi chuyển khoản cọc váy cưới hay cọc hoa, hãy mở điện thoại vào tab Ngân Sách Mây Hẹn điền ngay con số để bức tranh tài chính luôn chính xác từng phút.
