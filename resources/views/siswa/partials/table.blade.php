@forelse ($siswa_list as $siswa)
    <tr>
        <td class="ps-4 fw-semibold text-muted">{{ ($siswa_list->currentPage() - 1) * $siswa_list->perPage() + $loop->iteration }}</td>
        <td>
            <div class="d-flex align-items-center">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                     style="width: 40px; height: 40px;">
                    <span class="text-white fw-bold">{{ substr($siswa->nama, 0, 1) }}</span>
                </div>
                <div>
                    <div class="fw-bold text-dark">{{ $siswa->nama }}</div>
                    <small class="text-muted">ID: {{ $siswa->id_card ?? '-' }}</small>
                </div>
            </div>
        </td>
        <td>
            <span class="badge bg-secondary">{{ $siswa->kelas->nama_kelas }}</span>
        </td>
        <td class="text-center">
            <span class="badge bg-success  py-2 px-3 rounded-pill">{{ $siswa->hadir_count ?? 0 }}</span>
        </td>
        <td class="text-center">
            <span class="badge bg-danger py-2 px-3 rounded-pill">{{ $siswa->alpa_count ?? 0 }}</span>
        </td>
        <td class="text-center">
            <span class="badge bg-warning rounded-pill py-2 px-3">{{ $siswa->izin_count ?? 0 }}</span>
        </td>
        <td>
            <span class="text-muted">{{ $siswa->no_orangtua ?? '-' }}</span>
        </td>
        <td class="text-center">
            <div class="btn-group" role="group">
                @admin
                <a href="{{ route('siswa.edit', $siswa->id) }}" 
                   class="btn btn-outline-primary-profesional border-0 rounded-start" 
                   title="Edit" 
                   data-bs-toggle="tooltip">
                    <i class="fas fa-edit"></i>
                </a>
                @endadmin
                <form action="{{ route('siswa.delete', $siswa->id) }}" method="POST" class="d-inline">
                    @csrf 
                    @method('DELETE')
                    <button type="submit" 
                            class="btn btn-outline-danger-profesional border-0 rounded-end" 
                            title="Hapus" 
                            onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')"
                            data-bs-toggle="tooltip">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-5">
            <div class="py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-2 fs-5">Tidak ada data siswa</p>
                <p class="text-muted small">
                    @if(request()->hasAny(['search', 'kelas', 'status']))
                        Coba ubah filter pencarian Anda
                    @else
                        Klik tombol "Tambah Siswa" untuk menambahkan data siswa pertama
                    @endif
                </p>
            </div>
        </td>
    </tr>
@endforelse