// resources/js/halaman/register/managers/SiswaRegister.js

import { ApiService } from '../../../js/utils/ApiService';

export class SiswaRegister {
    constructor() {
        this.isSubmitting = false;
        this.resetTimer = null;
        this.init();
    }

    init() {
        this.initializeElements();
        this.setupEventListeners();
        this.checkFormComplete();
    }

    initializeElements() {
        try {
            this.form = document.getElementById("siswaForm");
            this.namaInput = document.getElementById("nama");
            this.kelasInput = document.getElementById("kelas");
            this.idCardInput = document.getElementById("id_card");
            this.noOrtuInput = document.getElementById("no_ortu");
            this.submitButton = document.getElementById("submitButton");
            this.scanStatus = document.getElementById("scanStatus");

            this.storeUrl = this.form ? this.form.getAttribute('data-store-url') : '/siswa';
            this.defaultStatus = "Menunggu kartu...";
        } catch (error) {
            console.error('Initialize elements error:', error);
        }
    }

    setupEventListeners() {
        if (this.form && !this.form.hasAttribute('data-listener-added')) {
            this.form.addEventListener("submit", (e) => this.handleSubmit(e));
            this.form.setAttribute('data-listener-added', 'true');
        }

        const inputs = [this.namaInput, this.kelasInput, this.noOrtuInput, this.idCardInput];
        inputs.forEach(input => {
            if (input) {
                input.addEventListener("input", () => this.checkFormComplete());
            }
        });
    }

    setDefaultState() {
        this.updateStatus(this.defaultStatus, 'fas fa-spinner fa-spin');
    }

    handleRFIDScan(result) {
        // Bersihkan timer sebelumnya jika ada
        if (this.resetTimer) {
            clearTimeout(this.resetTimer);
            this.resetTimer = null;
        }
        if (!result.isSuccess) {
            this.updateStatus('Kartu sudah terdaftar!', 'fas fa-times-circle', 'text-danger');
            this.resetTimer = setTimeout(() => {
                this.resetScanner();
            }, 3000);
        } else {
            this.idCardInput.value = result.data.uid;
            this.updateStatus('Kartu siap digunakan', 'fas fa-check-circle', 'text-success');
            this.checkFormComplete();
        }
    }

    async handleSubmit(e) {
        e.preventDefault();

        if (this.isSubmitting) return;

        this.isSubmitting = true;
        this.submitButton.disabled = true;
        const originalText = this.submitButton.innerHTML;

        if (!this.validateForm()) {
            this.unlockForm(originalText);
            return;
        }

        const formData = {
            nama: this.namaInput.value.trim(),
            kelas: this.kelasInput.value,
            no_ortu: this.noOrtuInput.value.trim(),
            id_card: this.idCardInput.value.trim()
        };

        try {
            await ApiService.call(
                this.storeUrl,
                'POST',
                formData,
                'Data siswa berhasil disimpan!',
                this.submitButton
            );

            this.showSuccess(originalText);
        } catch (error) {
            this.unlockForm(originalText);
        }
    }

    showSuccess(originalText) {
        this.resetScanner();
        this.unlockForm(originalText);
        this.resetForm();
    }

    unlockForm(originalText) {
        this.isSubmitting = false;
        this.submitButton.disabled = false;
        this.submitButton.innerHTML = originalText;
    }

    validateForm() {
        const errors = [];

        if (!this.namaInput.value.trim()) {
            errors.push('Nama siswa harus diisi');
        }
        if (!this.kelasInput.value) {
            errors.push('Kelas harus dipilih');
        }
        if (!this.noOrtuInput.value.trim()) {
            errors.push('Nomor HP orang tua harus diisi');
        }
        if (!this.idCardInput.value.trim()) {
            errors.push('ID Kartu harus dipindai');
        }

        if (errors.length > 0) {
            ApiService.showAlert(errors.join('<br>'), 'error');
            return false;
        }
        return true;
    }

    checkFormComplete() {
        if (!this.submitButton) return;

        const isFormValid = (
            this.getValue(this.namaInput) &&
            this.getValue(this.kelasInput) &&
            this.getValue(this.noOrtuInput) &&
            this.getValue(this.idCardInput)
        );

        this.submitButton.disabled = !isFormValid;
    }

    getValue(inputElement) {
        return inputElement ? inputElement.value.trim() : '';
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

    resetForm() {
        if (this.namaInput) this.namaInput.value = '';
        if (this.kelasInput) this.kelasInput.value = '';
        if (this.noOrtuInput) this.noOrtuInput.value = '';
        if (this.idCardInput) this.idCardInput.value = '';
        this.checkFormComplete();
    }

    resetScanner() {
        this.setDefaultState();
        if (this.resetTimer) {
            clearTimeout(this.resetTimer);
            this.resetTimer = null;
        }
    }
}