<div class="space-y-6">
    <flux:heading size="xl">Dashboard</flux:heading>

    <div class="rounded-lg border border-base-300 bg-base-200 p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <flux:avatar
                :src="auth('admins')->user()->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url(auth('admins')->user()->avatar) : null"
                :initials="\Illuminate\Support\Str::of(auth('admins')->user()->name)->explode(' ')->map(fn ($part) => \Illuminate\Support\Str::substr($part, 0, 1))->join('')"
                circle
            />
            <div>
                <flux:text class="font-medium text-zinc-800 dark:text-white">{{ auth('admins')->user()->name }}</flux:text>
                <flux:text class="text-sm text-zinc-500">{{ auth('admins')->user()->email }}</flux:text>
                <flux:text class="text-sm text-zinc-500">{{ auth('admins')->user()->role?->label ?? 'No role' }}</flux:text>
            </div>
        </div>
    </div>
</div>
