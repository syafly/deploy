// src/js/managers/WebSocketManager.js
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

        this._isRefreshing = false;
        this._rateLimitUntil = 0; // timestamp kapan rate limit selesai

        this.connect();
    }

    get state() {
        return this.connectionState;
    }

    on(eventName, callback) {
        if (!this.handlers[eventName]) this.handlers[eventName] = [];
        this.handlers[eventName].push(callback);
    }

    off(eventName, callback) {
        if (!this.handlers[eventName]) return;
        this.handlers[eventName] = this.handlers[eventName].filter(cb => cb !== callback);
    }

    emit(eventName, payload) {
        const callbacks = this.handlers[eventName];
        if (callbacks) callbacks.forEach(cb => cb(payload));
    }

    setState(newState) {
        if (this.connectionState === newState) return;
        this.connectionState = newState;
        this.emit(newState);
    }

    async connect(isReconnect = false) {
        if (this.reconnectTimeout) {
            clearTimeout(this.reconnectTimeout);
            this.reconnectTimeout = null;
        }

        // Jika sedang dalam masa rate limit, jangan konek
        if (this._rateLimitUntil > Date.now()) {
            const waitMs = this._rateLimitUntil - Date.now();
            console.log(`[WebSocket] rate limit active, waiting ${waitMs}ms before reconnect`);
            this.reconnectTimeout = setTimeout(() => {
                this.reconnectTimeout = null;
                this.connect(isReconnect);
            }, waitMs);
            return;
        }

        if (!isReconnect) {
            if (this.connectionState === 'CONNECTING') return;
            this.setState('CONNECTING');
        }

        const token = await tokenManager.ensureValidToken();
        if (!token) {
            console.error('No access token available');
            this.setState('UNAUTHORIZED');
            return;
        }

        try {
            this.ws = new WebSocket(`${this.url}?type=browser&token=${token}&product=absensi`);
            this.setupEventHandlers();
        } catch (error) {
            console.error('WebSocket connection error:', error);
            this.scheduleReconnect();
        }
    }

    setupEventHandlers() {
        this.ws.onopen = () => {
            this.reconnectAttempts = 0;
            this._rateLimitUntil = 0; // reset rate limit
            if (this.reconnectTimeout) {
                clearTimeout(this.reconnectTimeout);
                this.reconnectTimeout = null;
            }
            this._isRefreshing = false;
            this.setState('CONNECTED');
        };

        this.ws.onmessage = (event) => {
            this.handleMessage(event);
        };

        this.ws.onclose = (event) => {
            console.log('[WebSocket] closed - code:', event.code, 'reason:', event.reason);

            // Jika koneksi digantikan oleh tab lain (server mengirim 1000 dengan reason 'REPLACED')
            if (event.code === 1000 && event.reason === 'REPLACED') {
                console.log('[WebSocket] session replaced by another tab. Stopping reconnection.');
                this.setState('DISCONNECTED');
                // Opsional: beri notifikasi ke user
                this.emit('NOTIFICATION', { message: 'Sesi Anda digunakan di tab lain', type: 'warning' });
                return;
            }

            if (this._isRefreshing) return;
            if (event.code === 1008) {
                this.handleUnauthorized(event.reason);
            } else {
                if (this._rateLimitUntil > Date.now()) {
                this.scheduleReconnect();
                } else {
                this.scheduleReconnect();
                }
            }
        };

        this.ws.onerror = (error) => {
            if (this._isRefreshing) return;
            if (!this.reconnectTimeout && !(this._rateLimitUntil > Date.now())) {
                setTimeout(() => {
                    if (this.ws && this.ws.readyState === WebSocket.CLOSED) {
                        this.scheduleReconnect();
                    }
                }, 100);
            }
        };
    }

    handleMessage(event) {
        console.log(event)
        try {
            const response = JSON.parse(event.data);
            console.log(response)
            if (response.event === 'error') {
                this.handleServerError(response);
                return;
            }
            this.emit(response.event, response);
        } catch (error) {
            console.log(error)
            Notify.error(`Error parsing WebSocket message: ${error}`);
        }
    }

    handleServerError(error) {
        // Extract and format the message
        let message = error.message;
        if (Array.isArray(message)) {
            // If message is an array of validation errors, format them
            message = message.map(err => {
                if (err.constraints) {
                    return Object.values(err.constraints).join(', ');
                }
                return JSON.stringify(err);
            }).join('; ');
        } else if (typeof message !== 'string') {
            message = String(message);
        }

        switch (error.code) {
            case 'TOKEN_EXPIRED':
                this.handleTokenExpired();
                break;
            case 'RATE_LIMIT':
                const match = message?.match(/in (\d+) seconds/);
                const seconds = match ? parseInt(match[1], 10) : 60;
                this._rateLimitUntil = Date.now() + seconds * 1000;
                this.emit('NOTIFICATION', { message, type: 'error' });
                this.setState('RATE_LIMITED');
                break;
            default:
                this.emit('NOTIFICATION', { message: message || 'Unknown error', type: 'error' });
                console.error('Received unknown error event from WebSocket:', error);
                break;
        }
    }

    async handleTokenExpired() {
        if (this._isRefreshing) return;

        this._isRefreshing = true;
        this.setState('UNAUTHORIZED');

        try {
            const newToken = await tokenManager.refreshToken();

            if (!newToken) {
                this.askReload('Sesi habis. Silakan login ulang.');
                return;
            }

            // pastikan ws lama ditutup
            if (this.ws) {
                this.ws.close();
                this.ws = null;
            }

            this._isRefreshing = false;

            // reconnect dengan token baru
            this.connect(true);

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

    scheduleReconnect() {
        if (this.reconnectTimeout) return;
        if (this._isRefreshing) return;

        // Jika sedang rate limit, gunakan waktu yang ditentukan
        if (this._rateLimitUntil > Date.now()) {
            const waitMs = this._rateLimitUntil - Date.now();
            console.log(`[WebSocket] waiting ${waitMs}ms due to rate limit`);
            this.reconnectTimeout = setTimeout(() => {
                this.reconnectTimeout = null;
                this.connect(true);
            }, waitMs);
            return;
        }

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