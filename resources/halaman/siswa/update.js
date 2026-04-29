import { SiswaUpdate } from "./managers/SiswaUpdate";
import wsManager from "../../js/core/websocket-instance";


const updateManager = new SiswaUpdate();

wsManager.on('ready', () => {
    updateManager.updateStatus('Menunggu Kartu...', 'fas fa-spinner fa-spin');

    wsManager.send({
        event: 'change-mode',
        data: {
            mode:"update"
        }
    })
});

wsManager.on('result-scan', (data) => {
    updateManager.handleRFIDScan(data);
});

wsManager.on('DISCONNECTED', () => {
    updateManager.updateStatus('Koneksi Terputus!', 'fas fa-exclamation-circle', 'text-danger');
});

wsManager.on('UNAUTHORIZED', () => {
    updateManager.updateStatus('Akses Ditolak!', 'fas fa-times-circle', 'text-danger');
});

wsManager.on('CONNECTING', () => {
    updateManager.updateStatus('Menghubungkan...', 'fas fa-spinner fa-spin', 'text-warning');
});

wsManager.on('RECONNECTING', () => {
    updateManager.updateStatus('Menghubungkan kembali...', 'fas fa-spinner fa-spin', 'text-warning');
});