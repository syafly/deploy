// src/js/managers/DeviceManager.js
export class DeviceManager {
    constructor() {
        this.devices = this.loadFromStorage() || [];
        this.render();
    }

    // Simpan ke localStorage
    saveToStorage() {
        try {
            localStorage.setItem('deviceManager_devices', JSON.stringify(this.devices));
        } catch (e) {
            console.warn('Failed to save devices to localStorage', e);
        }
    }

    // Muat dari localStorage
    loadFromStorage() {
        try {
            const stored = localStorage.getItem('deviceManager_devices');
            return stored ? JSON.parse(stored) : null;
        } catch (e) {
            console.warn('Failed to load devices from localStorage', e);
            return null;
        }
    }

    // Hapus data (misal saat logout)
    clear() {
        this.devices = [];
        localStorage.removeItem('deviceManager_devices');
        this.render();
    }

    // Snapshot awal, ganti seluruh daftar
    setSnapshot(devices) {
        this.devices = devices.data.map(d => ({
            id: d.id,
            deviceId: d.deviceId,
            ip: d.ip || '0.0.0.0',
            state: d.state
        }));
        this.saveToStorage();
        this.render();
    }

    // Tambah atau perbarui device (untuk event device-connected, device-update, dll)
    addOrUpdateDevice(result) {
        const device = result.data
        const index = this.devices.findIndex(d => d.id == device.id);
        const newDevice = {
            id: device.id,
            deviceId: device.deviceId,
            ip: device.ip || '0.0.0.0',
            state: device.state
        };

        if (index >= 0) {
            // Update existing
            this.devices[index] = { ...this.devices[index], ...newDevice };
        } else {
            // Add new
            this.devices.push(newDevice);
        }
        this.saveToStorage();
        this.render();
    }

    // Perbarui state device (untuk device-suspend, device-disconnect, dll)
    updateDeviceState(result) {
        const device = result.data
        const dev = this.devices.find(d => d.id == device.id);
        if (dev) {
            dev.state = device.state || dev.state;
            this.saveToStorage();
            this.render();
        }
    }

    // Render ke UI
    render() {
        const monitorArea = document.getElementById('monitorArea');
        if (!monitorArea) return;
        const container = monitorArea.querySelector('.d-flex.flex-wrap.gap-2');
        const title = monitorArea.querySelector('.mb-2.fw-bold');
        if (!container || !title) return;

        container.innerHTML = '';

        if (this.devices.length > 0) {
            title.innerHTML = '<i class="bi bi-pc-display me-1"></i>Device yang tersedia:';
            this.devices.forEach(dev => {
                const el = document.createElement('div');
                el.className = 'device p-3 bg-white border rounded';
                el.dataset.deviceId = dev.deviceId;
                el.dataset.ip = dev.ip;
                // Tentukan warna badge berdasarkan state
                const badgeClass = dev.state === 'online' ? 'bg-success' :
                                   dev.state === 'suspend' ? 'bg-warning' :
                                   dev.state === 'offline' ? 'bg-secondary' : 'bg-info';
                el.innerHTML = `
                    <div class="d-flex align-items-center mb-1">
                        <p class="m-0 fw-bold">${dev.deviceId}</p>
                        <span class="ms-2 badge ${badgeClass}">${dev.state}</span>
                    </div>
                    <small class="text-muted"><i class="bi bi-dot me-1"></i>IP: ${dev.ip}</small>
                `;
                container.appendChild(el);
            });
        } else {
            title.innerHTML = '<i class="bi bi-pc-display me-1 text-muted"></i>Tidak ada device yang tersedia.';
        }
    }

    // Mendapatkan jumlah device aktif (tidak offline)
    getActiveCount() {
        return this.devices.filter(d => d.state !== 'OFFLINE').length;
    }
}

export const deviceManager = new DeviceManager();