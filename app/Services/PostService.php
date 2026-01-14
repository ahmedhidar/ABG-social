<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PostService
{
    /**
     * Create a new post.
     */
public function createPost(array $data, ?UploadedFile $image = null): Post
{
    if ($image) {
        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

        $path = $image->storeAs('posts', $filename, 'public');
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
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('posts', $filename, 'public');

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
        $friendIds = \Illuminate\Support\Facades\DB::table('friendships')
            ->where('user_id', $userId)
            ->pluck('friend_id');

        return Post::with(['user', 'comments.user'])
            ->withCount('likes')
            ->withExists([
                    'likes as is_liked' => function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    }
                ])
            ->whereIn('user_id', $friendIds->push($userId))
            ->latest()
            ->paginate(10);
    }
}
