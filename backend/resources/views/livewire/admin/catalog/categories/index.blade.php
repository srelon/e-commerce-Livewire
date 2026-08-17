@php
    $can_edit = $this->hasAccess('edit');
@endphp

<div
    x-data="sortableTree(@js($this->tree))"
    x-on:categories-tree-updated.window="tree = $event.detail.tree; original = JSON.parse(JSON.stringify($event.detail.tree)); is_dirty = false"
    wire:ignore.self
>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Categories</flux:heading>

        @if ($can_edit)
            <flux:button variant="primary" wire:click="openCreate">New category</flux:button>
        @endif
    </div>

    <flux:text class="mt-1 text-zinc-500">Drag items to reorder.</flux:text>

    <div class="mt-6">
        <ul
            x-sort.ghost="onDrop($item, $position)"
            x-sort:config="{ forceFallback: true, onChoose: pauseObserving, onUnchoose: resumeObserving }"
            class="space-y-2"
        >
            <template x-for="node in tree" :key="node.id">
                <li x-sort:item="node.id">
                    @include('livewire.admin.catalog.categories.partials.category-row')
                </li>
            </template>
        </ul>

        <template x-if="tree.length === 0">
            <flux:text class="mt-4 text-zinc-500">No categories yet.</flux:text>
        </template>
    </div>

    @if ($can_edit)
        <div class="mt-6 flex gap-3 border-t border-base-300 pt-4" x-show="is_dirty">
            <flux:button variant="primary" x-on:click="commitOrder">Save changes</flux:button>
            <flux:button variant="ghost" x-on:click="cancelOrder">Cancel</flux:button>
        </div>
    @endif

    <x-admin.modal-form
        name="category-form"
        :title="$editing_id ? 'Edit category' : 'New category'"
        :fields="$this->schema()"
        :disabled="! $can_edit"
    />

    @include('livewire.admin.partials.confirm-modal')
</div>
