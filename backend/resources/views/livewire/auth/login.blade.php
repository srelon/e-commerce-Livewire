<div class="flex min-h-screen items-center justify-center px-4">
    <div class="w-full max-w-sm space-y-6">
        <div class="text-center">
            <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-600 text-lg font-bold text-white">{{ \Illuminate\Support\Str::substr(config('app.name'), 0, 1) }}</span>
            <flux:heading size="xl">{{ config('app.name') }} Admin</flux:heading>
            <flux:text class="mt-1">Sign in to manage the store</flux:text>
        </div>

        <form wire:submit="login" class="space-y-4 rounded-lg border border-base-300 bg-base-200 p-6 shadow-sm">
            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input type="email" wire:model="email" autofocus autocomplete="username" />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>Password</flux:label>
                <flux:input type="password" wire:model="password" autocomplete="current-password" />
                <flux:error name="password" />
            </flux:field>

            <flux:checkbox wire:model="remember" label="Remember me" />

            <flux:button type="submit" variant="primary" class="w-full">Sign in</flux:button>
        </form>
    </div>
</div>
