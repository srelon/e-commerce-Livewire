<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthService
{
    public function __construct(protected CartService $cartService) {}

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

    public function formatUserWithCart(?User $user): array {
        return [
            'user' => $user ? $this->formatUser($user) : null,
            'cart' => $user ? $this->cartService->getItems($user) : null,
        ];
    }

    public function issueBroadcastTicket(User $user): string {
        $payload = base64_encode(json_encode([
            'public_id' => $user->public_id,
            'issued_at' => time(),
            'expires_at' => time() + config('websocket.ticket_ttl'),
        ]));

        $signature = hash_hmac('sha256', $payload, config('websocket.ticket_secret'));

        return "{$payload}.{$signature}";
    }
}
