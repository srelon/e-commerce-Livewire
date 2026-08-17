<flux:navlist variant="outline" class="mt-4">
    @foreach ($items as $entry)
        @if (isset($entry['group']))
            @php
                $group_active = collect($entry['items'])->contains(fn ($item) => request()->routeIs($item['route_pattern']));
            @endphp
            <flux:sidebar.group :heading="$entry['group']" :icon="$entry['icon'] ?? null" expandable :expanded="$group_active" class="mt-4">
                @foreach ($entry['items'] as $item)
                    <flux:navlist.item :href="route($item['route'])" :current="request()->routeIs($item['route_pattern'])" wire:navigate>
                        {{ $item['label'] }}
                    </flux:navlist.item>
                @endforeach
            </flux:sidebar.group>
        @else
            <flux:navlist.item :href="route($entry['route'])" :icon="$entry['icon'] ?? null" :current="request()->routeIs($entry['route_pattern'])" wire:navigate>
                {{ $entry['label'] }}
            </flux:navlist.item>
        @endif
    @endforeach
</flux:navlist>
