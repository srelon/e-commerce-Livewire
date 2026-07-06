<?php

namespace App\Services;

use App\Http\Resources\UserNotificationResource;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Redis;

class NotificationService
{
    public function create(
        User $user,
        string $type,
        array $data,
        ?int $product_id = null,
        ?int $review_id = null,
        ?int $parent_id = null,
        ?int $from_user_id = null,
    ): UserNotification {
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'from_user_id' => $from_user_id,
            'type' => $type,
            'data' => $data,
            'product_id' => $product_id,
            'review_id' => $review_id,
            'parent_id' => $parent_id,
        ]);

        $this->publish($user->public_id, ['action' => 'upserted', 'notification' => (new UserNotificationResource($notification))->resolve()]);

        return $notification;
    }

    public function upsertReaction(
        User $user,
        string $type,
        array $data,
        ?int $product_id,
        int $review_id,
        ?int $parent_id,
        int $from_user_id,
    ): UserNotification {
        $notification = UserNotification::firstOrNew([
            'user_id' => $user->id,
            'review_id' => $review_id,
            'from_user_id' => $from_user_id,
        ]);

        $notification->fill([
            'type' => $type,
            'data' => $data,
            'product_id' => $product_id,
            'parent_id' => $parent_id,
        ])->save();

        $this->publish($user->public_id, ['action' => 'upserted', 'notification' => (new UserNotificationResource($notification))->resolve()]);

        return $notification;
    }

    public function deleteReaction(User $user, int $review_id, int $from_user_id): void {
        $notification = UserNotification::where('user_id', $user->id)
            ->where('review_id', $review_id)
            ->where('from_user_id', $from_user_id)
            ->first();

        if (! $notification) {
            return;
        }

        $id = $notification->id;
        $notification->delete();

        $this->publish($user->public_id, ['action' => 'deleted', 'id' => $id]);
    }

    public function getForUser(User $user, int $limit = 10): array {
        $notifications = $user->userNotifications()->latest()->limit($limit)->get();

        $this->markManyRead($user, $notifications->pluck('id')->all());

        return [
            'items' => $notifications->map(fn (UserNotification $n) => (new UserNotificationResource($n))->resolve())->all(),
            'unread_count' => $this->getUnreadCount($user),
        ];
    }

    public function getAllPaginated(User $user, int $perPage = 20): LengthAwarePaginator {
        $paginated = $user->userNotifications()->latest()->paginate($perPage);

        $this->markManyRead($user, collect($paginated->items())->pluck('id')->all());

        return $paginated->through(fn (UserNotification $n) => (new UserNotificationResource($n))->resolve());
    }

    public function getUnreadCount(User $user): int {
        return $user->userNotifications()->whereNull('read_at')->count();
    }

    public function markOneRead(User $user, int $id): void {
        $notification = $user->userNotifications()->whereKey($id)->first();

        if (! $notification || $notification->read_at) {
            return;
        }

        $notification->update(['read_at' => now()]);

        $this->publish($user->public_id, ['action' => 'unread_count', 'count' => $this->getUnreadCount($user)]);
    }

    protected function markManyRead(User $user, array $ids): void {
        if (empty($ids)) {
            return;
        }

        $updated = UserNotification::whereIn('id', $ids)->whereNull('read_at')->update(['read_at' => now()]);

        if ($updated > 0) {
            $this->publish($user->public_id, ['action' => 'unread_count', 'count' => $this->getUnreadCount($user)]);
        }
    }

    protected function publish(string $user_public_id, array $payload): void {
        Redis::publish("notifications.users.{$user_public_id}", json_encode([
            'event' => 'notification',
            'data' => $payload,
        ]));
    }
}
