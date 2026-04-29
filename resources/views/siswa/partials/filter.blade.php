<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('siswa') }}" method="GET" id="filterSiswa">
            <div class="row g-3 align-items-end">
                <!-- Search Input -->
                <div class="col-md-4">
                    <label for="searchSiswa" class="form-label">Cari Siswa</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="searchSiswa" 
                               class="form-control border-start-0" 
                               placeholder="Cari nama siswa atau ID..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Kelas Filter -->
                <div class="col-md-4">
                    <label for="kelasSiswa" class="form-label">Filter Kelas</label>
                    <select name="kelas" id="kelasSiswa" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas_list as $kelas)
                            <option value="{{ $kelas->id }}" 
                                {{ request('kelas') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Active Filters Container -->
            <div class="active-filters-siswa mt-3">
                @include('siswa.partials.active-filters')
            </div>
        </form>
    </div>
</div>