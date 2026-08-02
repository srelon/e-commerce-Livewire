<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function show(Request $request) {
        $user = Auth::guard('web')->user();

        return $this->respondWithJson($this->authService->formatUserWithCart($user));
    }
}
