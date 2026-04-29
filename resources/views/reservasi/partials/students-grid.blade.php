@if(isset($siswa) && $siswa->count() > 0)

    @foreach($siswa as $s)
    <div class="col-6 col-sm-6 col-xl-3 col-lg-4 col-md-6 mb-2 siswa-item">
        <div class="student-card p-3 rounded border cursor-pointer" data-siswa-id="{{ $s->id }}">
            <div class="d-flex align-items-center">
                <div class="student-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                    <i class="fas fa-user text-light"></i>
                </div>
                <div>
                    <div class="fw-medium text-dark small">{{ $s->nama }}</div>
                    <div class="text-muted x-small">{{ $s->kelas->nama_kelas ?? '-' }}</div>
                </div>
            </div>

            <div class="student-check">
                <i class="fas fa-check-circle text-success d-none"></i>
            </div>

            <input type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" class="siswa-checkbox" style="display:none;">
        </div>
    </div>
    @endforeach

@else

    {{-- hanya tampilkan empty state di first load --}}
    @if(!isset($isPartial) || !$isPartial)
    <div class="col-12">
        <div class="text-center py-5 text-muted">
            <i class="fas fa-users fa-2x mb-3"></i>
            <div class="small">Tidak ada data siswa</div>
        </div>
    </div>
    @endif

@endif