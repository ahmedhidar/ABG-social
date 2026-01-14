<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\CommentPosted;

class CommentService
{
    /**
     * Create a new comment.
     */
    public function createComment(array $data): Comment
    {
        $comment = Comment::create($data);

        $post = Post::find($data['post_id']);
        if ($post && $post->user_id !== $data['user_id']) {
            $user = User::find($data['user_id']);
            if ($user) {
                $post->user->notify(new CommentPosted($user, $post));
            }
        }

        return $comment;
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }

    /**
     * Get comments for a post.
     */
    public function getCommentsForPost(int $postId)
    {
        return Comment::with('user')
            ->where('post_id', $postId)
            ->oldest()
            ->get();
    }
}
