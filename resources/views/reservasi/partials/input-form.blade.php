<!-- Input Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Waktu Mulai</label>
                <div class="input-group">
                    <input type="datetime-local" class="form-control" id="jam_mulai" name="jam_mulai" value="{{ date('Y-m-d\TH:i') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Waktu Akhir</label>
                <div class="input-group">
                    <input type="datetime-local" class="form-control" id="jam_akhir" name="jam_akhir" value="{{ date('Y-m-d\TH:i') }}" required>
                </div>
            </div>
            <div class="col-md">
                <label class="form-label small fw-semibold text-muted">Keterangan</label>
                <input type="text" class="form-control" id="keterangan_global" name="keterangan_global">
            </div>
        </div>
    </div>
</div>