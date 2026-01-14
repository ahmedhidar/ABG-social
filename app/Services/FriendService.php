<?php

namespace App\Services;

use App\Models\FriendRequest;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Support\Collection;
use App\Notifications\NewFriendRequest;

class FriendService
{
    /**
     * Send a friend request.
     */
    public function sendRequest(int $senderId, int $receiverId): FriendRequest
    {
        $request = FriendRequest::updateOrCreate(
            ['sender_id' => $senderId, 'receiver_id' => $receiverId],
            ['status' => 'pending']
        );

        $sender = User::find($senderId);
        $receiver = User::find($receiverId);

        if ($sender && $receiver) {
            $receiver->notify(new NewFriendRequest($sender));
        }

        return $request;
    }

    /**
     * Accept a friend request.
     */
    public function acceptRequest(FriendRequest $request): void
    {
        $request->update(['status' => 'accepted']);

        // Create mutual friendships
        Friendship::create([
            'user_id' => $request->sender_id,
            'friend_id' => $request->receiver_id
        ]);

        Friendship::create([
            'user_id' => $request->receiver_id,
            'friend_id' => $request->sender_id
        ]);
    }

    /**
     * Reject a friend request.
     */
    public function rejectRequest(FriendRequest $request): void
    {
        $request->update(['status' => 'rejected']);
    }

    /**
     * Search for users.
     */
    public function searchUsers(string $query, int $excludeUserId): Collection
    {
        return User::where('name', 'like', "%{$query}%")
            ->where('id', '!=', $excludeUserId)
            ->with([
                    'friendRequestsSent' => function ($q) use ($excludeUserId) {
                        $q->where('receiver_id', $excludeUserId);
                    },
                    'friendRequestsReceived' => function ($q) use ($excludeUserId) {
                        $q->where('sender_id', $excludeUserId);
                    },
                    'friends' => function ($q) use ($excludeUserId) {
                        $q->where('friend_id', $excludeUserId);
                    }
                ])
            ->get();
    }

    /**
     * Get pending requests for a user.
     */
    public function getPendingRequests(int $userId): Collection
    {
        return FriendRequest::with('sender')
            ->where('receiver_id', $userId)
            ->where('status', 'pending')
            ->get();
    }

    /**
     * Remove a friend.
     */
    public function removeFriend(int $userId, int $friendId): void
    {
        Friendship::where('user_id', $userId)
            ->where('friend_id', $friendId)
            ->delete();

        Friendship::where('user_id', $friendId)
            ->where('friend_id', $userId)
            ->delete();
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancelRequest(int $senderId, int $receiverId): void
    {
        FriendRequest::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('status', 'pending')
            ->delete();
    }
}
