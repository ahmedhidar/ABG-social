<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\LikeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class LikeController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    #[OA\Post(
        path: "/api/posts/{postId}/like",
        operationId: "toggleLike",
        summary: "Toggle like on a post",
        security: [["apiAuth" => []]],
        tags: ["Likes"],
        parameters: [
            new OA\Parameter(name: "postId", description: "Post id", required: true, in: "path", schema: new OA\Schema(type:
                "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Like status toggled",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "liked", type: "boolean"),
                        new OA\Property(property: "message", type: "string")
                    ]
                )
            )
        ]
    )]
    public function toggle(Request $request, Post $post)
    {
        $result = $this->likeService->toggleLike($post, $request->user()->id);

        return response()->json([
            'liked' => $result['status'] === 'liked',
            'message' => $result['status'] === 'liked' ? 'Post liked' : 'Post unliked'
        ]);
    }
}