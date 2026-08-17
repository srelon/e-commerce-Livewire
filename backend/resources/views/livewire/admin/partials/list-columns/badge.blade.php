<flux:badge :color="$row[$column['key']] ? 'emerald' : 'zinc'">
    {{ $row[$column['key']] ? 'Active' : 'Inactive' }}
</flux:badge>
