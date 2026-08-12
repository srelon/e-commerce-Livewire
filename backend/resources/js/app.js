import '../../vendor/power-components/livewire-powergrid/dist/powergrid.js';
import Sort from '@alpinejs/sort';
import { Chart, registerables } from 'chart.js';
import menuTree from './menu-tree.js';

Chart.register(...registerables);
window.Chart = Chart;

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(Sort);
    window.Alpine.data('menuTree', menuTree);
});
