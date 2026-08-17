@if ($row[$column['key']])
    <img src="{{ $row[$column['key']] }}" alt="{{ $row['name'] }}" class="h-8 w-8 rounded-full object-cover">
@else
    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-base-300 text-zinc-400">
        <x-heroicon-o-user class="h-4 w-4" />
    </div>
@endif
