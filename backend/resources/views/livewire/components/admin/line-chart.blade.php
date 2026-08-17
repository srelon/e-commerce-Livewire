@props([
    'title',
    'label',
    'labels',
    'values',
    'color' => '#d97706',
])

<div
    class="rounded-lg border border-base-300 bg-base-200 p-4 shadow-sm"
    x-data="{
        is_visible: false,
        chart: null,
        toggle() {
            this.is_visible = ! this.is_visible
            if (this.is_visible) {
                this.$nextTick(() => {
                    if (! this.chart) {
                        this.chart = new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: {
                                labels: @js($labels),
                                datasets: [{
                                    label: @js($label),
                                    data: @js($values),
                                    borderColor: @js($color),
                                    backgroundColor: @js($color) + '1a',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                }],
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false },
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { stepSize: 1, precision: 0 },
                                    },
                                },
                            },
                        })
                    } else {
                        this.chart.resize()
                    }
                })
            }
        },
    }"
>
    <div class="flex items-center justify-between">
        <flux:text class="font-semibold text-zinc-800 dark:text-white">{{ $title }}</flux:text>
        <button type="button" x-on:click="toggle()" class="btn btn-sm btn-primary">
            <x-heroicon-o-presentation-chart-line class="h-4 w-4" />
            <span x-text="is_visible ? 'Hide Chart' : 'Show Chart'"></span>
        </button>
    </div>

    <div x-show="is_visible" class="mt-4">
        <canvas x-ref="canvas" style="max-height: 300px"></canvas>
    </div>
</div>
