@if ($employees->hasPages() || $employees->total() > 0)
    <div class="card mt-3">
        <div class="datatable-footer d-flex justify-content-between align-items-center flex-wrap px-3 py-2">
            <div class="d-flex align-items-center gap-1">
                <span class="text-muted small">Show</span>
                <select id="grid_per_page" class="form-select form-select-sm" style="width: 75px;">
                    <option value="12" {{ $employees->perPage() == 12 ? 'selected' : '' }}>12</option>
                    <option value="24" {{ $employees->perPage() == 24 ? 'selected' : '' }}>24</option>
                    <option value="48" {{ $employees->perPage() == 48 ? 'selected' : '' }}>48</option>
                    <option value="96" {{ $employees->perPage() == 96 ? 'selected' : '' }}>96</option>
                </select>
                <span class="text-muted small">entries</span>
            </div>

            <div class="text-muted small">
                @if ($employees->total() > 0)
                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} entries
                @else
                    Showing 0 to 0 of 0 entries
                @endif
            </div>

            @if ($employees->hasPages())
                <nav aria-label="Employee grid pagination">
                    <ul class="pagination pagination-sm mb-0">
                        {{-- Previous Page Link --}}
                        @if ($employees->onFirstPage())
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link">Previous</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link grid-page-link" href="javascript:void(0);" data-page="{{ $employees->currentPage() - 1 }}" rel="prev">Previous</a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @php
                            $start = max(1, $employees->currentPage() - 2);
                            $end = min($employees->lastPage(), $employees->currentPage() + 2);
                        @endphp

                        @if ($start > 1)
                            <li class="page-item">
                                <a class="page-link grid-page-link" href="javascript:void(0);" data-page="1">1</a>
                            </li>
                            @if ($start > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        @for ($page = $start; $page <= $end; $page++)
                            @if ($page == $employees->currentPage())
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link grid-page-link" href="javascript:void(0);" data-page="{{ $page }}">{{ $page }}</a></li>
                            @endif
                        @endfor

                        @if ($end < $employees->lastPage())
                            @if ($end < $employees->lastPage() - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link grid-page-link" href="javascript:void(0);" data-page="{{ $employees->lastPage() }}">{{ $employees->lastPage() }}</a>
                            </li>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($employees->hasMorePages())
                            <li class="page-item">
                                <a class="page-link grid-page-link" href="javascript:void(0);" data-page="{{ $employees->currentPage() + 1 }}" rel="next">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link">Next</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endif
