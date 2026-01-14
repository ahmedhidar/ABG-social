<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    #[OA\Post(
        path: "/api/posts/{postId}/comments",
        operationId: "addComment",
        summary: "Add comment to a post",
        security: [["apiAuth" => []]],
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "postId", description: "Post id", required: true, in: "path", schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["content"],
                properties: [
                    new OA\Property(property: "content", type: "string", example: "Nice post!")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Comment added successfully")
        ]
    )]
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $this->commentService->createComment([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'content' => $request->input('content')
        ]);

        return new JsonResource($comment->load('user'));
    }

    #[OA\Delete(
        path: "/api/comments/{id}",
        operationId: "deleteComment",
        summary: "Delete existing comment",
        security: [["apiAuth" => []]],
        tags: ["Comments"],
        parameters: [
            new OA\Parameter(name: "id", description: "Comment id", required: true, in: "path", schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Comment deleted successfully")
        ]
    )]
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
