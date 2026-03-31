@if ($paginator->hasPages())
  <nav class="listing-pagination" role="navigation" aria-label="{{ $p['pagination_aria'] ?? 'Pagination' }}">
    <ul class="listing-pagination-list">
      @if ($paginator->onFirstPage())
        <li><span class="listing-pagination-link is-disabled" aria-disabled="true">{{ $p['pagination_prev'] }}</span></li>
      @else
        <li><a class="listing-pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ $p['pagination_prev'] }}</a></li>
      @endif

      @foreach ($elements as $element)
        @if (is_string($element))
          <li><span class="listing-pagination-ellipsis" aria-hidden="true">{{ $element }}</span></li>
        @endif

        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li><span class="listing-pagination-link is-current" aria-current="page">{{ $page }}</span></li>
            @else
              <li><a class="listing-pagination-link" href="{{ $url }}">{{ $page }}</a></li>
            @endif
          @endforeach
        @endif
      @endforeach

      @if ($paginator->hasMorePages())
        <li><a class="listing-pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $p['pagination_next'] }}</a></li>
      @else
        <li><span class="listing-pagination-link is-disabled" aria-disabled="true">{{ $p['pagination_next'] }}</span></li>
      @endif
    </ul>
  </nav>
@endif
