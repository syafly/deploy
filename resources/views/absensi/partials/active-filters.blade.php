@if(request()->hasAny(['search', 'kelas', 'tanggal']))
<div class="d-flex align-items-center flex-wrap gap-2">
    <small class="text-muted me-2">Filter aktif:</small>
    
    @if(request('search'))
    <span class="badge cls-filter-absensi bg-primary d-flex align-items-center">
        Pencarian: "{{ request('search') }}"
        <a href="{{ request()->fullUrlWithoutQuery('search') }}" class="text-white ms-2">
            <i class="fas fa-times"></i>
        </a>
    </span>
    @endif

    @if(request('tanggal'))
    <span class="badge cls-filter-absensi bg-primary d-flex align-items-center">
        Tanggal: "{{ request('tanggal') }}"
        <a href="{{ request()->fullUrlWithoutQuery('tanggal') }}" class="text-white ms-2">
            <i class="fas fa-times"></i>
        </a>
    </span>
    @endif

    @if(request('kelas'))
    @php
        $selectedKelas = $listKelas->firstWhere('id', request('kelas'));
    @endphp
    <span class="badge cls-filter-absensi bg-info d-flex align-items-center">
        Kelas: {{ $selectedKelas->nama_kelas ?? 'Tidak Diketahui' }}
        <a href="{{ request()->fullUrlWithoutQuery('kelas') }}" class="text-white ms-2">
            <i class="fas fa-times"></i>
        </a>
    </span>
    @endif

    <a href="{{ route('absensi') }}" class="btn btn-sm btn-outline-secondary clear-filter-absensi">
        <i class="fas fa-times me-1"></i>Hapus Semua Filter
    </a>
</div>
@endif