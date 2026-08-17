@php
    $can_edit = $this->hasAccess('edit');
@endphp

<div
    x-data="sortableTree(@js($this->tree))"
    x-on:menu-tree-updated.window="tree = $event.detail.tree; original = JSON.parse(JSON.stringify($event.detail.tree)); is_dirty = false"
    wire:ignore.self
>
    <flux:heading size="xl">Menus</flux:heading>

    <flux:text class="mt-1 text-zinc-500">Drag items to reorder or nest them under a parent (max one level deep).</flux:text>

    <div class="mt-4 flex items-end justify-between">
        <flux:field class="max-w-xs">
            <flux:label>Location</flux:label>
            <flux:select wire:model.live="active_location">
                <flux:select.option value="header">Header</flux:select.option>
                <flux:select.option value="footer">Footer</flux:select.option>
            </flux:select>
        </flux:field>

        @if ($can_edit)
            <flux:button class="self-end" variant="primary" wire:click="openCreate">New menu item</flux:button>
        @endif
    </div>

    <div class="mt-6">
        <ul x-sort.ghost="onDrop($item, $position)" x-sort:group="menu-tree" x-sort:config="{ onMove: checkMove, forceFallback: true, onChoose: pauseObserving, onUnchoose: resumeObserving }" data-drop-zone="root" class="space-y-1">
            <template x-for="node in tree" :key="node.id">
                <li x-sort:item="node.id" :data-has-children="node.children.length > 0 ? '1' : '0'">
                    @include('livewire.admin.menu.partials.tree-row', ['var' => 'node', 'root' => true])

                    <ul
                        x-sort.ghost="onDrop($item, $position, node.id)"
                        x-sort:group="menu-tree"
                        x-sort:config="{ onMove: checkMove, forceFallback: true, onChoose: pauseObserving, onUnchoose: resumeObserving }"
                        data-drop-zone="children"
                        :class="node.children.length ? 'mt-1' : 'mt-0'"
                        class="ml-8 space-y-1 min-h-1"
                    >
                        <template x-for="node in node.children" :key="node.id">
                            <li x-sort:item="node.id" data-has-children="0">
                                @include('livewire.admin.menu.partials.tree-row', ['var' => 'node', 'root' => false])
                            </li>
                        </template>
                    </ul>
                </li>
            </template>
        </ul>

        <template x-if="tree.length === 0">
            <flux:text class="mt-4 text-zinc-500">No menu items yet.</flux:text>
        </template>
    </div>

    @if ($can_edit)
        <div class="mt-6 flex gap-3 border-t border-base-300 pt-4" x-show="is_dirty">
            <flux:button variant="primary" x-on:click="commitOrder">Save changes</flux:button>
            <flux:button variant="ghost" x-on:click="cancelOrder">Cancel</flux:button>
        </div>
    @endif

    <x-admin.modal-form
        name="menu-item-form"
        :title="$editing_id ? 'Edit menu item' : ($parent_id ? 'New child menu item' : 'New menu item')"
        :fields="$this->schema()"
        :disabled="! $can_edit"
    >
        @if ($type === 'route' && in_array($route, ['product', 'post']))
            <flux:field class="relative">
                <flux:label>Target</flux:label>
                <flux:input wire:model.live.debounce.300ms="slug_search" placeholder="Search..." :disabled="! $can_edit" autocomplete="off" />

                @if (filled($slug_results))
                    <div class="absolute z-10 mt-1 w-full rounded-lg border border-base-300 bg-base-200 shadow-lg">
                        @foreach ($slug_results as $result)
                            <button
                                type="button"
                                wire:click="selectSlug('{{ $result['slug'] }}', '{{ addslashes($result['title']) }}')"
                                class="block w-full cursor-pointer px-3 py-2 text-left text-sm hover:bg-base-300"
                            >
                                {{ $result['title'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
                <flux:error name="params_slug" />
            </flux:field>
        @endif
    </x-admin.modal-form>

    @include('livewire.admin.partials.confirm-modal')
</div>
