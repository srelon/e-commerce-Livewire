<div>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $page_title }}</flux:heading>

        @if (($create_route ?? null) && $this->hasAccess('edit'))
            <flux:button variant="primary" :href="route($create_route)" wire:navigate>
                {{ $create_label }}
            </flux:button>
        @endif
    </div>

    @isset($extra)
        <div class="mt-6">
            @include($extra, $extra_data ?? [])
        </div>
    @endisset

    <div class="mt-6">
        @livewire($table_component)
    </div>

    @include('livewire.admin.partials.confirm-modal')
</div>
