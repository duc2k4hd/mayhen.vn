@if ($paginator->hasPages())
    <nav class="mayhen_client_pagination" role="navigation" aria-label="Điều hướng phân trang">
        <p class="mayhen_client_pagination_summary">
            Hiển thị
            <strong>{{ $paginator->firstItem() }}</strong>&ndash;<strong>{{ $paginator->lastItem() }}</strong>
            trong <strong>{{ number_format($paginator->total()) }}</strong> kết quả
        </p>

        <ul class="mayhen_client_pagination_list">
            {{-- Nút Trang trước --}}
            @if ($paginator->onFirstPage())
                <li class="mayhen_client_pagination_item is-disabled" aria-disabled="true" aria-label="Trang trước">
                    <span class="mayhen_client_pagination_btn" aria-hidden="true">&larr; Trước</span>
                </li>
            @else
                <li class="mayhen_client_pagination_item">
                    <a class="mayhen_client_pagination_btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Trang trước">&larr; Trước</a>
                </li>
            @endif

            {{-- Số trang --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="mayhen_client_pagination_item is-ellipsis" aria-disabled="true">
                        <span class="mayhen_client_pagination_ellipsis">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="mayhen_client_pagination_item is-active" aria-current="page">
                                <span class="mayhen_client_pagination_num">{{ $page }}</span>
                            </li>
                        @else
                            <li class="mayhen_client_pagination_item">
                                <a class="mayhen_client_pagination_num" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Nút Trang sau --}}
            @if ($paginator->hasMorePages())
                <li class="mayhen_client_pagination_item">
                    <a class="mayhen_client_pagination_btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Trang sau">Sau &rarr;</a>
                </li>
            @else
                <li class="mayhen_client_pagination_item is-disabled" aria-disabled="true" aria-label="Trang sau">
                    <span class="mayhen_client_pagination_btn" aria-hidden="true">Sau &rarr;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
