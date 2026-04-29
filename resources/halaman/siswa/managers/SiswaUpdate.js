export class SiswaUpdate {
    constructor() {
        this.init();
    }
    init() {
        this.initializeElements();
    }

    initializeElements() {
        try{
            this.scanStatus = document.getElementById("scanStatus");
            this.idCardInput = document.getElementById("id_card");
            
            this.defaultStatus = "Menunggu kartu...";
        }catch(errors){
            
        }
    }

    setDefaultState() {
        this.updateStatus(this.defaultStatus, 'fas fa-spinner fa-spin');
    }

    updateStatus(text, icon, textColor = '') {
        try {
            if (this.scanStatus) {
                this.scanStatus.innerHTML = `<i class="${icon} me-2 ${textColor}"></i> ${text}`;
            }
        } catch (error) {
            console.error("Error updating scan status UI:", error);
        }
    }

    handleRFIDScan(result) {
        if (!result.isSuccess) {
            this.updateStatus('Kartu sudah terdaftar!', 'fas fa-times-circle', 'text-danger');
            this.resetTimer = setTimeout(() => {
                this.resetScanner();
            }, 3000);
        } else {

            this.idCardInput.value = result.data.uid
            this.updateStatus('Kartu siap digunakan', 'fas fa-check-circle', 'text-success');
        }
    }

    resetScanner() {
        this.setDefaultState();
        this.resetTimer = null;
    }
}