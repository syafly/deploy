export class ApiService {
    static async call(url, methode = 'GET', data = null, successMessage = null, button = null) {
        const config = {
            method: methode,
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        };

        if (methode !== 'GET') {
            config.headers['Content-Type'] = 'application/json';
            config.headers['X-XSRF-TOKEN'] = this.getCsrfToken();
        }

        if (data) {
            config.body = JSON.stringify(data);
        }

        let response;
        try {
            response = await fetch(url, config);
        } catch (err) {
            console.error('Network error:', err);
            this.showAlert('Koneksi error', 'error');
            throw err;
        }

        // CSRF expired handler
        if (response.status === 419) {
            await this.refreshCsrf();
            config.headers['X-XSRF-TOKEN'] = this.getCsrfToken();
            try {
                response = await fetch(url, config);
            } catch (err) {
                console.error('Network error after CSRF refresh:', err);
                this.showAlert('Koneksi error', 'error');
                throw err;
            }
        }

        const result = await response.json();

        // Jika ada button, tampilkan loading dan reset setelah selesai
        if (button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
            button.disabled = true;
            const start = Date.now();
            try {
                if (!response.ok) {
                    const message = result.message || 'Terjadi kesalahan';
                    this.showAlert(message, 'error');
                    throw new Error(message);
                }
                const successMsg = successMessage ?? result.message ?? 'Sukses';
                this.showAlert(successMsg, 'success');
                return result;
            } finally {
                const elapsed = Date.now() - start;
                const delay = Math.max(0, 300 - elapsed); // minimal 300ms
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, delay);
            }
        } else {
            // Tanpa button
            if (!response.ok) {
                const message = result.message || 'Terjadi kesalahan';
                this.showAlert(message, 'error');
                throw new Error(message);
            }
            // Jika ada successMessage, tampilkan (opsional)
            if (successMessage) {
                this.showAlert(successMessage, 'success');
            }
            return result;
        }
    }

    static showAlert(message, type) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        alertDiv.style.zIndex = '1060';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) alertDiv.remove();
        }, 5000);
    }

    static async refreshCsrf() {
        await fetch('/sanctum/csrf-cookie', {
            method: 'GET',
            credentials: 'include'
        });
    }

    static getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    static getCsrfToken() {
        const cookie = this.getCookie('XSRF-TOKEN');
        return cookie ? decodeURIComponent(cookie) : '';
    }
}