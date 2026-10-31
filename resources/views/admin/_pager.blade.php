<div class="pager">
    <span class="muted small">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
    <nav class="pages" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm is-disabled" aria-disabled="true">← Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm" rel="prev">← Previous</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pages-gap">{{ $element }}</span>
            @else
                @foreach ($element as $page => $url)
                    @if ($page === $paginator->currentPage())
                        <span class="btn btn-sm btn-primary" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="btn btn-sm">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm" rel="next">Next →</a>
        @else
            <span class="btn btn-sm is-disabled" aria-disabled="true">Next →</span>
        @endif
    </nav>
</div>
