import { deviceManager } from './DeviceManager';

export function updateWebSocketStatus(wsStat) {
    const el = document.getElementById('wsStatus');
    if (!el) return;
    
    const status = String(wsStat || 'OFFLINE').toUpperCase();
    el.textContent = status;
    let colorClass = 'text-danger';
    if (status === 'CONNECTED') {
        colorClass = 'text-success';
    } else if (status === 'RECONNECTING' || status === 'CONNECTING') {
        colorClass = 'text-warning';
    }
    el.className = `fw-bold ${colorClass}`;
}

export function updateDeviceCount() {
    const el = document.getElementById('deviceCount');
    if (!el) return;
    
    const count = deviceManager.getActiveCount();
    el.textContent = count !== undefined ? `${count} Active` : 'Loading...';
    
    if (el.classList) {
        el.classList.remove('text-success', 'text-danger');
        el.classList.add(count > 0 ? 'text-success' : 'text-danger');
    }
}