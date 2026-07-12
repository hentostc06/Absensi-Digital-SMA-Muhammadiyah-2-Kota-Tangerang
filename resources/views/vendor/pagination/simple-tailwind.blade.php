@if ($paginator->hasPages())
    <nav class="bc-pagination simple" role="navigation" aria-label="Navigasi halaman">
        <div class="bc-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="bc-page disabled">Sebelumnya</span>
            @else
                <a class="bc-page" href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="bc-page" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
            @else
                <span class="bc-page disabled">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
