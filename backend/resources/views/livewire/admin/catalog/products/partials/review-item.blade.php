@php
    $is_replying_here = $root
        ? ($replying_to_id === $review['id'] && ! $replying_to_reply_id)
        : ($replying_to_reply_id === $review['id']);
    $replies_count = count($review['replies'] ?? []);
    $liked = ($review['my_reaction'] ?? null) === 'like';
    $disliked = ($review['my_reaction'] ?? null) === 'dislike';
@endphp

<div
    class="rounded-lg border border-base-300 p-4 {{ $root ? 'bg-base-100' : 'ml-8 mt-3 bg-[color-mix(in_oklab,var(--color-white)_7%,transparent)]' }}"
    @if ($root)
        x-data="{ show_replies: false }"
        x-on:review-reply-posted.window="if ($event.detail.reviewId === {{ $review['id'] }}) show_replies = true"
    @endif
>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span class="font-medium">{{ $review['user']['name'] ?? 'Deleted user' }}</span>

                @if ($review['user']['is_moderator'] ?? false)
                    <flux:badge color="emerald" size="sm">Moderator</flux:badge>
                @endif

                @if ($root && $review['rating'])
                    <span class="text-sm text-amber-500">{{ str_repeat('★', $review['rating']).str_repeat('☆', 5 - $review['rating']) }}</span>
                @endif

                <span class="text-xs text-zinc-400">{{ \Illuminate\Support\Carbon::parse($review['created_at'])->diffForHumans() }}</span>
            </div>

            @if ($review['replied_to'])
                <div class="mt-1 text-xs text-zinc-500">Replying to {{ $review['replied_to']['user']['name'] ?? 'deleted user' }}</div>
            @endif

            @if ($editing_id === $review['id'])
                <div class="mt-2 space-y-2">
                    @include('livewire.admin.partials.fields.richtext', [
                        'field' => [
                            'name' => 'editing_body',
                            'value' => $editing_body,
                            'placeholder' => 'Edit review...',
                            'buttons' => ['undo', 'redo', 'bold', 'italic', 'underline', 'strike'],
                            'emoji' => true,
                            'min_height' => 'min-h-16',
                        ],
                        'disabled' => false,
                    ])
                    <div class="flex gap-2">
                        <flux:button size="sm" variant="primary" wire:click="submitEdit">Save</flux:button>
                        <flux:button size="sm" variant="ghost" wire:click="cancelEdit">Cancel</flux:button>
                    </div>
                </div>
            @else
                <div class="mt-1 text-sm">{!! $review['body'] !!}</div>
            @endif
        </div>

        @if ($can_edit)
            <div class="flex shrink-0 items-center gap-1">
                <button type="button" class="btn btn-sm" title="Edit" wire:click="startEdit({{ $review['id'] }}, @js($review['body']))">
                    <x-heroicon-o-pencil-square class="w-5 text-blue-600 dark:text-blue-400" />
                </button>

                <button
                    type="button"
                    class="btn btn-sm"
                    title="Delete"
                    x-on:click="$wire.dispatchSelf('confirm-action', {
                        event: 'deleteReview',
                        params: { reviewId: {{ $review['id'] }} },
                        message: 'Delete this review?',
                        heading: 'Confirm deletion',
                        label: 'Delete',
                        variant: 'danger',
                    })"
                >
                    <x-heroicon-o-trash class="w-5 text-red-600 dark:text-red-400" />
                </button>
            </div>
        @endif
    </div>

    <div class="mt-3 flex items-center gap-4 text-sm">
        <button
            type="button"
            wire:click="like({{ $review['id'] }}, 'like')"
            class="flex cursor-pointer items-center gap-1 rounded-full px-2 py-0.5 {{ $liked ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'text-zinc-500 hover:text-emerald-600' }}"
        >
            <x-heroicon-o-hand-thumb-up class="w-4" /> {{ $review['likes_count'] }}
        </button>

        <button
            type="button"
            wire:click="like({{ $review['id'] }}, 'dislike')"
            class="flex cursor-pointer items-center gap-1 rounded-full px-2 py-0.5 {{ $disliked ? 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400' : 'text-zinc-500 hover:text-red-600' }}"
        >
            <x-heroicon-o-hand-thumb-down class="w-4" /> {{ $review['dislikes_count'] }}
        </button>

        @if ($root)
            <button type="button" wire:click="startReply({{ $review['id'] }})" class="cursor-pointer text-zinc-500 hover:text-amber-600">Reply</button>
        @else
            <button type="button" wire:click="startReply({{ $review['parent_id'] }}, {{ $review['id'] }})" class="cursor-pointer text-zinc-500 hover:text-amber-600">Reply</button>
        @endif
    </div>

    @if ($is_replying_here)
        <div class="mt-3 space-y-2">
            @include('livewire.admin.partials.fields.richtext', [
                'field' => [
                    'name' => 'reply_body',
                    'value' => $reply_body,
                    'placeholder' => 'Write a reply as '.$this->selectedModerator?->name.'...',
                    'buttons' => ['undo', 'redo', 'bold', 'italic', 'underline', 'strike'],
                    'emoji' => true,
                    'min_height' => 'min-h-16',
                ],
                'disabled' => false,
            ])
            <div class="flex gap-2">
                <flux:button size="sm" variant="primary" wire:click="submitReply">Post reply</flux:button>
                <flux:button size="sm" variant="ghost" wire:click="cancelReply">Cancel</flux:button>
            </div>
        </div>
    @endif

    @if ($root && $replies_count > 0)
        <button type="button" x-show="!show_replies" x-on:click="show_replies = true" class="mt-3 cursor-pointer text-sm text-zinc-500 hover:text-amber-600">
            View {{ $replies_count }} {{ $replies_count === 1 ? 'reply' : 'replies' }}
        </button>

        <button type="button" x-show="show_replies" x-cloak x-on:click="show_replies = false" class="mt-3 cursor-pointer text-sm text-zinc-500 hover:text-amber-600">
            Hide replies
        </button>

        <div x-show="show_replies" x-cloak>
            @foreach ($review['replies'] as $reply)
                @include('livewire.admin.catalog.products.partials.review-item', ['review' => $reply, 'root' => false])
            @endforeach
        </div>
    @endif
</div>
