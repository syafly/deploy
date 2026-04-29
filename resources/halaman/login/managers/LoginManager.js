import { tokenManager } from '../../../js/managers/TokenManager.js';
import { ApiService } from '../../../js/utils/ApiService.js';

export class LoginManager {
    constructor() {
        this.form = document.getElementById('loginForm');
        if (!this.form) return;
        this.errorMessage = document.getElementById('errorMessage');
        this.errorText = document.getElementById('errorText');
        this.submitBtn = document.getElementById('submitBtn');
        this.loginText = document.getElementById('loginText');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.init();
    }

    async init() {
        // Ambil CSRF cookie dari server sebelum interaksi apapun
        await this.ensureCsrfCookie();
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.setupPasswordToggle();
    }

    async ensureCsrfCookie() {
        try {
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'include'
            });
        } catch (err) {
            console.warn('Gagal mengambil CSRF cookie', err);
        }
    }

    setupPasswordToggle() {
        const toggle = document.getElementById('togglePassword');
        if (!toggle) return;
        toggle.addEventListener('click', () => {
            const password = document.getElementById('password');
            const icon = toggle.querySelector('i');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    async handleSubmit(e) {
        e.preventDefault();
        this.hideError();
        this.setLoading(true);

        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        try {
            // Gunakan ApiService
            const data = await ApiService.call('/login', 'POST', { username, password });

            // ApiService sudah melempar error jika response tidak ok
            if (!data.access_token) {
                throw new Error('Access token tidak ditemukan');
            }

            tokenManager.setAccessToken(data.access_token, data.expires_in);
            window.location.href = '/';
        } catch (err) {
            this.showError(err.message);
        } finally {
            this.setLoading(false);
        }
    }

    setLoading(loading) {
        this.submitBtn.disabled = loading;
        this.loginText.classList.toggle('d-none', loading);
        this.loadingSpinner.classList.toggle('d-none', !loading);
    }

    showError(message) {
        this.errorText.textContent = message;
        this.errorMessage.classList.remove('d-none');
    }

    hideError() {
        this.errorMessage.classList.add('d-none');
    }
}