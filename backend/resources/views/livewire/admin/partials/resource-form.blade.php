<div class="{{ $container_class ?? 'w-full' }}">
    <x-admin.resource-header
        :title="$page_title"
        :site-url="$site_url ?? null"
        :compact="$compact_header ?? false"
        :delete-action="$delete_action ?? null"
        :delete-message="$delete_message ?? null"
    />

    <form wire:submit="save" class="mt-6 space-y-6">
        <x-admin.form-fields :fields="$fields" :disabled="$disabled" />

        @isset($extra)
            @include($extra, $extra_data ?? [])
        @endisset

        <div class="flex gap-3 pt-2">
            @unless ($disabled)
                <flux:button type="submit" variant="primary">Save</flux:button>
                <flux:button type="button" variant="filled" wire:click="saveAndExit">Save and exit</flux:button>
            @endunless

            <flux:button variant="ghost" :href="route($cancel_route)" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</div>
