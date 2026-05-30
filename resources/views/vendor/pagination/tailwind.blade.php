@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="ui-pagination">

        <div class="flex items-center justify-between gap-2 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="ui-pagination-btn ui-pagination-btn-disabled rounded-md">{!! __('pagination.previous') !!}</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ui-pagination-btn rounded-md">{!! __('pagination.previous') !!}</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ui-pagination-btn rounded-md">{!! __('pagination.next') !!}</a>
            @else
                <span class="ui-pagination-btn ui-pagination-btn-disabled rounded-md">{!! __('pagination.next') !!}</span>
            @endif
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:flex-col sm:gap-2 sm:items-stretch lg:flex-row lg:items-center lg:justify-between">
            <p class="leading-5">
                {!! __('Showing') !!}
                @if ($paginator->firstItem())
                    <span class="font-medium text-foreground">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-medium text-foreground">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                {!! __('of') !!}
                <span class="font-medium text-foreground">{{ $paginator->total() }}</span>
                {!! __('results') !!}
            </p>

            <span class="inline-flex rtl:flex-row-reverse">
                @if ($paginator->onFirstPage())
                    <span class="ui-pagination-btn ui-pagination-btn-disabled rounded-l-lg" aria-hidden="true">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ui-pagination-btn rounded-l-lg" aria-label="{{ __('pagination.previous') }}">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="ui-pagination-btn ui-pagination-btn-disabled -ml-px">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="ui-pagination-btn ui-pagination-btn-active -ml-px" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="ui-pagination-btn -ml-px" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ui-pagination-btn -ml-px rounded-r-lg" aria-label="{{ __('pagination.next') }}">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @else
                    <span class="ui-pagination-btn ui-pagination-btn-disabled -ml-px rounded-r-lg" aria-hidden="true">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
            </span>
        </div>
    </nav>
@endif
