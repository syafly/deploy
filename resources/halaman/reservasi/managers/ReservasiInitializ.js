import { ApiService } from "../../../js/utils/ApiService";

export class ReservasiInitializ {
    constructor() {
        this.siswaContainer = document.getElementById('siswaContainer');
        this.selectedCount = document.getElementById('selectedCount');
        this.submitCount = document.getElementById('submitCount');
        this.checkAllSiswa = document.getElementById('checkAllSiswa');
        this.isLoading = false;
        this.isLoadingMore = false;
        this.currentPage = 1;
        this.hasMore = true;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.bindStudentCards();
        this.updateSelectedCount();
        this.bindActivityCloseButtons();
        this.setupInfiniteScroll();
    }

    setupEventListeners() {
        const searchInput = document.getElementById('searchReservasi');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce(() => {
                this.resetPagination();
                this.loadData();
            }, 500));
        }

        const kelasFilter = document.getElementById('kelasReservasi');
        if (kelasFilter) {
            kelasFilter.addEventListener('change', () => {
                this.resetPagination();
                this.loadData();
            });
        }

        if (this.checkAllSiswa) {
            this.checkAllSiswa.addEventListener('change', (e) => {
                this.handleSelectAll(e.target.checked);
            });
        }

        const reservasiForm = document.getElementById('reservasiForm');
        if (reservasiForm) {
            reservasiForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        const clearSearch = document.getElementById('clearSearch');
        if (clearSearch) {
            clearSearch.addEventListener('click', () => {
                document.getElementById('searchReservasi').value = '';
                this.resetPagination();
                this.loadData();
            });
        }
    }

    setupInfiniteScroll() {
        if (!this.siswaContainer) return;

        // Scroll event pada siswaContainer, bukan window
        this.siswaContainer.addEventListener('scroll', () => {
            if (this.shouldLoadMore()) {
                this.loadMoreData();
            }
        });
    }

    shouldLoadMore() {
        if (this.isLoadingMore || !this.hasMore || !this.siswaContainer) return false;
        
        // Hitung berdasarkan scroll siswaContainer
        const { scrollTop, scrollHeight, clientHeight } = this.siswaContainer;
        return (scrollTop + clientHeight >= scrollHeight - 50); // 50px threshold
    }

    resetPagination() {
        this.currentPage = 1;
        this.hasMore = true;
        this.hideLoadMoreSpinner();
    }

    async loadData() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const result = await this.requestSiswaGrid(`/reservasi`)

            if (result.success) {
                this.updateSiswaGrid(result.html);
                this.hasMore = result.hasMore || false;
                this.updateLoadMoreUI();
            } else {
                throw new Error(result.message || 'Invalid response from server');
            }
        } catch (error) {
            ApiService.showAlert(error.message || 'Gagal memuat data', 'error');
        } finally {
            this.isLoading = false;
        }
    }

    async loadMoreData(partially = null) {
        if (this.isLoadingMore || !this.hasMore) return;
        
        this.isLoadingMore = true;
        this.currentPage++;
        this.showLoadMoreSpinner();
        
        try {
            const result = await this.requestSiswaGrid(`/reservasi/more-siswa`, partially)
            if (result.success) {
                this.appendSiswaGrid(result.html);
                this.hasMore = result.hasMore || false;
                this.updateLoadMoreUI();
            } else {
                this.hasMore = false;
            }
        } catch (error) {
            this.hasMore = false;
        } finally {
            this.isLoadingMore = false;
            this.hideLoadMoreSpinner();
        }
    }

    async requestSiswaGrid(url, partially = null) {  
        const params = new URLSearchParams();
        const search = document.getElementById('searchReservasi')?.value || '';
        const kelas = document.getElementById('kelasReservasi')?.value || '';
        
        if (search) params.set('search', search);
        if (kelas) params.set('kelas', kelas);
        params.set('page', this.currentPage);
        if (partially) params.set('partially', true);
        params.set('_ajax', 'true');

        const fullUrl = `${url}?${params.toString()}`;
        return await ApiService.call(fullUrl);
    }

    updateSiswaGrid(html) {
        if (this.siswaContainer && html) {
            this.siswaContainer.innerHTML = html;
            this.bindStudentCards();
            this.updateSelectedCount();
            this.updateCheckAllStatus();
        }
    }

    appendSiswaGrid(html) {
        if (this.siswaContainer && html) {
            this.siswaContainer.insertAdjacentHTML('beforeend', html);
            this.bindStudentCards(); // Re-bind untuk cards baru
        }
    }

    showLoadMoreSpinner() {
        let loadMoreContainer = document.getElementById('loadMoreContainer');
        if (!loadMoreContainer) {
            loadMoreContainer = document.createElement('div');
            loadMoreContainer.id = 'loadMoreContainer';
            loadMoreContainer.className = 'text-center mt-3';
            this.siswaContainer.appendChild(loadMoreContainer);
        }
        
        loadMoreContainer.innerHTML = `
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="text-muted ms-2">Memuat data lebih banyak...</span>
        `;
    }

    hideLoadMoreSpinner() {
        const loadMoreContainer = document.getElementById('loadMoreContainer');
        if (loadMoreContainer) {
            loadMoreContainer.remove();
        }
    }

    updateLoadMoreUI() {
        if (!this.hasMore && this.currentPage > 1) {
            const loadMoreContainer = document.createElement('div');
            loadMoreContainer.id = 'loadMoreContainer';
            loadMoreContainer.className = 'text-center mt-3';
            loadMoreContainer.innerHTML = '<p class="text-muted small">Semua data telah dimuat</p>';
            this.siswaContainer.appendChild(loadMoreContainer);
        }
    }

    bindStudentCards() {
        document.querySelectorAll('.student-card').forEach(card => {
            if (card.hasAttribute('data-listener-attached')) return;
            
            card.setAttribute('data-listener-attached', 'true');
            
            card.addEventListener('click', (e) => {
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'BUTTON') return;

                const checkbox = card.querySelector('.siswa-checkbox');
                checkbox.checked = !checkbox.checked;
                card.classList.toggle('selected', checkbox.checked);
                
                this.updateCheckAllStatus();
                this.updateSelectedCount();
                this.updateSubmitButton();
            });
        });
    }

    async handleSelectAll(isChecked) {
        await this.loadMoreData(true); // Pastikan semua data telah dimuat

        document.querySelectorAll('.siswa-checkbox').forEach(checkbox => {
            checkbox.checked = isChecked;
            const card = checkbox.closest('.student-card');
            if (card) {
                card.classList.toggle('selected', isChecked);
            }
        });
        this.updateSelectedCount();
        this.updateSubmitButton();
    }

    updateSelectedCount() {
        const selected = document.querySelectorAll('.siswa-checkbox:checked').length;
        if (this.selectedCount) {
            this.selectedCount.textContent = `${selected} siswa terpilih`;
        }
        if (this.submitCount) {
            this.submitCount.textContent = selected;
        }
    }

    updateSubmitButton() {
        const selected = document.querySelectorAll('.siswa-checkbox:checked').length;
        const submitSection = document.getElementById('submitSection');
        
        if (submitSection) {
            submitSection.style.display = selected > 0 ? 'block' : 'none';
        }
    }

    updateCheckAllStatus() {
        const checkboxes = document.querySelectorAll('.siswa-checkbox');
        if (this.checkAllSiswa && checkboxes.length > 0) {
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            this.checkAllSiswa.checked = checkedCount === checkboxes.length;
            this.checkAllSiswa.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const selectedCount = document.querySelectorAll('.siswa-checkbox:checked').length;
        if (selectedCount === 0) {
            ApiService.showAlert('Pilih minimal 1 siswa', 'error');
            return;
        }

        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        try {
            const formData = new FormData(e.target);
            const jsonData = {};
            
            for (let [key, value] of formData.entries()) {
                if (key === 'siswa_ids[]') {
                    if (!jsonData.siswa_ids) jsonData.siswa_ids = [];
                    jsonData.siswa_ids.push(value);
                } else {
                    jsonData[key] = value;
                }
            }

            const result = await ApiService.call(
                '/reservasi', 
                'POST',
                jsonData, 
                'Reservasi berhasil dibuat', 
                submitBtn
            );

            document.getElementById('keterangan_global').value = '';
            
            this.handleSelectAll(false);
            this.updateSubmitButton();
            this.updateRecentActivity(result.html); 
        } catch (error) {
            // Error sudah dihandle oleh ApiService
        }
    }

    updateRecentActivity(html) {
        const activityList = document.getElementById('activityList');
        if (activityList && html) {
            activityList.innerHTML = html;
            this.bindActivityCloseButtons();
            this.updateActivityCount();
        }
    }

    updateDataActivity(id) {
        const activityList = document.getElementById('activityList');

        const item = activityList.querySelector(`[data-reservasi-id="${id}"]`);

        if (item) {
            item.remove();
            this.updateActivityCount();
        }
    }


    showLoading() {
        if (this.siswaContainer) {
            this.siswaContainer.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data siswa...</p>
                    </div>
                </div>
            `;
        }
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

    // Activity methods tetap sama...
    bindActivityCloseButtons() {
        document.querySelectorAll('.activity-item .btn-close').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeActivityItem(button);
            });
        });
    }

    async removeActivityItem(button) {
        const activityItem = button.closest('.activity-item');
        if (!activityItem) return;

        const reservasiId = activityItem.dataset.reservasiId;
        if (!reservasiId) {
            return;
        }

        activityItem.style.opacity = '0';
        activityItem.style.transform = 'translateX(-10px)';
        activityItem.style.transition = 'all 0.2s ease';

        try {
            const response = await ApiService.call(
                `/reservasi/${reservasiId}`, 
                'DELETE'
            );

            if (response.success) {
                setTimeout(() => {
                    activityItem.remove();
                    this.updateActivityCount();
                    ApiService.showAlert('Reservasi berhasil dihapus', 'success');
                }, 200);
            } else {
                activityItem.style.opacity = '1';
                activityItem.style.transform = 'translateX(0)';
                throw new Error(response.message || 'Gagal menghapus reservasi');
            }
            
        } catch (error) {
            activityItem.style.opacity = '1';
            activityItem.style.transform = 'translateX(0)';
            console.error('Error:', error);
            ApiService.showAlert(error.message || 'Gagal menghapus reservasi', 'error');
        }
    }

    updateActivityCount() {
        const activityItems = document.querySelectorAll('.activity-item');
        const activityCount = activityItems.length;
        
        const counterElement = document.querySelector('.card-footer small');
        if (counterElement) {
            counterElement.textContent = `${activityCount} aktivitas hari ini`;
        }
        
        const activityList = document.getElementById('activityList');
        if (activityCount === 0 && activityList) {
            activityList.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-lg mb-2"></i>
                    <div class="small">Tidak ada aktivitas</div>
                </div>
            `;
        }
    }
}