// src/js/managers/WebSocketManager.js
import { deviceManager } from '../../partials/monitoring/index.js';
import { ApiService } from '../utils/ApiService.js';
import { updateStatusUI } from '../../partials/sidebar/index.js';
import { tokenManager } from './TokenManager.js';

export class WebSocketManager {
    constructor() {
        this.ws = null;
        this.url = document.querySelector('meta[name="websocket-url"]')?.getAttribute('content');

        this.reconnectInterval = 3000;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3;
        this.reconnectTimeout = null;
        this.isReconnectDialogShown = false;
        this.connectionState = 'DISCONNECTED';
        this.handlers = {};
        this.lastDeviceCount = 0;

        // Flag untuk mencegah refresh ganda
        this._isRefreshing = false;

        this.connect();
    }

    // ========== EVENT REGISTRY ==========
    on(eventName, callback) {
        if (!this.handlers[eventName]) this.handlers[eventName] = [];
        this.handlers[eventName].push(callback);
        this.callIfStateMatches(eventName, callback);
    }

    off(eventName, callback) {
        if (!this.handlers[eventName]) return;
        this.handlers[eventName] = this.handlers[eventName].filter(cb => cb !== callback);
    }

    emit(eventName, payload) {
        const callbacks = this.handlers[eventName];
        if (callbacks) callbacks.forEach(cb => cb(payload));
    }

    callIfStateMatches(eventName, callback) {
        const state = this.connectionState;
        if (eventName === 'CONNECTED' && state === 'CONNECTED') callback(this.lastDeviceCount);
        if (eventName === 'DISCONNECTED' && state === 'DISCONNECTED') callback();
        if (eventName === 'UNAUTHORIZED' && state === 'UNAUTHORIZED') callback();
        if (eventName === 'CONNECTING' && state === 'CONNECTING') callback();
        if (eventName === 'RECONNECTING' && state === 'RECONNECTING') callback();
    }

    // ========== STATE MANAGEMENT ==========
    setState(newState, deviceCount = undefined) {
        if (this.connectionState === newState) return;
        this.connectionState = newState;
        if (deviceCount !== undefined) this.lastDeviceCount = deviceCount;
        updateStatusUI(newState, deviceCount);
        this.emit(newState, deviceCount);
    }

    // ========== CONNECTION ==========
    connect(isReconnect = false) {
        if (this.reconnectTimeout) {
            clearTimeout(this.reconnectTimeout);
            this.reconnectTimeout = null;
        }

        if (!isReconnect) {
            if (this.connectionState === 'CONNECTING') return;
            this.setState('CONNECTING');
        }

        const token = tokenManager.getAccessToken();
        if (!token) {
            console.error('No access token available');
            this.setState('UNAUTHORIZED');
            return;
        }

        try {
            this.ws = new WebSocket(`${this.url}?type=browser&token=${token}`);
            this.setupEventHandlers();
        } catch (error) {
            console.error('WebSocket connection error:', error);
            this.scheduleReconnect();
        }
    }

    setupEventHandlers() {
        this.ws.onopen = () => {
            this.reconnectAttempts = 0;
            if (this.reconnectTimeout) {
                clearTimeout(this.reconnectTimeout);
                this.reconnectTimeout = null;
            }
            this._isRefreshing = false; // reset flag jika koneksi berhasil
            this.setState('CONNECTED');
        };

        this.ws.onmessage = (event) => {
            this.handleMessage(event);
        };

        this.ws.onclose = (event) => {
            console.log('[WebSocket] closed - code:', event.code, 'reason:', event.reason);
            // Jika sudah dalam proses refresh, jangan lakukan apa-apa (nanti akan connect ulang setelah refresh)
            if (this._isRefreshing) return;

            if (event.code === 1008) {
                this.handleUnauthorized(event.reason);
            } else {
                this.scheduleReconnect();
            }
        };

        this.ws.onerror = (error) => {
            console.error('[WebSocket] error:', error);
            if (this._isRefreshing) return; // jangan ganggu proses refresh
            if (!this.reconnectTimeout) {
                setTimeout(() => {
                    if (this.ws && this.ws.readyState === WebSocket.CLOSED) {
                        this.scheduleReconnect();
                    }
                }, 100);
            }
        };
    }

    // ========== MESSAGE HANDLING ==========
    handleMessage(event) {
        try {
            const response = JSON.parse(event.data);
            console.log('[WebSocket] message received:', response);

            if (response.event === 'error') {
                const error = response.error;
                console.error('[WebSocket] server error:', error);
                this.handleServerError(error);
                return;
            }

            switch (response.type) {
                case 'status_all':
                    this.setState('CONNECTED', response.devices || 0);
                    break;
                case 'rfid_scan':
                    this.emit('RFID_SCAN', response.data);
                    break;
                case 'device_status':
                    deviceManager.updateDevice({
                        id_device: response.id_device,
                        ip: response.ip,
                        status: response.status || 'connected'
                    });
                    break;
                case 'all_devices':
                    deviceManager.updateMonitor(response.devices || []);
                    break;
                case 'webhook_event':
                    ApiService.showAlert(response.message, response.message ? 'success' : 'error');
                    break;
                default:
                    console.log('Unknown message type:', response.type);
            }
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }

    handleServerError(error) {
        switch (error.code) {
            case 'TOKEN_EXPIRED':
                this.handleTokenExpired();
                break;
            case 'RATE_LIMIT':
                ApiService.showAlert(error.message || 'Too many requests, please wait.', 'error');
                // Jangan reconnect, biarkan server menutup koneksi
                break;
            default:
                ApiService.showAlert(error.message || 'Unknown error', 'error');
        }
    }

    async handleTokenExpired() {
        if (this._isRefreshing) return; // cegah double refresh
        this._isRefreshing = true;

        console.log('[WebSocket] token expired, attempting refresh...');
        if (this.reconnectTimeout) {
            clearTimeout(this.reconnectTimeout);
            this.reconnectTimeout = null;
        }
        this.setState('UNAUTHORIZED');

        try {
            const newToken = await tokenManager.refreshToken();
            console.log('[WebSocket] refresh result:', newToken ? 'success' : 'failed');
            if (newToken) {
                this._isRefreshing = false;
                this.connect(); // coba koneksi ulang dengan token baru
            } else {
                this._isRefreshing = false;
                this.askReload('Sesi habis. Silakan login ulang.');
            }
        } catch (error) {
            console.error('Refresh token error:', error);
            this._isRefreshing = false;
            this.askReload('Sesi habis. Silakan login ulang.');
        }
    }

    handleUnauthorized(reason) {
        console.log('[WebSocket] unauthorized close, reason:', reason);
        this.handleTokenExpired();
    }

    // ========== RECONNECT LOGIC ==========
    scheduleReconnect() {
        if (this.reconnectTimeout) return;
        if (this._isRefreshing) return; // jangan ganggu proses refresh

        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.setState('RECONNECTING');
            this.reconnectTimeout = setTimeout(() => {
                this.reconnectAttempts++;
                this.reconnectTimeout = null;
                this.connect(true);
            }, this.reconnectInterval);
        } else {
            this.setState('DISCONNECTED');
            console.error('Max reconnection attempts reached');
            this.askReload();
        }
    }

    askReload(message = 'Segarkan Halaman?') {
        if (!this.isReconnectDialogShown) {
            this.isReconnectDialogShown = true;
            if (confirm(message)) {
                location.reload();
            }
        }
    }

    send(message) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(message));
        } else {
            console.warn('WebSocket not connected, message not sent:', message);
        }
    }
}