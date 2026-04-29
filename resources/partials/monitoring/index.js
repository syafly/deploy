import wsManager from '../../js/core/websocket-instance';
import { deviceManager } from '../managers/DeviceManager';

wsManager.on('device-snapshot', (result) => {
    deviceManager.setSnapshot(result);
});
wsManager.on('device-connected', (result) => {
    deviceManager.addOrUpdateDevice(result);
});
wsManager.on('device-suspend', (result) => {
    deviceManager.updateDeviceState(result);
});
wsManager.on('device-disconnected', (result) => {
    deviceManager.updateDeviceState(result);
});

wsManager.on('device-reconnect', (result) => {
    deviceManager.addOrUpdateDevice(result);
});