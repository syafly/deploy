<small class="text-muted">
    <i class="fas fa-info-circle me-1"></i>
    Menampilkan {{ $absensi_list->count() }} dari {{ $absensi_list->total() }} siswa
    @if(request()->hasAny(['search', 'id_kelas', 'tanggal']))
        (difilter)
    @endif
</small>

<!-- Pagination -->
<div class="pagination">
    @if($absensi_list->hasPages())
    <ul class="pagination pagination-sm mb-0 paginationAbsensi">
        {{-- Previous Page Link --}}
        @if($absensi_list->onFirstPage())
            <li class="page-item disabled">
                <span class="page-link">‹</span>
            </li>
        @else
            <li class="page-item">
                <a class="page-link" href="{{ $absensi_list->previousPageUrl() }}">‹</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach($absensi_list->getUrlRange(1, $absensi_list->lastPage()) as $page => $url)
            @if($page == $absensi_list->currentPage())
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
        @if($absensi_list->hasMorePages())
            <li class="page-item">
                <a class="page-link" href="{{ $absensi_list->nextPageUrl() }}">›</a>
            </li>
        @else
            <li class="page-item disabled">
                <span class="page-link">›</span>
            </li>
        @endif
    </ul>
    @endif
</div>
