import { SiswaRegister } from './managers/SiswaRegister';
import wsManager from '../../js/core/websocket-instance';

const registerManager = new SiswaRegister();

wsManager.on('result-scan', (data) => {
    registerManager.handleRFIDScan(data);
});

wsManager.on('ready', () => {
    registerManager.updateStatus('Menunggu Kartu...', 'fas fa-spinner fa-spin');

    wsManager.send({
        event: 'change-mode',
        data: {
            mode:"register"
        }
    })

});

wsManager.on('DISCONNECTED', () => {
    registerManager.updateStatus('Koneksi Terputus!', 'fas fa-exclamation-circle', 'text-danger');
});

wsManager.on('UNAUTHORIZED', () => {
    registerManager.updateStatus('Akses Ditolak!', 'fas fa-times-circle', 'text-danger');
});

wsManager.on('CONNECTING', () => {
    registerManager.updateStatus('Menghubungkan...', 'fas fa-spinner fa-spin', 'text-warning');
});

wsManager.on('RECONNECTING', () => {
    registerManager.updateStatus('Menghubungkan kembali...', 'fas fa-spinner fa-spin', 'text-warning');
});