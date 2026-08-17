<div>
    <flux:heading size="xl">My Profile</flux:heading>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <form wire:submit="save" class="space-y-6">
                <x-admin.form-fields :fields="$this->schema()" :disabled="false" />

                <flux:button type="submit" variant="primary">Save</flux:button>
            </form>
        </div>

        <div class="rounded-lg border border-base-300 bg-base-200 p-4">
            <flux:heading size="lg">Moderator accounts</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500">Link a customer account to act through it when moderating reviews.</flux:text>

            <div class="mt-4 space-y-3">
                <flux:field class="relative">
                    <flux:label>Search by email or name</flux:label>
                    <flux:input wire:model.live.debounce.300ms="moderator_search" placeholder="user@example.com" autocomplete="off" />

                    @if (filled($moderator_results))
                        <div class="absolute z-10 mt-1 w-full rounded-lg border border-base-300 bg-base-200 shadow-lg">
                            @foreach ($moderator_results as $result)
                                <button
                                    type="button"
                                    wire:click="selectModeratorCandidate({{ $result['id'] }}, '{{ addslashes($result['name'].' ('.$result['email'].')') }}')"
                                    class="block w-full cursor-pointer px-3 py-2 text-left text-sm hover:bg-base-300"
                                >
                                    {{ $result['name'] }} ({{ $result['email'] }})
                                </button>
                            @endforeach
                        </div>
                    @endif
                </flux:field>

                @if ($found_user_id)
                    <flux:field>
                        <flux:label>Confirm {{ $found_user_label }}'s password</flux:label>
                        <flux:input type="password" wire:model="moderator_password" wire:keydown.enter="linkModerator" />
                    </flux:field>

                    <flux:button variant="primary" wire:click="linkModerator">Link account</flux:button>
                @endif

                @if ($moderator_error)
                    <flux:text class="text-red-500">{{ $moderator_error }}</flux:text>
                @endif
            </div>

            <div class="mt-4 space-y-2">
                @forelse ($this->moderators as $moderator)
                    <div class="flex items-center justify-between rounded-lg border border-base-300 bg-base-100 px-3 py-2">
                        <div class="text-sm">
                            <div class="font-medium">{{ $moderator->name }}</div>
                            <div class="text-zinc-500">{{ $moderator->email }}</div>
                        </div>

                        <button
                            type="button"
                            class="btn btn-sm"
                            title="Remove"
                            x-on:click="$wire.dispatch('confirm-action', {
                                event: 'unlinkModerator',
                                params: { userId: {{ $moderator->id }} },
                                message: 'Unlink this moderator account?',
                                heading: 'Confirm unlink',
                                label: 'Unlink',
                                variant: 'danger',
                            })"
                        >
                            <x-heroicon-o-trash class="w-5 text-red-600 dark:text-red-400" />
                        </button>
                    </div>
                @empty
                    <flux:text class="text-sm text-zinc-500">No moderators linked yet.</flux:text>
                @endforelse
            </div>
        </div>
    </div>

    @include('livewire.admin.partials.confirm-modal')
</div>
