<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class HeaderProfile extends Component
{
    #[On('profile-updated')]
    public function refresh(): void {
        //
    }

    public function render() {
        $admin = auth('admins')->user();

        return view('livewire.components.layouts.admin.header-profile', [
            'name' => $admin?->name,
            'avatar' => $admin?->avatar ? Storage::disk('public')->url($admin->avatar) : null,
            'initials' => Str::of($admin?->name)->explode(' ')->map(fn (string $part) => Str::substr($part, 0, 1))->join(''),
        ]);
    }
}
