import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine.js globally available
window.Alpine = Alpine;
Alpine.start();

// Lazy load Chart.js only when needed (dashboard page)
async function loadChartJS() {
    if (typeof window.Chart === 'undefined') {
        const { Chart, registerables } = await import('chart.js');
        Chart.register(...registerables);
        window.Chart = Chart;
        return Chart;
    }
    return window.Chart;
}

// Export the lazy loader for use in dashboard
window.loadChartJS = loadChartJS;

// Auto-load Chart.js if we're on the dashboard page
if (document.querySelector('#salesChart') || document.querySelector('#profitChart')) {
    loadChartJS().then(() => {
        // Charts will be initialized by inline scripts after Chart.js is loaded
        console.log('Chart.js loaded for dashboard');
    });
}
