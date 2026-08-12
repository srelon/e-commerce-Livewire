<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }} Admin</title>

        @fluxAppearance
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-base-100">
        <flux:sidebar sticky class="border-r border-base-300 bg-base-200 shadow-sm">
            <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 px-2 py-1">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-600 text-sm font-bold text-white">{{ \Illuminate\Support\Str::substr(config('app.name'), 0, 1) }}</span>
                <span class="text-base font-bold text-base-content">{{ config('app.name') }}</span>
            </a>

            <flux:navlist variant="outline" class="mt-4">
                <flux:navlist.item :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                    Dashboard
                </flux:navlist.item>

                @if (Route::has('admin.users.index') && auth('admins')->user()?->hasAccess('users.view'))
                    <flux:navlist.item :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>
                        Users
                    </flux:navlist.item>
                @endif

                @if (Route::has('admin.menus.index') && auth('admins')->user()?->hasAccess('menus.view'))
                    <flux:navlist.item :href="route('admin.menus.index')" :current="request()->routeIs('admin.menus.*')" wire:navigate>
                        Menus
                    </flux:navlist.item>
                @endif

                @if ((Route::has('admin.admins.index') && auth('admins')->user()?->hasAccess('admins.view')) || (Route::has('admin.roles.index') && auth('admins')->user()?->hasAccess('roles.view')))
                    <flux:navlist.group heading="Settings" class="mt-4">
                        @if (Route::has('admin.admins.index') && auth('admins')->user()?->hasAccess('admins.view'))
                            <flux:navlist.item :href="route('admin.admins.index')" :current="request()->routeIs('admin.admins.*')" wire:navigate>
                                Admins
                            </flux:navlist.item>
                        @endif

                        @if (Route::has('admin.roles.index') && auth('admins')->user()?->hasAccess('roles.view'))
                            <flux:navlist.item :href="route('admin.roles.index')" :current="request()->routeIs('admin.roles.*')" wire:navigate>
                                Roles
                            </flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            <flux:dropdown position="top" align="start">
                <flux:profile
                    :name="auth('admins')->user()?->name"
                    :avatar="auth('admins')->user()?->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url(auth('admins')->user()->avatar) : null"
                    :initials="\Illuminate\Support\Str::of(auth('admins')->user()?->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->join('')"
                    circle
                />

                <flux:menu>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <flux:menu.item as="button" type="submit">Log out</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:main>
            {{ $slot }}
        </flux:main>

        <div
            x-data="{
                toasts: [],
                push(toast) {
                    const id = Date.now() + Math.random()
                    this.toasts.push({ id, ...toast })
                    setTimeout(() => this.remove(id), 4000)
                },
                remove(id) {
                    this.toasts = this.toasts.filter((toast) => toast.id !== id)
                },
            }"
            x-init="@if (session('notify')) push(@js(session('notify'))) @endif"
            x-on:notify.window="push($event.detail)"
            class="fixed top-4 right-4 z-50 flex w-80 flex-col gap-2"
        >
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    x-show="true"
                    x-transition
                    x-on:click="remove(toast.id)"
                    class="cursor-pointer rounded-lg px-4 py-3 text-sm text-white shadow-lg"
                    :class="{
                        'bg-emerald-600': toast.type === 'success',
                        'bg-red-600': toast.type === 'danger',
                        'bg-base-200': !toast.type || toast.type === 'info',
                    }"
                    x-text="toast.message"
                ></div>
            </template>
        </div>

        <div
            x-data="{ open: false, src: null }"
            x-on:open-lightbox.window="src = $event.detail.src; open = true"
            x-on:keydown.escape.window="open = false"
            x-show="open"
            x-cloak
            x-on:click="open = false"
            class="fixed inset-0 z-50 flex cursor-pointer items-center justify-center bg-black/80 p-4"
        >
            <img :src="src" x-on:click.stop alt="" class="max-h-[90vh] max-w-[90vw] cursor-default rounded-lg object-contain shadow-2xl">

            <button
                type="button"
                x-on:click="open = false"
                class="absolute top-4 right-4 flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                aria-label="Close"
            >
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>

        @fluxScripts
    </body>
</html>
