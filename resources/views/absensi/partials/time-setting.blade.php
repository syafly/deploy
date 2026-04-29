<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 text-dark fw-semibold">
            <i class="fas fa-clock me-2 text-primary"></i>Pengaturan Waktu Absensi
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-center"> <!-- Gap diperkecil dari g-3 ke g-2 -->
            
            <!-- Masuk -->
            <div class="col-xl-3 col-lg-6">
                <div class="time-setting-card">
                    <div class="time-label" title="Masuk">
                        <i class="fas fa-sign-in-alt me-2 text-success"></i>
                        <span class="fw-semibold">Masuk</span>
                    </div>
                    <div class="time-inputs">
                        <input type="time" class="form-control time-input" id="masukFrom" 
                               value="{{ $waktuAbsen['masuk']->from ?? '07:00' }}">
                        <span class="time-separator">s/d</span>
                        <input type="time" class="form-control time-input" id="masukTo" 
                               value="{{ $waktuAbsen['masuk']->to ?? '07:15' }}">
                    </div>
                </div>
            </div>
            
            <!-- Istirahat -->
            <div class="col-xl-3 col-lg-6">
                <div class="time-setting-card">
                    <div class="time-label" title="Istirahat">
                        <i class="fas fa-coffee me-2 text-warning"></i>
                        <span class="fw-semibold">Istirahat</span>
                    </div>
                    <div class="time-inputs">
                        <input type="time" class="form-control time-input" id="istirahatFrom" 
                               value="{{ $waktuAbsen['istirahat']->from ?? '10:00' }}">
                        <span class="time-separator">s/d</span>
                        <input type="time" class="form-control time-input" id="istirahatTo" 
                               value="{{ $waktuAbsen['istirahat']->to ?? '10:15' }}">
                    </div>
                </div>
            </div>
            
            <!-- Kembali -->
            <div class="col-xl-3 col-lg-6">
                <div class="time-setting-card">
                    <div class="time-label" title="Kembali">
                        <i class="fas fa-redo me-2 text-info"></i>
                        <span class="fw-semibold">Kembali</span>
                    </div>
                    <div class="time-inputs">
                        <input type="time" class="form-control time-input" id="kembaliFrom" 
                               value="{{ $waktuAbsen['kembali_istirahat']->from ?? '10:15' }}">
                        <span class="time-separator">s/d</span>
                        <input type="time" class="form-control time-input" id="kembaliTo" 
                               value="{{ $waktuAbsen['kembali_istirahat']->to ?? '10:30' }}">
                    </div>
                </div>
            </div>
            
            <!-- Pulang -->
            <div class="col-xl-3 col-lg-6">
                <div class="time-setting-card">
                    <div class="time-label" title="Pulang">
                        <i class="fas fa-sign-out-alt me-2 text-danger"></i>
                        <span class="fw-semibold">Pulang</span>
                    </div>
                    <div class="time-inputs">
                        <input type="time" class="form-control time-input" id="pulangFrom" 
                               value="{{ $waktuAbsen['pulang']->from ?? '14:00' }}">
                        <span class="time-separator">s/d</span>
                        <input type="time" class="form-control time-input" id="pulangTo" 
                               value="{{ $waktuAbsen['pulang']->to ?? '14:30' }}">
                    </div>
                </div>
            </div>
            
            <!-- Save Button -->
            <div class="col-12 text-center mt-3">
                <button type="button" class="btn btn-primary-profesional px-4" id="simpanBtn">
                    <i class="fas fa-save me-2"></i>Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</div>