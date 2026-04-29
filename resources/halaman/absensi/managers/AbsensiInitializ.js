import { ApiService } from "../../../js/utils/ApiService";

export class AbsensiInitializ {
    constructor() {
        this.tableBody = document.querySelector('tbody');
        this.filterForm = document.getElementById('filterAbsensi');
        this.infoPaginationContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mt-3');
        this.isLoading = false;
        this.headerRekapContainer = document.querySelector('.card-header-rekap');
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.initializeHeaderEvents(); // Initialize events saat pertama load
    }

    setupEventListeners() {
        const searchInput = document.getElementById('searchAbsensi');
        const kelasSelect = document.getElementById('kelasAbsensi');
        const tanggalInput = document.getElementById('tanggalAbsensi');

        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(() => {
                this.loadData(1);
            }, 500));
        }

        if (kelasSelect) {
            kelasSelect.addEventListener('change', () => {
                this.loadData(1);
            });
        }

        if (tanggalInput) {
            tanggalInput.addEventListener('change', () => {
                this.loadData(1);
            });
        }

        // Event untuk pagination
        document.addEventListener('click', (e) => {
            if (e.target.closest('.paginationAbsensi a.page-link')) {
                e.preventDefault();
                const link = e.target.closest('a');
                const url = new URL(link.href);
                const page = url.searchParams.get('page') || 1;
                this.loadData(page);
            }
            
            if (e.target.closest('.cls-filter-absensi') || e.target.closest('.clear-filter-absensi')) {
                e.preventDefault();
                const link = e.target.closest('a');
                if (link.href === window.location.origin + '/absensi' || link.textContent.includes('Hapus Semua')) {
                    this.clearAllFilters();
                } else {
                    this.clearFilter(link.href);
                }
            }
        });

        if (this.filterForm) {
            this.filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.loadData(1);
            });
        }
    }

    async loadData(page = 1) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();

        try {
            const params = new URLSearchParams();
            const search = document.getElementById('searchAbsensi')?.value || '';
            const kelas = document.getElementById('kelasAbsensi')?.value || '';
            const tanggal = document.getElementById('tanggalAbsensi')?.value || '';

            if (search) params.set('search', search);
            if (kelas) params.set('kelas', kelas);
            if (tanggal) params.set('tanggal', tanggal);
            if (page > 1) params.set('page', page);

            const url = `${this.filterForm.action}?${params.toString()}`;
            
            const result = await ApiService.call(url);
            if (result.success) {
                this.updateTable(result.html);
                this.updateInfoPagination(result.infoPagination);
                this.updateActiveFilters(result.activeFilters);
                
                // Update header rekap jika ada data
                if (result.headerRekap) {
                    this.updateHeaderRekap(result.headerRekap);
                }
                
            } else {
                throw new Error(result.message || 'Invalid response from server');
            }
            
        } catch (error) {
            ApiService.showAlert(error.message || 'Gagal memuat data', 'error');
        } finally {
            this.isLoading = false;
        }
    }

    updateHeaderRekap(html) {
        if (this.headerRekapContainer && html) {
            this.headerRekapContainer.innerHTML = html;
            this.initializeHeaderEvents(); // Re-initialize events setelah update header
        }
    }

    initializeHeaderEvents() {
        // Handle tombol finalisasi rekap
        const btnFinalisasi = document.getElementById('btnRekapitulasiFinal');
        if (btnFinalisasi) {
            btnFinalisasi.addEventListener('click', () => {
                this.finalisasiRekap();
            });
        }
    }

    async finalisasiRekap() {
        if (!confirm('Apakah Anda yakin ingin melakukan finalisasi rekapitulasi? Tindakan ini tidak dapat dibatalkan.')) {
            return;
        }
        
        const btn = document.getElementById('btnRekapitulasiFinal');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        btn.disabled = true;

        try {
            const tanggal = document.getElementById('tanggalAbsensi')?.value || '';
            const kelas = document.getElementById('kelasAbsensi')?.value || '';
            const search = document.getElementById('searchAbsensi')?.value || ''; // Ambil nilai search

            // Validasi input
            if (!tanggal) {
                throw new Error('Tanggal harus diisi');
            }

            const result = await ApiService.call('/absensi/rekap', 
                'POST',{
                tanggal: tanggal,
                kelas: kelas,
                search: search // Kirim parameter search
            });
                

            if (result.success) {
                ApiService.showAlert(result.message || 'Rekapitulasi berhasil difinalisasi!', 'success');
                // Update header rekap dengan data terbaru dari response
                if (result.headerRekap) {
                    this.updateHeaderRekap(result.headerRekap);
                }
                
                // Reload data tabel untuk menampilkan data yang sudah direkap
                this.loadData(1);
                
            } else {
                throw new Error(result.message || 'Gagal melakukan finalisasi');
            }
        } catch (error) {
            ApiService.showAlert(error.message || 'Gagal melakukan finalisasi', 'error');
        } finally {
            // Reset tombol hanya jika tombol masih ada (tidak di-replace oleh header baru)
            const currentBtn = document.getElementById('btnRekapitulasiFinal');
            if (currentBtn) {
                currentBtn.innerHTML = originalText;
                currentBtn.disabled = false;
            }
        }
    }

    updateInfoPagination(html) {
        if (this.infoPaginationContainer && html) {
            this.infoPaginationContainer.innerHTML = html;
        }
    }

    updateTable(html) {
        if (this.tableBody && html) {
            this.tableBody.innerHTML = html;
            this.initializeTooltips();
        }
    }

    updateActiveFilters(html) {
        const container = document.querySelector('.active-filters-absensi');
        if (container) {
            container.innerHTML = html || '';
        }
    }

    clearFilter(url) {
        const urlObj = new URL(url, window.location.origin);
        const params = new URLSearchParams(urlObj.search);
        
        if (!params.has('search')) document.getElementById('searchAbsensi').value = '';
        if (!params.has('kelas')) document.getElementById('kelasAbsensi').value = '';
        if (!params.has('tanggal')) document.getElementById('tanggalAbsensi').value = '';
        
        this.loadData(1);
    }

    clearAllFilters() {
        document.getElementById('searchAbsensi').value = '';
        document.getElementById('kelasAbsensi').value = '';
        document.getElementById('tanggalAbsensi').value = '';
        this.loadData(1);
    }

    showLoading() {
        if (this.tableBody) {
            this.tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </td>
                </tr>
            `;
        }
    }

    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}