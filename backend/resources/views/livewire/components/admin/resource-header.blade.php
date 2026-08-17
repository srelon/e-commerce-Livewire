@props(['title', 'siteUrl' => null, 'compact' => false, 'deleteAction' => null, 'deleteMessage' => null])

<div class="flex items-center justify-between gap-4">
    <flux:heading size="{{ $compact ? 'lg' : 'xl' }}">{{ $title }}</flux:heading>

    @if (! $compact && ($siteUrl || $deleteAction))
        <div class="flex items-center gap-2">
            @if ($siteUrl)
                <flux:button variant="primary" color="blue" :href="$siteUrl" target="_blank" rel="noopener noreferrer">
                    View on site
                </flux:button>
            @endif

            @if ($deleteAction)
                @php
                    $deleteConfirmPayload = json_encode([
                        'event' => $deleteAction,
                        'message' => $deleteMessage ?? 'Are you sure you want to delete this?',
                        'heading' => 'Confirm deletion',
                        'label' => 'Delete',
                        'variant' => 'danger',
                    ]);
                @endphp

                <flux:button
                    variant="danger"
                    data-delete-confirm="{{ $deleteConfirmPayload }}"
                    x-on:click="$wire.dispatchSelf('confirm-action', JSON.parse($el.dataset.deleteConfirm))"
                >
                    Delete
                </flux:button>
            @endif
        </div>
    @endif
</div>
