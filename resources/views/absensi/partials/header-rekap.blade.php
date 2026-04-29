<div class="d-flex justify-content-between align-items-center">
    <div>
        <h6 class="mb-1 fw-semibold text-dark">Rekapitulasi Absensi</h6>
        <small class="text-muted">{{ $tanggalFilter }}</small>
    </div>
    
    @if($tampilkanTombolRekap)
        <button type="button" class="btn btn-danger-profesional px-4" id="btnRekapitulasiFinal">
            <i class="fas fa-check-double me-2"></i> Finalisasi Rekapitulasi
        </button>
    @elseif (!$tampilkanTombolRekap && $totalSiswaDifilter > 0)
        <div class="text-success d-flex align-items-center">
            <i class="fas fa-check-circle me-2 fa-lg"></i>
            <div>
                <div class="fw-semibold">Rekapitulasi Sudah Final</div>
                <small class="text-muted">{{ $totalSiswaDifilter }} siswa tercatat</small>
            </div>
        </div>
    @else
        <div class="text-info d-flex align-items-center">
            <i class="fas fa-info-circle me-2 fa-lg"></i>
            <div>
                <div class="fw-semibold">Tidak Ada Data</div>
                <small class="text-muted">Tidak ada siswa yang difilter</small>
            </div>
        </div>
    @endif

</div>