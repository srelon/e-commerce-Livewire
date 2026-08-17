<flux:dropdown position="bottom" align="end">
    <flux:profile :name="$name" :avatar="$avatar" :initials="$initials" circle />

    <flux:menu>
        <flux:menu.item :href="route('admin.profile')" wire:navigate>Profile</flux:menu.item>

        <flux:menu.separator />

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <flux:menu.item as="button" type="submit">Log out</flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
