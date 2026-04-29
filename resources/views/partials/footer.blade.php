<footer class="bg-light border-top py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h6 class="text-primary mb-3">Techno Kreatif Solusindo</h6>
                <p class="text-muted small mb-2">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Jl. Kelapa
                </p>
                <p class="text-muted small mb-2">
                    <i class="fas fa-phone me-2"></i>
                    (089xxxxxxxxxx)
                </p>
                <p class="text-muted small mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    info@klinikcomputer.id
                </p>
            </div>
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h6 class="text-primary mb-3">Quick Links</h6>
                <div class="d-flex flex-column">
                    <a href="{{ route('/') }}" class="text-muted small mb-2 text-decoration-none">Home</a>
                    <a href="{{ route('siswa') }}" class="text-muted small mb-2 text-decoration-none">Data Siswa</a>
                    <a href="{{ route('absensi') }}" class="text-muted small mb-2 text-decoration-none">Absensi</a>
                    <a href="{{ route('scan') }}" class="text-muted small mb-0 text-decoration-none">Scan Kartu</a>
                </div>
            </div>
            <div class="col-lg-4">
                <h6 class="text-primary mb-3">System Info</h6>
                <div class="d-flex align-items-center mb-2">
                    <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                    <span class="text-muted small">System Online</span>
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-code me-2"></i>
                    Developed with Laravel 10
                </p>
            </div>
        </div>
        <hr class="my-4">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted small mb-0">
                    &copy; 2025 Techno Kreatif Solusindo
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted small">Sistem Absensi RFID v1.0</span>
            </div>
        </div>
    </div>
</footer>