import wsManager from '../../js/core/websocket-instance';
import { updateDeviceCount, updateWebSocketStatus } from '../managers/SidebarUtils';

wsManager.on('CONNECTED', () => {
    updateWebSocketStatus('CONNECTED');
    updateDeviceCount()
});
wsManager.on('DISCONNECTED', () => {
    updateWebSocketStatus('DISCONNECTED');
});
wsManager.on('RECONNECTING', () => updateWebSocketStatus('RECONNECTING'));
wsManager.on('CONNECTING', () => updateWebSocketStatus('CONNECTING'));
wsManager.on('UNAUTHORIZED', () => {
    updateWebSocketStatus('UNAUTHORIZED');
});

// Event perubahan device
wsManager.on('device-snapshot', updateDeviceCount);
wsManager.on('device-connected', updateDeviceCount);
wsManager.on('device-disconnected', updateDeviceCount);