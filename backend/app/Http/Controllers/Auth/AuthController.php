<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request) {
        $user = $this->authService->register($request->validated());

        return $this->respondWithJson($this->authService->formatUserWithCart($user));
    }

    public function login(LoginRequest $request) {
        $request->authenticate();

        return $this->respondWithJson($this->authService->formatUserWithCart($request->user()));
    }

    public function logout(Request $request) {
        $this->authService->logout($request);

        return $this->respondWithJson(['message' => 'Logged out.']);
    }
}
