import { ApiService } from "../../../js/utils/ApiService";

export class TimeSettingsManager {
    constructor() {
        if (document.getElementById('simpanBtn')) {
            this.originalValues = {};
            this.init();
        }
    }

    init() {
        this.simpanBtn = document.getElementById('simpanBtn');
        this.timeInputs = document.querySelectorAll('.time-input');
        this.setupEventListeners();
        this.storeOriginalValues();
    }

    storeOriginalValues() {
        this.timeInputs.forEach(input => {
            this.originalValues[input.id] = input.value;
        });
    }

    setupEventListeners() {
        this.timeInputs.forEach(input => {
            input.addEventListener('change', (e) => this.formatTimeInput(e.target));
            input.addEventListener('input', (e) => this.trackChanges(e.target));
        });

        if (this.simpanBtn) {
            this.simpanBtn.addEventListener('click', () => this.saveTimeSettings());
        }
    }

    trackChanges(input) {
        // Beri visual feedback saat user mengubah nilai
        if (input.value !== this.originalValues[input.id]) {
            input.classList.add('border-warning', 'bg-warning', 'bg-opacity-10');
        } else {
            input.classList.remove('border-warning', 'bg-warning', 'bg-opacity-10');
        }
    }

    formatTimeInput(input) {
        const time = input.value;
        if (time && !time.match(/^\d{2}:\d{2}$/)) {
            const [hours, minutes] = time.split(':');
            input.value = `${hours.padStart(2, '0')}:${minutes.padStart(2, '0')}`;
        }
    }

    getTimeData() {
        return {
            masuk: {
                from: document.getElementById('masukFrom')?.value,
                to: document.getElementById('masukTo')?.value
            },
            istirahat: {
                from: document.getElementById('istirahatFrom')?.value,
                to: document.getElementById('istirahatTo')?.value
            },
            kembali_istirahat: {
                from: document.getElementById('kembaliFrom')?.value,
                to: document.getElementById('kembaliTo')?.value
            },
            pulang: {
                from: document.getElementById('pulangFrom')?.value,
                to: document.getElementById('pulangTo')?.value
            }
        };
    }

    getChangedInputs() {
        const changedInputs = [];
        this.timeInputs.forEach(input => {
            if (input.value !== this.originalValues[input.id]) {
                changedInputs.push(input);
            }
        });
        return changedInputs;
    }

    validateTimeRanges(data) {
        const errors = [];
        const ranges = [
            { name: 'Masuk', from: data.masuk.from, to: data.masuk.to },
            { name: 'Istirahat', from: data.istirahat.from, to: data.istirahat.to },
            { name: 'Kembali Istirahat', from: data.kembali_istirahat.from, to: data.kembali_istirahat.to },
            { name: 'Pulang', from: data.pulang.from, to: data.pulang.to }
        ];

        ranges.forEach(range => {
            if (range.from && range.to && range.from >= range.to) {
                errors.push(`${range.name}: "Dari" harus sebelum "Sampai"`);
            }
        });

        if (data.masuk.to > data.istirahat.from) {
            errors.push('Waktu Masuk harus selesai sebelum Istirahat dimulai');
        }
        
        if (data.istirahat.to > data.kembali_istirahat.from) {
            errors.push('Waktu Istirahat harus selesai sebelum Kembali Istirahat dimulai');
        }
        
        if (data.kembali_istirahat.to > data.pulang.from) {
            errors.push('Waktu Kembali Istirahat harus selesai sebelum Pulang dimulai');
        }
        
        if (errors.length > 0) {
            ApiService.showAlert(errors.join('<br>'), 'error');
            return false;
        }
        
        return true;
    }

    async saveTimeSettings() {
        const data = this.getTimeData();
        
        if (!this.validateTimeRanges(data)) {
            return;
        }

        const changedInputs = this.getChangedInputs();
        if (changedInputs.length === 0) {
            ApiService.showAlert('Tidak ada perubahan yang disimpan', 'info');
            return;
        }

        try {
            const waktuSettings = [
                { status: 'masuk', from: data.masuk.from, to: data.masuk.to },
                { status: 'istirahat', from: data.istirahat.from, to: data.istirahat.to },
                { status: 'kembali_istirahat', from: data.kembali_istirahat.from, to: data.kembali_istirahat.to },
                { status: 'pulang', from: data.pulang.from, to: data.pulang.to }
            ];

            await ApiService.call(
                '/api/pengaturan-waktu', 
                'POST',
                { waktu_settings: waktuSettings },
                'Pengaturan waktu berhasil disimpan!', 
                this.simpanBtn
            );

            this.updateUIWithNewData(changedInputs);
            this.storeOriginalValues();

        } catch (error) {
        }
    }

    updateUIWithNewData(changedInputs) {
        changedInputs.forEach(input => {
            input.classList.remove('border-warning', 'bg-warning', 'bg-opacity-10');
            input.classList.add('border-success', 'bg-success', 'bg-opacity-10');
            
            setTimeout(() => {
                input.classList.remove('border-success', 'bg-success', 'bg-opacity-10');
            }, 2000);
        });
    }
}