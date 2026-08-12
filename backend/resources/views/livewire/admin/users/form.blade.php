<div class="max-w-xl">
    <flux:heading size="xl">{{ $user ? 'Edit user' : 'New user' }}</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-4 rounded-lg border border-base-300 bg-base-200 p-6 shadow-sm">
        @if ($user?->avatar)
            @php $avatar_url = \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar); @endphp
            <x-lightbox-trigger :src="$avatar_url">
                <img src="{{ $avatar_url }}" alt="" class="h-16 w-16 rounded-full object-cover">
            </x-lightbox-trigger>
        @endif

        <div>
            <label class="mb-1.5 block text-sm font-medium text-zinc-800 dark:text-white">Avatar</label>
            <input type="file" wire:model="avatar" @disabled(! $this->hasAccess('edit')) class="block w-full text-sm text-zinc-600 dark:text-zinc-300 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm dark:file:bg-zinc-700">
            @error('avatar') <flux:text class="mt-1 text-red-500">{{ $message }}</flux:text> @enderror
        </div>

        <flux:field>
            <flux:label>Name</flux:label>
            <flux:input wire:model="name" :disabled="! $this->hasAccess('edit')" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Email</flux:label>
            <flux:input type="email" wire:model="email" :disabled="! $this->hasAccess('edit')" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>Password</flux:label>
            <flux:input type="password" wire:model="password" :disabled="! $this->hasAccess('edit')" placeholder="{{ $user ? 'Leave blank to keep current password' : '' }}" />
            <flux:error name="password" />
        </flux:field>

        <div class="flex gap-3 pt-2">
            @if ($this->hasAccess('edit'))
                <flux:button type="submit" variant="primary">Save</flux:button>
            @endif

            <flux:button variant="ghost" :href="route('admin.users.index')" wire:navigate>Cancel</flux:button>
        </div>
    </form>
</div>
