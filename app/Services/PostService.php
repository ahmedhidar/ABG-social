<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PostService
{
    /**
     * Create a new post.
     */
    public function createPost(array $data, ?UploadedFile $image = null): Post
    {
        if ($image) {
            $path = $image->store('posts', 'public');
            $data['image_path'] = $path;
        }

        return Post::create($data);
    }

    /**
     * Update an existing post.
     */
    public function updatePost(Post $post, array $data, ?UploadedFile $image = null): Post
    {
        if ($image) {
            // Delete old image if exists
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }

            $path = $image->store('posts', 'public');
            $data['image_path'] = $path;
        }

        $post->update($data);

        return $post;
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post): bool
    {
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        return $post->delete();
    }

    /**
     * Get news feed for a user.
     */
    public function getNewsFeed(int $userId): LengthAwarePaginator
    {
        // Get posts from user and friends
        return Post::with(['user', 'comments.user'])
            ->withCount('likes')
            ->withExists([
                    'likes as is_liked' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }
                ])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('user.friends', function ($q) use ($userId) {
                        $q->where('friend_id', $userId);
                    });
            })
            ->latest()
            ->paginate(10);
    }
}
