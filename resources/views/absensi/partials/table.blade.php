@forelse($absensi_list as $data)
    <tr class="border-bottom">
        <td class="ps-4 ">
            <span class="text-muted small">{{ ($absensi_list->currentPage() - 1) * $absensi_list->perPage() + $loop->iteration }}</span>
        </td>
        <td class="">
            <div class="d-flex align-items-center">
                <div class="student-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                    <i class="fas fa-user text-white small"></i>
                </div>
                <div>
                    <div class="fw-medium text-dark">{{ $data->nama }}</div>
                    <small class="text-muted">{{ $data->kelas ?? '-' }}</small>
                </div>
            </div>
        </td>
        <td class="text-center text-muted">
            @if($data->masuk)
                <span>
                    {{ $data->masuk }}
                </span>
            @else
                <span>-</span>
            @endif
        </td>
        <td class="text-center">
            @if($data->istirahat)
                <span>
                    {{ $data->istirahat }}
                </span>
            @else
                <span>-</span>
            @endif
        </td>
        <td class="text-center">
            @if($data->kembali)
                <span>
                    {{ $data->kembali }}
                </span>
            @else
                <span>-</span>
            @endif
        </td>
        <td class="text-center">
            @if($data->pulang)
                <span>
                    {{ $data->pulang }}
                </span>
            @else
                <span>-</span>
            @endif
        </td>
        <td class="text-center">
            @if($data->status)
                <span class="">
                    {{ $data->status }}
                </span>
            @else
                <span>-</span>
            @endif
        </td>
        <td>
            @if($data->keterangan)
                <span class="keterangan-text">{!! $data->keterangan !!}</span>
            @else
                <span>-</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-5">
            <div class="empty-state">
                <i class="fas fa-clipboard-list fa-2x text-muted mb-3"></i>
                <h6 class="text-muted mb-2">Tidak ada data absensi</h6>
                <p class="text-muted small mb-0">Belum ada data siswa yang tercatat untuk tanggal ini.</p>
            </div>
        </td>
    </tr>
@endforelse
