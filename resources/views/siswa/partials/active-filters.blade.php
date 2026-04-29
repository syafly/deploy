@if(request()->hasAny(['search', 'kelas']))
<div class="d-flex align-items-center flex-wrap gap-2">
    <small class="text-muted me-2">Filter aktif:</small>
    
    @if(request('search'))
    <span class="badge cls-filter-siswa bg-primary d-flex align-items-center">
        Pencarian: "{{ request('search') }}"
        <a href="{{ request()->fullUrlWithoutQuery('search') }}" class="text-white ms-2">
            <i class="fas fa-times"></i>
        </a>
    </span>
    @endif

    @if(request('kelas'))
    @php
        $selectedKelas = $kelas_list->firstWhere('id', request('kelas'));
    @endphp
    <span class="badge cls-filter-siswa bg-info d-flex align-items-center">
        Kelas: {{ $selectedKelas->nama_kelas ?? 'Tidak Diketahui' }}
        <a href="{{ request()->fullUrlWithoutQuery('kelas') }}" class="text-white ms-2">
            <i class="fas fa-times"></i>
        </a>
    </span>
    @endif

    <a href="{{ route('siswa') }}" class="btn btn-sm btn-outline-secondary clear-filter-siswa">
        <i class="fas fa-times me-1"></i>Hapus Semua Filter
    </a>
</div>
@endif