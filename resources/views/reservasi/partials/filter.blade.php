<!-- Filter Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="kelasReservasi" class="form-label small fw-semibold text-muted">Filter Kelas</label>
                <select name="kelas" id="kelasReservasi" class="form-select">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas_list as $kelas)
                        <option value="{{ $kelas->id }}" 
                            {{ request('kelas') == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Cari Siswa</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="searchReservasi" placeholder="Nama siswa...">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>