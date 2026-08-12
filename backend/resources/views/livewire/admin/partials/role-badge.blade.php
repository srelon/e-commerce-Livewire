@if ($role)
    @if (auth('admins')->user()?->hasAccess('roles.view'))
        <a href="{{ route('admin.roles.edit', ['role' => $role->id]) }}" wire:navigate class="inline-flex">
            <flux:badge size="sm" class="hover:underline">{{ $role->label }}</flux:badge>
        </a>
    @else
        <flux:badge size="sm">{{ $role->label }}</flux:badge>
    @endif
@else
    &mdash;
@endif
