<div class="activity-list" style="max-height: 400px; overflow-y: auto;" id="activityList">
    @forelse($reservasi as $r)
        <div class="activity-item border-bottom p-3 position-relative" data-reservasi-id="{{ $r->id }}">
            <button class="btn-close btn-close-sm position-absolute" 
                    style="top: 12px; right: 12px;"
                    title="Hapus reservasi"></button>
            
            <div class="pe-4">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <div class="fw-medium text-dark small">{{ $r->siswa->nama }}</div>
                </div>
                <div class="text-muted x-small mb-1">
                    <small class="text-muted">{{ $r->waktu_mulai }}</small>
                    <small class="text-muted">{{ $r->waktu_akhir }}</small>
                </div>
                @if($r->keterangan)
                <div class="text-muted x-small mt-1 bg-light rounded px-2 py-1">
                    "{{ $r->keterangan }}"
                </div>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted">
            <i class="fas fa-inbox fa-lg mb-2"></i>
            <div class="small">Tidak ada aktivitas</div>
        </div>
    @endforelse
</div>