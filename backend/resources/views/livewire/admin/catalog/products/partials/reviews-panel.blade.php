@php
    $can_edit = $this->hasAccess('edit');
@endphp

<div class="mt-2">
    <div class="rounded-lg border border-base-300 bg-base-200 p-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="shrink-0 text-sm font-medium text-zinc-500 whitespace-nowrap">Acting as:</span>

                @if ($this->moderators->isEmpty())
                    <flux:button size="sm" variant="primary" :href="route('admin.profile')" wire:navigate>
                        Add moderator
                    </flux:button>
                @else
                    <flux:select wire:model.live="selected_moderator_id" class="max-w-xs">
                        <flux:select.option value="">— Select —</flux:select.option>
                        @foreach ($this->moderators as $moderator)
                            <flux:select.option value="{{ $moderator->id }}">{{ $moderator->name }} ({{ $moderator->email }})</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
            </div>

            <flux:text class="text-sm text-zinc-500">{{ $this->reviews->total() }} reviews</flux:text>
        </div>

        <div class="mt-4 space-y-4">
            @forelse ($this->reviews as $review)
                @include('livewire.admin.catalog.products.partials.review-item', ['review' => $review, 'root' => true])
            @empty
                <flux:text class="text-zinc-500">No reviews yet.</flux:text>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ $this->reviews->links() }}
    </div>

    @include('livewire.admin.partials.confirm-modal')
</div>
