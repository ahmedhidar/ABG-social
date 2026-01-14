<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService)
    {
    }

    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $this->commentService->createComment($data);

        return back()->with('success', 'Comment added successfully!');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return back()->with('success', 'Comment deleted successfully!');
    }
}
