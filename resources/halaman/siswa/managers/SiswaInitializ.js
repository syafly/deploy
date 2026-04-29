import { ApiService } from "../../../js/utils/ApiService";
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

export class SiswaInitializ {
    constructor() {
        this.tableBody = document.querySelector('tbody');
        this.filterForm = document.getElementById('filterSiswa');
        this.infoPaginationContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mt-3'); // ⬅️ TAMBAHKAN
        this.isLoading = false;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        console.log('✅ SiswaInitializ initialized');
    }

    setupEventListeners() {
        // Cari input dan select
        const searchInput = document.getElementById('searchSiswa');
        const kelasSelect = document.getElementById('kelasSiswa');
        
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

        document.addEventListener('click', (e) => {
            // Handle pagination
            if (e.target.closest('.paginationSiswa a.page-link')) {
                e.preventDefault();
                const link = e.target.closest('a');
                const url = new URL(link.href);
                const page = url.searchParams.get('page') || 1;
                this.loadData(page);
            }
            
            // Event untuk hapus filter
            if (e.target.closest('.cls-filter-siswa') || e.target.closest('.clear-filter-siswa')) {
                e.preventDefault();
                const link = e.target.closest('a');
                if (link.href === window.location.origin + '/siswa' || link.textContent.includes('Hapus Semua')) {
                    this.clearAllFilters();
                } else {
                    this.clearFilter(link.href);
                }
            }
        });

        // Prevent form submission
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
            const search = document.getElementById('searchSiswa')?.value || '';
            const kelas = document.getElementById('kelasSiswa')?.value || '';

            if (search) params.set('search', search);
            if (kelas) params.set('kelas', kelas);
            if (page > 1) params.set('page', page);

            const url = `${this.filterForm.action}?${params.toString()}`;
            
            const result = await ApiService.call(url);
            
            if (result.success) {
                this.updateTable(result.html);
                this.updateInfoPagination(result.infoPagination); // ⬅️ UPDATE METHOD INI
                this.updateActiveFilters(result.activeFilters);
            } else {
                throw new Error(result.message || 'Invalid response from server');
            }
            
        } catch (error) {
            console.error('Error:', error);
            ApiService.showAlert(error.message || 'Gagal memuat data', 'error');
        } finally {
            this.isLoading = false;
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
            this.initializeTooltips(); // ⬅️ Jika perlu initialize tooltips
        }
    }

    updatePagination(html) {
        const paginationContainer = document.querySelector('.pagination');
        if (paginationContainer && html) {
            paginationContainer.innerHTML = html;
        }
    }

    updateInfo(html) {
        const infoContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mt-3');
        if (infoContainer && html) {
            infoContainer.innerHTML = html;
        }
    }

    updateActiveFilters(html) {
        const container = document.querySelector('.active-filters-siswa');
        if (container) {
            container.innerHTML = html || '';
        }
    }

    clearFilter(url) {
        const urlObj = new URL(url, window.location.origin);
        const params = new URLSearchParams(urlObj.search);
        
        // Reset form values yang tidak ada di URL
        if (!params.has('search')) document.getElementById('searchSiswa').value = '';
        if (!params.has('kelas')) document.getElementById('kelasSiswa').value = '';
        
        this.loadData(1);
    }

    clearAllFilters() {
        document.getElementById('searchSiswa').value = '';
        document.getElementById('kelasSiswa').value = '';
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
        // Initialize Bootstrap tooltips jika diperlukan
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