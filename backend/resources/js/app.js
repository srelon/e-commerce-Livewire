import '../../vendor/power-components/livewire-powergrid/dist/powergrid.js';
import Sort from '@alpinejs/sort';
import { Chart, registerables } from 'chart.js';
import sortableTree from './sortable-tree.js';
import richTextEditor from './rich-text-editor.js';

Chart.register(...registerables);
window.Chart = Chart;

document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(Sort);
    window.Alpine.data('sortableTree', sortableTree);
    window.Alpine.data('richTextEditor', richTextEditor);
});
