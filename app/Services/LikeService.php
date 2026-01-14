<?php

namespace App\Services;

use App\Models\Like;
use Illuminate\Database\Eloquent\Model;
use App\Notifications\PostLiked;
use App\Models\Post;
use App\Models\User;

class LikeService
{
    /**
     * Toggle like on a model (Post or Comment).
     */
    public function toggleLike(Model $model, int $userId): array
    {
        $existingLike = $model->likes()
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return ['status' => 'unliked', 'count' => $model->likes()->count()];
        }

        $like = $model->likes()->create([
            'user_id' => $userId
        ]);

        if ($model instanceof Post && $model->user_id !== $userId) {
            $user = User::find($userId);
            if ($user) {
                $model->user->notify(new PostLiked($user, $model));
            }
        }

        return ['status' => 'liked', 'count' => $model->likes()->count()];
    }

    /**
     * Check if user liked a model.
     */
    public function hasLiked(Model $model, int $userId): bool
    {
        return $model->likes()
            ->where('user_id', $userId)
            ->exists();
    }
}
