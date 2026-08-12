@props(['src'])

<button type="button" x-on:click="$dispatch('open-lightbox', { src: @js($src) })" {{ $attributes->class('cursor-pointer') }}>
    {{ $slot }}
</button>
