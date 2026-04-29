<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('absensi') }}" method="GET" id="filterAbsensi">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="searchAbsensi" class="form-label">Cari Siswa</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="searchAbsensi" 
                               class="form-control border-start-0" 
                               placeholder="Cari nama siswa atau ID..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="tanggalAbsensi" class="form-label fw-semibold text-muted small">Tanggal</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-calendar text-muted"></i>
                        </span>
                        <input type="date" name="tanggal" id="tanggalAbsensi" class="form-control border-start-0" value="{{ $tanggalFilter }}">
                    </div>
                </div>
                
                
                <!-- Kelas Filter -->
                <div class="col-md-4">
                    <label for="kelasAbsensi" class="form-label">Filter Kelas</label>
                    <select name="kelas" id="kelasAbsensi" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($listKelas as $kelas)
                            <option value="{{ $kelas->id }}" 
                                {{ request('kelas') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="active-filters-absensi mt-3">
                    @include('absensi.partials.active-filters')
                </div>
            </div>
        </form>
    </div>
</div>