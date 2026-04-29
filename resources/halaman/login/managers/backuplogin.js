import { tokenManager } from '../../../js/managers/TokenManager.js';

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

    init() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.setupPasswordToggle();
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
        const csrfToken = document.querySelector('input[name="_token"]').value;

        try {
            const response = await fetch('/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ username, password })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || data.error || 'Login gagal');
            }

            if (!data.access_token) {
                throw new Error('Access token tidak ditemukan');
            }

            // Simpan access token
            tokenManager.setAccessToken(
                data.access_token,
                data.expires_in
            );
            

            // Redirect ke dashboard
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
}b