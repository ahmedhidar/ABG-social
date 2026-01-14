<?php

namespace App\Policies;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FriendRequestPolicy
{
    /**
     * Determine whether the user can update (accept/reject) the friend request.
     */
    public function update(User $user, FriendRequest $friendRequest): bool
    {
        return $user->id === $friendRequest->receiver_id;
    }

    /**
     * Determine whether the user can delete (cancel/reject) the friend request.
     */
    public function delete(User $user, FriendRequest $friendRequest): bool
    {
        // Manager of the request can be sender (to cancel) or receiver (to reject)
        return $user->id === $friendRequest->sender_id || $user->id === $friendRequest->receiver_id;
    }
}
