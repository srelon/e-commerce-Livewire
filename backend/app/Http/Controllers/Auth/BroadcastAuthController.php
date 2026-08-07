<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastAuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function auth(Request $request) {
        $user = Auth::guard('web')->user();

        return $this->respondWithJson([
            'ticket' => $this->authService->issueBroadcastTicket($user),
        ]);
    }
}
