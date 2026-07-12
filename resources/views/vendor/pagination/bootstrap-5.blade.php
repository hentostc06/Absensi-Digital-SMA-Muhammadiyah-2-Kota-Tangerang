@if ($paginator->hasPages())
    <nav class="bc-pagination" role="navigation" aria-label="Navigasi halaman">
        <div class="bc-pagination-info">
            Menampilkan
            <strong>{{ $paginator->firstItem() }}</strong>
            sampai
            <strong>{{ $paginator->lastItem() }}</strong>
            dari
            <strong>{{ $paginator->total() }}</strong>
            data
        </div>

        <div class="bc-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="bc-page disabled">Sebelumnya</span>
            @else
                <a class="bc-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="bc-page dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="bc-page active">{{ $page }}</span>
                        @else
                            <a class="bc-page" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="bc-page" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else
                <span class="bc-page disabled">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
