@if ($paginator->hasPages())
    <div class="pagination-container" style="border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background-color: var(--surface-color);">
        {{-- Pagination Info --}}
        <div class="pagination-info" style="font-size: 0.85rem; font-weight: 600; color: var(--text-main); padding: 0 10px;">
            Hiển thị 
            <strong style="font-weight: 700; color: var(--text-main);">{{ $paginator->firstItem() }}</strong> 
            đến 
            <strong style="font-weight: 700; color: var(--text-main);">{{ $paginator->lastItem() }}</strong> 
            trong số 
            <strong style="font-weight: 700; color: var(--text-main);">{{ $paginator->total() }}</strong> 
            kết quả
        </div>

        {{-- Pagination Buttons --}}
        <div class="pagination-buttons" style="display: flex; align-items: center; gap: 6px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-btn disabled" aria-disabled="true">
                    <i class="fa-solid fa-angle-left"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pagination-btn" rel="prev">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="pagination-btn disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-btn">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pagination-btn" rel="next">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            @else
                <span class="pagination-btn disabled" aria-disabled="true">
                    <i class="fa-solid fa-angle-right"></i>
                </span>
            @endif
        </div>
    </div>
@endif
