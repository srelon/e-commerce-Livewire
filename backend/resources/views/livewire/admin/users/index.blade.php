<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Users</flux:heading>

        @if ($this->hasAccess('edit'))
            <flux:button variant="primary" :href="route('admin.users.create')" wire:navigate>
                New user
            </flux:button>
        @endif
    </div>

    <div
        class="mt-6 rounded-lg border border-base-300 bg-base-200 p-4 shadow-sm"
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
                                    labels: @js($chart_data['labels']),
                                    datasets: [{
                                        label: 'Registrations',
                                        data: @js($chart_data['values']),
                                        borderColor: 'rgb(217, 119, 6)',
                                        backgroundColor: 'rgba(217, 119, 6, 0.1)',
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
            <flux:text class="font-semibold text-zinc-800 dark:text-white">User Registrations — last 30 days</flux:text>
            <flux:button x-on:click="toggle()" size="sm" variant="ghost">
                <span x-text="is_visible ? 'Hide Chart' : 'Show Chart'"></span>
            </flux:button>
        </div>

        <div x-show="is_visible" class="mt-4">
            <canvas x-ref="canvas" style="max-height: 300px"></canvas>
        </div>
    </div>

    <div class="mt-6">
        <livewire:admin.users.table />
    </div>

    @include('livewire.admin.partials.confirm-modal')
</div>
