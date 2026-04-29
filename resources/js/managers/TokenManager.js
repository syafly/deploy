import { ApiService } from '../utils/ApiService.js';

class TokenManager {
    constructor() {
        this.refreshPromise = null;
        this.EXPIRY_BUFFER = 60;
    }

    getAccessToken() {
        return localStorage.getItem('access_token');
    }

    setAccessToken(token, expiresIn) {
        localStorage.setItem('access_token', token);

        if (expiresIn) {
            const expiresAt = Date.now() + expiresIn * 1000;
            localStorage.setItem('expires_at', expiresAt.toString());
        }
    }

    clearToken() {
        localStorage.removeItem('access_token');
        localStorage.removeItem('expires_at');
    }

    async refreshToken() {
        if (this.refreshPromise) return this.refreshPromise;

        this.refreshPromise = (async () => {
            try {
                const data = await ApiService.call('/refresh', 'POST');

                this.setAccessToken(data.access_token, data.expires_in);

                return data.access_token;
            } catch (err) {
                console.error('Refresh error:', err);

                this.clearToken();

                await ApiService.call('/logout', 'POST').catch(() => {});
                window.location.href = '/login';

                return null;
            } finally {
                this.refreshPromise = null;
            }
        })();

        return this.refreshPromise;
    }

    async ensureValidToken() {
        const token = this.getAccessToken();
        if (!token) return this.refreshToken();

        const expiresAt = localStorage.getItem('expires_at');

        if (!expiresAt || Date.now() > parseInt(expiresAt) - this.EXPIRY_BUFFER * 1000) {
            return this.refreshToken();
        }

        return token;
    }
}

export const tokenManager = new TokenManager();