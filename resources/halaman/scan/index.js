import wsManager from '../../js/core/websocket-instance';
import { SiswaLogin } from './managers/SiswaLogin';

const loginManager = new SiswaLogin();

wsManager.on('result-scan', (data) => {
    loginManager.handleRFIDScan(data);
});

wsManager.on('ready', () => {
    loginManager.updateStatus('Menunggu Kartu...', 'fas fa-spinner fa-spin');
        wsManager.send({
            event: 'change-mode',
            data: { mode: "login" }
        });
});

wsManager.on('DISCONNECTED', () => {
    loginManager.updateStatus('Koneksi Terputus!', 'fas fa-exclamation-circle', 'text-danger');
});

wsManager.on('UNAUTHORIZED', () => {
    loginManager.updateStatus('Akses Ditolak!', 'fas fa-times-circle', 'text-danger');
});

wsManager.on('CONNECTING', () => {
    loginManager.updateStatus('Menghubungkan...', 'fas fa-spinner fa-spin', 'text-warning');
});

wsManager.on('RECONNECTING', () => {
    loginManager.updateStatus('Menghubungkan kembali...', 'fas fa-spinner fa-spin', 'text-warning');
});