<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Import this
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewFriendRequest extends Notification implements ShouldBroadcast // Implement this
{
    use Queueable;

    public $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $sender)
    {
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'message' => 'sent you a friend request.',
            'type' => 'friend_request',
        ];
    }
}
