// resources/js/utils/PollingManager.js
export class PollingManager {
    constructor(onDataCallback, options = {}) {
        this.onData = onDataCallback;
        this.pollInterval = options.pollInterval || 5000;
        this.maxErrors = options.maxErrors || 3;
        this.isPolling = false;
        this.errorCount = 0;
        this.#init();
    }

    #init() {
        this.isPolling = true;
        this.#startPolling();

        window.addEventListener('beforeunload', () => this.#stopPolling());
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.#stopPolling();
            } else {
                this.#startPolling();
            }
        });
    }

    async #startPolling() {
        if (!this.isPolling) {
            this.isPolling = true;
            await this.#pollData();
        }
    }

    #stopPolling() {
        this.isPolling = false;
    }

    async #pollData() {
        while (this.isPolling) {
            try {
                const response = await fetch('/api/dashboard-data');
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                this.onData(data); // panggil callback dengan data
                this.errorCount = 0;
                await this.#delay(this.pollInterval);
            } catch (error) {
                console.error('Polling error:', error);
                this.errorCount++;
                if (this.errorCount >= this.maxErrors) {
                    this.#stopPolling();
                    break;
                }
                await this.#delay(10000);
            }
        }
    }

    #delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}