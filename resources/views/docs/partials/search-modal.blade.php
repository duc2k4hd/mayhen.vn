{{-- Search Modal Dialog (Native HTML5 <dialog>) --}}
<dialog id="mayhen_docs_search_modal" class="mayhen_docs_search_modal">
  <div class="mayhen_docs_search_modal_box">
    <div class="mayhen_docs_search_input_wrap">
      <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <input type="search" id="mayhen_docs_search_input" placeholder="Tìm kiếm hướng dẫn, quy trình, lỗi thường gặp..." autocomplete="off">
      <button type="button" class="mayhen_docs_search_close" data-action="close-search" title="Đóng tìm kiếm (Esc)">
        <span class="mayhen_docs_kbd_badge">ESC</span>
      </button>
    </div>
    <div id="mayhen_docs_search_results" class="mayhen_docs_search_results">
      <div class="mayhen_docs_search_empty">Gõ từ khóa có dấu hoặc không dấu để tìm kiếm tức thì...</div>
    </div>
    <div class="mayhen_docs_search_modal_footer">
      <span><kbd>↑</kbd> <kbd>↓</kbd> để chọn</span>
      <span><kbd>Enter</kbd> để mở</span>
      <span><kbd>Esc</kbd> để đóng</span>
    </div>
  </div>
</dialog>

