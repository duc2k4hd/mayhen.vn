{{-- Unified Docs Footer (Quiet Luxury) --}}
<footer class="mayhen_docs_footer">
  <div class="mayhen_docs_footer_inner">
    <div class="mayhen_docs_footer_col">
      <div class="mayhen_docs_footer_brand">
        <strong>MÂY HẸN</strong>
        <p>Nền tảng thiệp cưới trực tuyến cao cấp phong cách Quiet Luxury. Tôn vinh câu chuyện tình yêu, lưu giữ khoảnh khắc vĩnh cửu.</p>
        <small>Tuân thủ Luật An toàn thông tin mạng & Luật 91/2025/QH15.</small>
      </div>
    </div>
    <div class="mayhen_docs_footer_col">
      <h4>TÀI LIỆU HƯỚNG DẪN</h4>
      <ul>
        <li><a href="{{ route('docs.index') }}">Trang chủ hướng dẫn</a></li>
        <li><a href="{{ route('pricing') }}">Bảng giá & Các gói dịch vụ</a></li>
        <li><a href="{{ route('templates.index') }}">Bộ sưu tập mẫu thiệp</a></li>
        <li><a href="{{ route('partner.index') }}">Cổng hợp tác Studio cưới</a></li>
      </ul>
    </div>
    <div class="mayhen_docs_footer_col">
      <h4>HỖ TRỢ & LIÊN HỆ</h4>
      <ul>
        <li><a href="{{ route('contact') }}">Gửi yêu cầu hỗ trợ (Ticket)</a></li>
        <li><a href="https://zalo.me" target="_blank" rel="noopener">Hotline Zalo tư vấn</a></li>
        <li><a href="{{ route('faq') }}">Câu hỏi thường gặp (FAQ)</a></li>
        <li><a href="{{ route('about') }}">Về thương hiệu Mây Hẹn</a></li>
      </ul>
    </div>
    <div class="mayhen_docs_footer_col">
      <h4>PHÁP LÝ & AN TOÀN</h4>
      <ul>
        <li><a href="{{ route('terms') }}">Điều khoản sử dụng dịch vụ</a></li>
        <li><a href="{{ route('privacy') }}">Chính sách bảo mật thông tin</a></li>
        <li><a href="{{ route('home') }}">Trạng thái hệ thống (99.9% Uptime)</a></li>
        @if(auth()->check() && auth()->user()->isAdmin())
          <li><a href="{{ route('admin.docs.index') }}">Admin Knowledge Base</a></li>
        @endif
      </ul>
    </div>
  </div>
  <div class="mayhen_docs_footer_bottom">
    <div class="mayhen_docs_footer_bottom_inner">
      <p>© {{ date('Y') }} Mây Hẹn (mayhen.vn). Tất cả quyền được bảo lưu.</p>
      <div class="mayhen_docs_footer_shortcuts">
        <span>Nhấn <kbd>?</kbd> để xem phím tắt</span>
        <span>Nhấn <kbd>Ctrl</kbd> <kbd>K</kbd> để tìm kiếm</span>
      </div>
    </div>
  </div>
</footer>

{{-- Back to Top Floating Button --}}
<button type="button" class="mayhen_docs_back_to_top" aria-label="Cuộn lên đầu trang" title="Về đầu trang">
  <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
</button>
