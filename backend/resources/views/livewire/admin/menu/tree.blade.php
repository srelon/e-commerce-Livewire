@php
    $can_edit = $this->hasAccess('edit');
@endphp

<div
    x-data="menuTree(@js($this->tree))"
    x-on:menu-tree-updated.window="tree = $event.detail.tree; original = JSON.parse(JSON.stringify($event.detail.tree))"
>
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Menus</flux:heading>

        @if ($can_edit)
            <flux:button variant="primary" wire:click="openCreate">New menu item</flux:button>
        @endif
    </div>

    <flux:text class="mt-1 text-zinc-500">Drag items to reorder or nest them under a parent (max one level deep).</flux:text>

    <div class="mt-6">
        <ul x-sort.ghost="onDrop($item, $position, -1)" x-sort:group="menu-tree" x-sort:config="{ onMove: checkMove, forceFallback: true, onChoose: pauseObserving, onUnchoose: resumeObserving }" data-drop-zone="root" class="space-y-1">
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
        <div class="mt-6 flex gap-3 border-t border-base-300 pt-4" x-show="isDirty">
            <flux:button variant="primary" x-on:click="commitOrder">Save changes</flux:button>
            <flux:button variant="ghost" x-on:click="cancelOrder">Cancel</flux:button>
        </div>
    @endif

    <flux:modal wire:model.self="show_modal" name="menu-item-form" class="max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg">{{ $editing_id ? 'Edit menu item' : 'New menu item' }}</flux:heading>

            <flux:field>
                <flux:label>Name</flux:label>
                <flux:input wire:model="name" :disabled="! $can_edit" />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Type</flux:label>
                <flux:select wire:model.live="type" :disabled="! $can_edit">
                    <flux:select.option value="route">Internal route</flux:select.option>
                    <flux:select.option value="link">External link</flux:select.option>
                </flux:select>
            </flux:field>

            @if ($type === 'link')
                <flux:field>
                    <flux:label>URL</flux:label>
                    <flux:input wire:model="route" placeholder="https://..." :disabled="! $can_edit" />
                    <flux:error name="route" />
                </flux:field>
            @else
                <flux:field>
                    <flux:label>Route</flux:label>
                    <flux:select wire:model.live="route" :disabled="! $can_edit">
                        <flux:select.option value="">— Select a route —</flux:select.option>
                        @foreach ($this->getRouteOptions() as $key => $label)
                            <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="route" />
                </flux:field>

                @if (in_array($route, ['product', 'post']))
                    <flux:field class="relative">
                        <flux:label>Target</flux:label>
                        <flux:input wire:model.live.debounce.300ms="slug_search" placeholder="Search..." :disabled="! $can_edit" autocomplete="off" />

                        @if (filled($slug_results))
                            <div class="absolute z-10 mt-1 w-full rounded-lg border border-base-300 bg-base-200 shadow-lg">
                                @foreach ($slug_results as $result)
                                    <button
                                        type="button"
                                        wire:click="selectSlug('{{ $result['slug'] }}', '{{ addslashes($result['title']) }}')"
                                        class="block w-full px-3 py-2 text-left text-sm hover:bg-base-300"
                                    >
                                        {{ $result['title'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <flux:error name="params_slug" />
                    </flux:field>
                @endif
            @endif

            <flux:field>
                <flux:label>Location</flux:label>
                <flux:select wire:model="location" :disabled="! $can_edit">
                    <flux:select.option value="header">Header</flux:select.option>
                    <flux:select.option value="footer">Footer</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="flex justify-end gap-3 pt-2">
                <flux:button variant="ghost" wire:click="closeModal">Cancel</flux:button>

                @if ($can_edit)
                    <flux:button variant="primary" wire:click="save">Save</flux:button>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
