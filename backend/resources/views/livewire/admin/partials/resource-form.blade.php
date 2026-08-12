<div class="{{ $container_class ?? 'w-full' }}">
    <flux:heading size="xl">{{ $title }}</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-6">
        <x-admin.form-fields :fields="$fields" :disabled="$disabled" />

        @isset($extra)
            @include($extra, $extra_data ?? [])
        @endisset

        <div class="flex gap-3 pt-2">
            @unless ($disabled)
                <flux:button type="submit" variant="primary">Save</flux:button>
            @endunless

            <flux:button variant="ghost" :href="route($cancel_route)" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</div>
