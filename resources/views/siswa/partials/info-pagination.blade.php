<small class="text-muted">
    <i class="fas fa-info-circle me-1"></i>
    Menampilkan {{ $siswa_list->count() }} dari {{ $siswa_list->total() }} siswa
    @if(request()->hasAny(['search', 'kelas']))
        (difilter)
    @endif
</small>

<div class="pagination">
    @if($siswa_list->hasPages())
    <ul class="pagination paginationSiswa pagination-sm mb-0">
        {{-- Previous Page Link --}}
        @if($siswa_list->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">‹</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $siswa_list->previousPageUrl() }}">‹</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach($siswa_list->getUrlRange(1, $siswa_list->lastPage()) as $page => $url)
            @if($page == $siswa_list->currentPage())
                <li class="page-item active">
                    <span class="page-link">{{ $page }}</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                </li>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if($siswa_list->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $siswa_list->nextPageUrl() }}">›</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">›</span>
            </li>
        @endif
    </ul>
    @endif
</div>