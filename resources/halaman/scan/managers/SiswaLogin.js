export class SiswaLogin {
    constructor() {
        this.scannerArea = document.getElementById('scannerArea');
        this.scanStatus = document.getElementById('scanStatus');
        this.resetTimer = null;
        this.init();
    }

    init() {
        this.setDefaultState();
    }

    setDefaultState() {
        this.updateScannerArea(`
            <div class="scan-line"></div>
            <i class="fas fa-id-card rfid-tag"></i>
        `);
        this.updateStatus('Menunggu Kartu...', 'fas fa-spinner fa-spin', 'text-info');
    }

    handleRFIDScan(result) {
        const data = result.data
        // Clear existing reset timer
        if (this.resetTimer) {
            clearTimeout(this.resetTimer);
        }

        let scanContent = '';
        let statusMessage = '';
        let statusColor = 'text-success';
        let statusIcon = '';

        if (!result.isSuccess) {
            statusIcon = 'fas fa-times-circle';
            
            switch(result.error.error_code){
                case 'NOT_FOUND':
                    scanContent = `<i class="fas fa-user-secret rfid-tag"></i>`;
                    statusColor = 'text-danger';
                case 'SPAM':
                    scanContent = `<i class="fas fa-user-secret rfid-tag"></i>`;
                    statusColor = 'text-danger';
                case 'EXPIRED':
                    scanContent = `<i class="fas fa-exclamation-triangle rfid-tag"></i>`;
                    statusColor = 'text-warning';
            }

            statusMessage = result.error.message;
        } else {
            statusIcon = 'fas fa-check-circle';
            statusColor = 'text-success';

            if (data.keterangan.includes('terlambat')) {
                statusColor = 'text-warning';
                scanContent = `
                    <div class="scan-line"></div>
                    <div class="welcome-text ${statusColor}">
                        ${data.nama}<br>
                        <small>${data.keterangan}</small>
                    </div>
                `;
                statusMessage = 'Terlambat Tercatat';
            } else {
                scanContent = `
                    <div class="scan-line"></div>
                    <div class="welcome-text ${statusColor}">
                        ${data.nama}<br>
                        <small>Absen <b>${data.status}</b> berhasil</small>
                    </div>
                `;
                statusMessage = 'Scan Berhasil';
            }
        }

        // Update tampilan
        this.updateScannerArea(scanContent);
        this.updateStatus(statusMessage, statusIcon, statusColor);

        // Auto reset setelah 3 detik
        this.resetTimer = setTimeout(() => {
            this.resetScanner();
        }, 3000);
    }

    updateScannerArea(html) {
        try {
            if (this.scannerArea) {
                this.scannerArea.innerHTML = html;
            }
        } catch (error) {
            console.error('Error updating scanner area:', error);
            // Fallback
            if (this.scannerArea) {
                this.scannerArea.innerHTML = '<div class="text-danger">Error loading content</div>';
            }
        }
    }

    updateStatus(text, icon, textColor = '') {
        try {
            if (this.scanStatus) {
                this.scanStatus.innerHTML = `<i class="${icon} me-2 ${textColor}"></i> ${text}`;
            }
        } catch (error) {
            console.error('Error updating scan status:', error);
        }
    }

    resetScanner() {
        this.setDefaultState();
        this.resetTimer = null;
    }
}
