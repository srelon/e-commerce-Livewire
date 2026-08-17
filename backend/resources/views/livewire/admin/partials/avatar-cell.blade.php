@php
    $avatar_url = $avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($avatar) : null;
    $initials = \Illuminate\Support\Str::of($name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->join('');
@endphp
@if ($avatar_url)
    <x-lightbox-trigger :src="$avatar_url">
        <flux:avatar :src="$avatar_url" :initials="$initials" circle size="sm" />
    </x-lightbox-trigger>
@else
    <flux:avatar :initials="$initials" circle size="sm" />
@endif
