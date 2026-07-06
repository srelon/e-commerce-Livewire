<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function index(Request $request) {
        return $this->respondWithJson($this->notificationService->getForUser($request->user()));
    }

    public function all(Request $request) {
        return $this->respondWithJson([
            'items' => $this->notificationService->getAllPaginated($request->user()),
        ]);
    }

    public function unreadCount(Request $request) {
        return $this->respondWithJson(['count' => $this->notificationService->getUnreadCount($request->user())]);
    }

    public function markRead(Request $request, int $id) {
        $this->notificationService->markOneRead($request->user(), $id);

        return $this->respondWithJson(['message' => 'Notification marked as read.']);
    }
}
