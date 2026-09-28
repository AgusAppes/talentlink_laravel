@if ($paginator->hasPages())
    <nav class="paginacion">
        @if ($paginator->onFirstPage())
            <span class="paginacion-btn deshabilitado">« Anterior</span>
        @else
            <a class="paginacion-btn" href="{{ $paginator->previousPageUrl() }}">« Anterior</a>
        @endif

        <span class="paginacion-info">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="paginacion-btn" href="{{ $paginator->nextPageUrl() }}">Siguiente »</a>
        @else
            <span class="paginacion-btn deshabilitado">Siguiente »</span>
        @endif
    </nav>
@endif
