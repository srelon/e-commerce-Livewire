@php
    $url = ! empty($image['original']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($image['original']) : null;
@endphp

@if ($url)
    <x-lightbox-trigger :src="$url">
        <img src="{{ $url }}" alt="" class="h-10 w-10 rounded object-cover">
    </x-lightbox-trigger>
@else
    <div class="flex h-10 w-10 items-center justify-center rounded bg-base-300 text-zinc-400">
        <x-heroicon-o-photo class="h-5 w-5" />
    </div>
@endif
