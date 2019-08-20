@if ($paginator->hasPages())
    <div class="bootstrap-table">
        <div class="fixed-table-pagination">
            <div class="pull-right pagination">
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="disabled page-pre">
                            <i class="font-icon font-icon-arrow-left"></i>
                        </li>
                    @else
                        <li class="page-pre">
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">
                                <i class="font-icon font-icon-arrow-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="disabled"><span>{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-number active"><a>{{$page}}</a></li>
                                @else
                                    <li><a href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-next">
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next">
                                <i class="font-icon font-icon-arrow-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-next disabled">
                            <i class="font-icon font-icon-arrow-right"></i>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </div>
@endif