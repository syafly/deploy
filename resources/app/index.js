import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '../partials/navbar/index.js';
import '../partials/monitoring/index.js'
import '../partials/sidebar/index.js';
import wsManager from '../js/core/websocket-instance.js';
import { ApiService } from '../js/utils/ApiService.js';
import { tokenManager } from '../js/managers/TokenManager.js';

function isMobile() {
    return window.innerWidth <= 992;
}

let resizeTimer;

function handleOverlayForMobile() {

    const overlay = document.querySelector('.overlay');
    const monitorArea = document.getElementById('monitorArea');

    if (!overlay) return;

    if (isMobile()) {
        overlay.classList.remove('active');
    } else if (monitorArea && !monitorArea.classList.contains('hidden') && !isMobile()) {
        overlay.classList.add('active');
    }
}

async function initApp() {
    await ApiService.refreshCsrf();
    await tokenManager.ensureValidToken();

    wsManager.on('NOTIFICATION', (error) => {
        ApiService.showAlert(error.message)
    })
}

document.addEventListener('DOMContentLoaded', async () => {
    await initApp();
    handleOverlayForMobile();
});

window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(handleOverlayForMobile, 150);
});