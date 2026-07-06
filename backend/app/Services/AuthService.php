<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function register(array $data): User {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return $user;
    }

    public function logout(Request $request): void {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function sendResetLink(string $email): string {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string {
        return Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            },
        );
    }

    public function formatUser(User $user): array {
        return (new UserResource($user))->resolve();
    }
}
