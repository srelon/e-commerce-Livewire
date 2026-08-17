@props([
    'columns',
    'items',
    'editMethod' => 'openEdit',
    'emptyMessage',
])

<table class="w-full text-sm">
    <thead>
        <tr class="border-b border-base-300 text-left text-zinc-500">
            @foreach ($columns as $column)
                <th class="py-2 font-medium">{{ $column['label'] ?? '' }}</th>
            @endforeach
            <th class="py-2"></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($items as $row)
            <tr class="border-b border-base-300">
                @foreach ($columns as $column)
                    <td class="py-2">
                        @include("livewire.admin.partials.list-columns.{$column['type']}", ['row' => $row, 'column' => $column])
                    </td>
                @endforeach
                <td class="py-2 text-right">
                    <button type="button" class="btn btn-sm" title="Edit" x-on:click="$wire.{{ $editMethod }}({{ $row['id'] }})">
                        <x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />
                    </button>

                    @if ($this->hasAccess('edit'))
                        <button
                            type="button"
                            class="btn btn-sm"
                            title="Delete"
                            x-on:click="$wire.dispatchSelf('confirm-action', {
                                event: 'delete-{{ $this->managerEventKey }}',
                                params: { id: {{ $row['id'] }} },
                                message: {{ \Illuminate\Support\Js::from($this->deleteMessage($row['name'])) }},
                                heading: 'Confirm deletion',
                                label: 'Delete',
                                variant: 'danger',
                            })"
                        >
                            <x-heroicon-o-trash class="w-5 text-red-600 dark:text-red-400" />
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($columns) + 1 }}" class="py-4 text-center text-zinc-500">{{ $emptyMessage }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
