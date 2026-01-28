<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function notify(
        User $user,
        string $type,
        ?string $message = null,
        array $meta = []
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'message' => $message,
            'read'    => false,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        if ($notification->read) {
            return;
        }

        $notification->update([
            'read' => true,
        ]);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsReadForUser(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->where('read', false)
            ->update([
                'read' => true,
            ]);
    }
}
