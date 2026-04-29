import { PollingManager } from '../../js/managers/PoolingManager';

function updateStats(stats) {
    const statCards = document.querySelectorAll('.stat-card');
    if (statCards.length >= 4) {
        const statNumbers = [
            stats.total || 0,
            stats.present || 0,
            stats.late || 0,
            stats.absent || 0
        ];
        statCards.forEach((card, index) => {
            const numberElement = card.querySelector('.stat-number');
            if (numberElement) {
                animateNumber(numberElement, statNumbers[index]);
            }
        });
    }
}

function animateNumber(element, newValue) {
    const currentValue = parseInt(element.textContent) || 0;
    if (currentValue === newValue) return;
    element.style.transform = 'scale(1.1)';
    element.textContent = newValue;
    setTimeout(() => element.style.transform = 'scale(1)', 300);
}

document.addEventListener('DOMContentLoaded', () => {
    new PollingManager(updateStats, { pollInterval: 5000 });
});