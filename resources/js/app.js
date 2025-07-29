import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Chart = Chart; // make globally available for Blade


window.Alpine = Alpine;

Alpine.start();
