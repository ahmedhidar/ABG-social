<?php

namespace App\Services;

use App\Models\User;

class NotificationService
{
    /**
     * Get unread notifications for a user.
     */
    public function getUnreadNotifications(User $user)
    {
        return $user->unreadNotifications;
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
